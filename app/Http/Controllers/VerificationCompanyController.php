<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Verification Company desk: licensed firms accept property document
 * verification jobs, certify property records, submit verification reports,
 * and receive verification payments.
 */
class VerificationCompanyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && ($user->isVerificationCompany() || $user->isAdmin()), 403);

        $canManageJobs = $user->hasPermission('verification_jobs.manage');
        $canVerifyDocuments = $user->hasPermission('property_documents.verify');
        $canSubmitReports = $user->hasPermission('verification_reports.submit');
        $canReceivePayments = $user->hasPermission('verification_payments.receive');

        $documents = PropertyDocument::query()
            ->with('property')
            ->latest()
            ->take(20)
            ->get();

        $pendingProperties = Property::query()
            ->whereIn('verification_status', ['pending', 'unverified'])
            ->latest()
            ->take(20)
            ->get();

        $submittedReports = PropertyDocument::query()
            ->with('property')
            ->where('verified_by', $user->id)
            ->latest()
            ->take(20)
            ->get();

        $payments = Payment::query()
            ->where('payment_type', 'service')
            ->latest()
            ->take(20)
            ->get();

        $stats = [
            'open_jobs' => $documents->whereIn('status', ['pending', 'resubmitted'])->count(),
            'pending_certifications' => $pendingProperties->count(),
            'reports_submitted' => $submittedReports->count(),
            'payments' => $payments->where('status', 'completed')->count(),
        ];

        return view('verification-company.portal', [
            'documents' => $documents,
            'pendingProperties' => $pendingProperties,
            'submittedReports' => $submittedReports,
            'payments' => $payments,
            'stats' => $stats,
            'canManageJobs' => $canManageJobs,
            'canVerifyDocuments' => $canVerifyDocuments,
            'canSubmitReports' => $canSubmitReports,
            'canReceivePayments' => $canReceivePayments,
        ]);
    }
}
