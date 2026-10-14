<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ProfessionalServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessionalDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual professional dashboards.');
        }

        $hasProfessionalPerm = $user?->hasAnyPermission([
            'services.jobs_receive',
            'services.jobs_accept',
            'services.profile_manage',
            'properties.view',
        ]);

        if ($user?->status === 'suspended' || (! in_array($user?->role, ['professional', 'service_provider'], true) && ! $hasProfessionalPerm)) {
            abort(403, 'Permission denied: Professional services desk access required.');
        }

        $requests = ProfessionalServiceRequest::with(['user', 'property'])
            ->where(function ($q) use ($user) {
                $q->where('professional_user_id', $user->id);
                if ($user->role === 'service_provider' || $user->role === 'professional') {
                    $q->orWhereNull('professional_user_id');
                }
            })
            ->latest()
            ->get();

        $appointments = $requests->filter(fn ($r) => ! empty($r->appointment_date));
        $reports = $requests->filter(fn ($r) => ! empty($r->report_file_path) || $r->status === 'completed');
        $payments = $requests->filter(fn ($r) => $r->fee > 0);
        $reviews = $requests->filter(fn ($r) => $r->client_rating !== null);

        $totalEarnings = (float) $requests->where('payment_status', 'paid')->sum('fee');

        return view('dashboards.professional', compact(
            'requests',
            'appointments',
            'reports',
            'payments',
            'reviews',
            'totalEarnings'
        ));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot update professional profiles.');
        }

        $validated = $request->validate([
            'profession' => ['nullable', 'string', 'max:100'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'portfolio_summary' => ['nullable', 'string', 'max:2000'],
        ]);

        $user->update($validated);

        AuditLog::log(
            action: 'professional.profile_updated',
            entity: $user,
            newValue: $validated,
            user: $user,
            request: $request
        );

        return back()->with('success', 'Professional profile & rates updated successfully.');
    }

    public function submitQuote(Request $request, ProfessionalServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot submit fee quotes.');
        }

        if ($serviceRequest->professional_user_id && (int) $serviceRequest->professional_user_id !== (int) $user->id) {
            abort(403, 'Access denied: You cannot submit quotes for another professional\'s service request.');
        }

        $fee = $request->input('fee') ?? $request->input('quote_amount');
        if (! $request->has('fee') && $fee !== null) {
            $request->merge(['fee' => $fee]);
        }

        $validated = $request->validate([
            'fee' => ['required', 'numeric', 'min:1'],
            'estimated_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'quote_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $serviceRequest->update([
            'fee' => $validated['fee'],
            'estimated_days' => $validated['estimated_days'] ?? null,
            'quote_notes' => $validated['quote_notes'] ?? null,
            'status' => 'quote_submitted',
            'professional_user_id' => $user->id,
        ]);

        AuditLog::log(
            action: 'professional.quote_submitted',
            entity: $serviceRequest,
            newValue: [
                'fee' => $validated['fee'],
                'estimated_days' => $validated['estimated_days'] ?? null,
                'status' => 'quote_submitted',
            ],
            user: $user,
            request: $request
        );

        return back()->with('success', "Fee quote of {$validated['fee']} ETB submitted successfully.");
    }

    public function scheduleAppointment(Request $request, ProfessionalServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot schedule appointments.');
        }

        if ($serviceRequest->professional_user_id && (int) $serviceRequest->professional_user_id !== (int) $user->id) {
            abort(403, 'Access denied: You cannot schedule appointments for another professional\'s service request.');
        }

        $date = $request->input('appointment_date') ?? $request->input('scheduled_date');
        if (! $request->has('appointment_date') && $date !== null) {
            $request->merge(['appointment_date' => $date]);
        }

        $validated = $request->validate([
            'appointment_date' => ['required', 'date'],
        ]);

        $serviceRequest->update([
            'appointment_date' => $validated['appointment_date'],
            'status' => 'in_progress',
            'professional_user_id' => $user->id,
        ]);

        AuditLog::log(
            action: 'professional.appointment_scheduled',
            entity: $serviceRequest,
            newValue: ['appointment_date' => $validated['appointment_date']],
            user: $user,
            request: $request
        );

        return back()->with('success', 'Appointment scheduled successfully.');
    }

    public function updateStatus(Request $request, ProfessionalServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot update service request status.');
        }

        if ($serviceRequest->professional_user_id && (int) $serviceRequest->professional_user_id !== (int) $user->id) {
            abort(403, 'Access denied: You cannot update another professional\'s service request.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:accepted,in_progress,completed,declined'],
        ]);

        $serviceRequest->update([
            'status' => $validated['status'],
            'professional_user_id' => $user->id,
        ]);

        AuditLog::log(
            action: 'professional.service_request_status_updated',
            entity: $serviceRequest,
            newValue: ['status' => $validated['status']],
            user: $user,
            request: $request
        );

        return back()->with('success', "Service request updated to '{$validated['status']}'.");
    }

    public function uploadReport(Request $request, ProfessionalServiceRequest $serviceRequest): RedirectResponse
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot upload professional reports.');
        }

        if ($serviceRequest->professional_user_id && (int) $serviceRequest->professional_user_id !== (int) $user->id) {
            abort(403, 'Access denied: You cannot upload reports for another professional\'s service request.');
        }

        $validated = $request->validate([
            'report_title' => ['required', 'string', 'max:255'],
            'findings' => ['required', 'string'],
            'report_file' => ['nullable', 'file', 'mimes:pdf,jpg,png,docx', 'max:10240'],
        ]);

        $filePath = null;
        if ($request->hasFile('report_file')) {
            $filePath = $request->file('report_file')->store('professional-reports', 'public');
        }

        $serviceRequest->update([
            'report_title' => $validated['report_title'],
            'findings' => $validated['findings'],
            'report_file_path' => $filePath ?? $serviceRequest->report_file_path,
            'status' => 'completed',
            'professional_user_id' => $user->id,
        ]);

        AuditLog::log(
            action: 'professional.report_submitted',
            entity: $serviceRequest,
            newValue: [
                'report_title' => $validated['report_title'],
                'status' => 'completed',
            ],
            user: $user,
            request: $request
        );

        return back()->with('success', 'Professional report uploaded and request marked completed.');
    }

    public function updateRequestStatus(Request $request, ProfessionalServiceRequest $requestItem): RedirectResponse
    {
        return $this->updateStatus($request, $requestItem);
    }
}
