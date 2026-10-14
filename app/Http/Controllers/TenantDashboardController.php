<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyOffer;
use App\Models\RentSchedule;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            abort(403, 'Admin boundary: Administrators cannot open individual tenant dashboards.');
        }

        $hasTenantPerm = $user?->hasAnyPermission([
            'agreements.view',
            'rent.view',
            'leases.view',
            'leases.sign',
            'rent.pay',
            'applications.view',
            'applications.create',
            'maintenance.view',
            'maintenance.create',
            'documents.view',
        ]);

        if ($user?->status === 'suspended' || ! $hasTenantPerm) {
            abort(403, 'Permission denied: Tenant agreement access required.');
        }

        $leases = Lease::with(['property.images', 'landlord', 'agent.user', 'schedules' => fn ($q) => $q->orderBy('installment_number')])
            ->where(function ($q) use ($user) {
                $q->where('tenant_user_id', $user->id)
                    ->orWhere('tenant_email', $user->email);
            })
            ->latest()
            ->get();

        $leaseIds = $leases->pluck('id')->all();
        $propertyIds = $leases->pluck('property_id')->filter()->all();

        // 1. Rental Applications (PropertyOffer where offer_type = 'rental')
        $rentalApplications = PropertyOffer::with(['property.images', 'negotiations'])
            ->where('offer_type', 'rental')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('buyer_email', $user->email);
            })
            ->latest()
            ->get();

        // 2. Rent Schedules & Installments
        $rentSchedules = RentSchedule::with(['lease.property', 'payment'])
            ->whereIn('lease_id', $leaseIds)
            ->orderBy('due_date')
            ->get();

        // 3. Maintenance Service Tickets
        $maintenanceTickets = MaintenanceTicket::with('property')
            ->where(function ($q) use ($user, $propertyIds) {
                $q->where('user_id', $user->id)
                    ->orWhereIn('property_id', $propertyIds);
            })
            ->latest()
            ->get();

        // 4. Tenancy & Property Documents
        $documents = PropertyDocument::where(function ($q) use ($user, $propertyIds) {
            $q->where('user_id', $user->id)
                ->orWhereIn('property_id', $propertyIds);
        })->latest()->get();

        // 5. Landlord / Property Manager Direct Messages (Inquiries)
        $inquiries = Inquiry::with(['property.images', 'agent'])
            ->where(function ($q) use ($user) {
                $q->where('email', $user->email)
                    ->orWhere('user_id', $user->id);
            })
            ->latest()
            ->get();

        // 6. Available Rental Properties for new applications
        $rentalProperties = Property::query()
            ->where('type', 'rent')
            ->where('is_active', true)
            ->where('status', 'active')
            ->take(12)
            ->get();

        return view('dashboards.tenant', compact(
            'leases',
            'rentalApplications',
            'rentSchedules',
            'maintenanceTickets',
            'documents',
            'inquiries',
            'rentalProperties'
        ));
    }

    public function storeApplication(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('applications.create')) {
            abort(403, 'Permission denied: applications.create required.');
        }

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'offered_price' => ['required', 'numeric', 'min:1'],
            'deposit_proposed' => ['nullable', 'numeric', 'min:0'],
            'valid_until' => ['nullable', 'date', 'after:today'],
            'message' => ['nullable', 'string', 'max:2000'],
            'supporting_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,docx', 'max:10240'],
        ]);

        $property = Property::findOrFail($validated['property_id']);
        $docPath = null;

        if ($request->hasFile('supporting_document')) {
            $docPath = $request->file('supporting_document')->store('rental-applications', 'public');
        }

        $application = PropertyOffer::create([
            'property_id' => $property->id,
            'agent_id' => $property->agent_id,
            'user_id' => $user->id,
            'buyer_name' => $user->name,
            'buyer_email' => $user->email,
            'buyer_phone' => $user->phone ?? '+251900000000',
            'offer_type' => 'rental',
            'original_price' => $property->price,
            'offered_price' => $validated['offered_price'],
            'currency' => 'ETB',
            'deposit_proposed' => $validated['deposit_proposed'] ?? ($validated['offered_price'] * 2),
            'valid_until' => $validated['valid_until'] ?? Carbon::today()->addDays(14),
            'status' => 'submitted',
            'message' => $validated['message'] ?? 'Rental tenancy application submitted.',
            'supporting_document_path' => $docPath,
        ]);

        AuditLog::log(
            action: 'tenant.application_created',
            entity: $application,
            newValue: [
                'property_id' => $property->id,
                'monthly_rent' => $application->offered_price,
                'deposit' => $application->deposit_proposed,
            ],
            user: $user,
            request: $request
        );

        return back()->with('success', "Rental application for '{$property->title}' submitted successfully.");
    }

    public function updateApplication(Request $request, PropertyOffer $application): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('applications.update')) {
            abort(403, 'Permission denied: applications.update required.');
        }

        if ($application->user_id !== $user->id && strtolower((string) $application->buyer_email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot modify another user\'s rental application.');
        }

        if (in_array($application->status, ['accepted', 'completed', 'withdrawn'], true)) {
            return back()->with('error', 'Cannot edit an application that has been accepted or withdrawn.');
        }

        $validated = $request->validate([
            'offered_price' => ['required', 'numeric', 'min:1'],
            'deposit_proposed' => ['nullable', 'numeric', 'min:0'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $oldValues = [
            'offered_price' => $application->offered_price,
            'deposit_proposed' => $application->deposit_proposed,
            'message' => $application->message,
        ];

        $application->update([
            'offered_price' => $validated['offered_price'],
            'deposit_proposed' => $validated['deposit_proposed'] ?? $application->deposit_proposed,
            'message' => $validated['message'] ?? $application->message,
        ]);

        AuditLog::log(
            action: 'tenant.application_updated',
            entity: $application,
            oldValue: $oldValues,
            newValue: [
                'offered_price' => $application->offered_price,
                'deposit_proposed' => $application->deposit_proposed,
                'message' => $application->message,
            ],
            user: $user,
            request: $request
        );

        return back()->with('success', "Rental application {$application->offer_reference} updated successfully.");
    }

    public function withdrawApplication(Request $request, PropertyOffer $application): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('applications.withdraw')) {
            abort(403, 'Permission denied: applications.withdraw required.');
        }

        if ($application->user_id !== $user->id && strtolower((string) $application->buyer_email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot withdraw another user\'s rental application.');
        }

        if (in_array($application->status, ['accepted', 'completed'], true)) {
            return back()->with('error', 'Cannot withdraw an accepted rental agreement.');
        }

        $oldStatus = $application->status;
        $application->update(['status' => 'withdrawn']);

        AuditLog::log(
            action: 'tenant.application_withdrawn',
            entity: $application,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => 'withdrawn'],
            user: $user,
            request: $request
        );

        return back()->with('success', "Rental application {$application->offer_reference} withdrawn.");
    }

    public function signLease(Request $request, Lease $lease): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAnyPermission(['leases.sign', 'agreements.sign'])) {
            abort(403, 'Permission denied: agreements.sign required.');
        }

        if ($lease->tenant_user_id !== $user->id && strtolower((string) $lease->tenant_email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot sign another tenant\'s lease agreement.');
        }

        $validated = $request->validate([
            'tenant_signature' => ['required', 'string', 'max:255'],
            'agree_terms' => ['required', 'accepted'],
        ]);

        $oldStatus = $lease->status;

        $lease->update([
            'status' => 'active',
            'signed_at' => Carbon::now(),
            'tenant_signature' => $validated['tenant_signature'],
        ]);

        if ($lease->schedules()->count() === 0) {
            $lease->generateSchedules();
        }

        AuditLog::log(
            action: 'tenant.lease_signed',
            entity: $lease,
            oldValue: ['status' => $oldStatus],
            newValue: [
                'status' => 'active',
                'signed_at' => $lease->signed_at,
                'signature' => $lease->tenant_signature,
            ],
            user: $user,
            request: $request
        );

        return back()->with('success', "Lease agreement {$lease->lease_code} signed and activated.");
    }

    public function submitMaintenance(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('maintenance.create')) {
            abort(403, 'Permission denied: maintenance.create required.');
        }

        $validated = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,normal,urgent,emergency'],
            'category' => ['nullable', 'string', 'in:plumbing,electrical,internet,appliance,structural,cleaning,general'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('maintenance-photos', 'public');
        }

        $ticket = MaintenanceTicket::create([
            'property_id' => $validated['property_id'],
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'category' => $validated['category'] ?? 'general',
            'photo_path' => $photoPath,
            'status' => 'open',
        ]);

        AuditLog::log(
            action: 'tenant.maintenance_submitted',
            entity: $ticket,
            newValue: $ticket->toArray(),
            user: $user,
            request: $request
        );

        return back()->with('success', "Maintenance request '{$ticket->title}' submitted.");
    }

    public function updateMaintenance(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('maintenance.update')) {
            abort(403, 'Permission denied: maintenance.update required.');
        }

        if ($ticket->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot update another user\'s maintenance ticket.');
        }

        $validated = $request->validate([
            'additional_notes' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $oldDesc = $ticket->description;
        $timestamp = Carbon::now()->format('M d, Y H:i');
        $updatedDesc = $ticket->description."\n\n[Tenant update {$timestamp}]: {$validated['additional_notes']}";

        $photoPath = $ticket->photo_path;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('maintenance-photos', 'public');
        }

        $ticket->update([
            'description' => $updatedDesc,
            'photo_path' => $photoPath,
        ]);

        AuditLog::log(
            action: 'tenant.maintenance_updated',
            entity: $ticket,
            oldValue: ['description' => $oldDesc],
            newValue: ['description' => $ticket->description, 'photo_path' => $ticket->photo_path],
            user: $user,
            request: $request
        );

        return back()->with('success', "Maintenance ticket {$ticket->ticket_number} updated.");
    }

    public function closeMaintenance(Request $request, MaintenanceTicket $ticket): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('maintenance.close')) {
            abort(403, 'Permission denied: maintenance.close required.');
        }

        if ($ticket->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot close another user\'s maintenance ticket.');
        }

        $oldStatus = $ticket->status;
        $ticket->update([
            'status' => 'closed',
            'resolved_at' => Carbon::now(),
            'resolution_notes' => $ticket->resolution_notes ?? 'Confirmed resolved and closed by tenant.',
        ]);

        AuditLog::log(
            action: 'tenant.maintenance_closed',
            entity: $ticket,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => 'closed', 'resolved_at' => $ticket->resolved_at],
            user: $user,
            request: $request
        );

        return back()->with('success', "Maintenance ticket {$ticket->ticket_number} closed.");
    }

    public function replyMessage(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAnyPermission(['messages.create', 'inquiries.create'])) {
            abort(403, 'Permission denied: messages.create required.');
        }

        if ($inquiry->user_id !== $user->id && strtolower((string) $inquiry->email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot reply to another user\'s conversation.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $timestamp = Carbon::now()->format('M d, Y H:i');
        $replyEntry = "\n\n[Tenant {$user->name} on {$timestamp}]:\n{$validated['message']}";

        $inquiry->update([
            'message' => $inquiry->message.$replyEntry,
            'status' => 'contacted',
        ]);

        AuditLog::log(
            action: 'tenant.message_sent',
            entity: $inquiry,
            newValue: ['message' => $validated['message']],
            user: $user,
            request: $request
        );

        return back()->with('success', 'Message sent to landlord / property manager.');
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('documents.create')) {
            abort(403, 'Permission denied: documents.create required.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'in:employment_letter,national_id,bank_statement,lease_guarantor,deposit_receipt,other'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,docx', 'max:10240'],
        ]);

        $file = $request->file('document_file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $storedPath = $file->store('tenant-documents', 'public');

        $doc = PropertyDocument::create([
            'user_id' => $user->id,
            'property_id' => null,
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'file_path' => $storedPath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'access_level' => 'private',
            'status' => 'verified',
        ]);

        AuditLog::log(
            action: 'tenant.document_uploaded',
            entity: $doc,
            newValue: ['title' => $doc->title, 'type' => $doc->document_type],
            user: $user,
            request: $request
        );

        return back()->with('success', "Tenancy document '{$doc->title}' uploaded.");
    }

    public function destroyDocument(Request $request, PropertyDocument $document): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasPermission('documents.delete')) {
            abort(403, 'Permission denied: documents.delete required.');
        }

        if ($document->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot delete another user\'s document.');
        }

        AuditLog::log(
            action: 'tenant.document_deleted',
            entity: $document,
            oldValue: ['title' => $document->title, 'path' => $document->file_path],
            user: $user,
            request: $request
        );

        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Document removed.');
    }

    public function receipt(Request $request, RentSchedule $schedule): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasAnyPermission(['rent.view', 'payments.view'])) {
            abort(403, 'Permission denied: rent.view required.');
        }

        $lease = $schedule->lease;
        if ($lease && $lease->tenant_user_id !== $user->id && strtolower((string) $lease->tenant_email) !== strtolower((string) $user->email) && ! $user->isAdmin()) {
            abort(403, 'Forbidden: You cannot view receipts for another tenant.');
        }

        if ($schedule->payment) {
            return redirect()->route('payments.receipt', $schedule->payment->transaction_number);
        }

        // Render direct installment receipt voucher if no standalone Payment record
        return view('dashboards.partials.rent-receipt', compact('schedule', 'lease', 'user'));
    }
}
