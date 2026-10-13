<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PropertyVerificationController extends Controller
{
    /**
     * Upload an Ethiopian legal or ownership document for a property (SRS §27, §28, §177).
     */
    public function uploadDocument(Request $request, Property $property): RedirectResponse
    {
        $this->authorizeDocumentManagement($property);

        $validated = $request->validate([
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:12288'],
            'document_type' => ['required', 'string', 'in:title_deed_carta,site_plan_mapa,building_permit,ownership_certificate,tax_clearance,power_of_attorney,seller_id,other,carta,site_plan'],
            'title' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'access_level' => ['nullable', 'in:confidential,verification_only,transaction_participants,public'],
        ]);

        $file = $request->file('document_file');
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $path = $file->store('property_documents/'.$property->id, 'public');

        $docType = match ($validated['document_type']) {
            'carta' => 'title_deed_carta',
            'site_plan' => 'site_plan_mapa',
            default => $validated['document_type'],
        };

        $document = PropertyDocument::create([
            'property_id' => $property->id,
            'user_id' => auth()->id(),
            'document_type' => $docType,
            'title' => $validated['title'],
            'document_number' => $validated['document_number'] ?? null,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'access_level' => $validated['access_level'] ?? 'confidential',
            'status' => 'pending',
        ]);

        // Auto-advance verification level if currently unverified (SRS §22: Level 3 Documents Submitted)
        if ($property->verification_level < 3) {
            $property->update([
                'verification_level' => 3,
                'verification_status' => 'pending',
            ]);
        }

        return back()->with('success', "Legal document '{$document->title}' uploaded successfully for verification review.");
    }

    /**
     * Securely download a property legal document respecting access permissions (SRS §28).
     */
    public function downloadDocument(Property $property, PropertyDocument $document): StreamedResponse
    {
        if ($document->property_id !== $property->id) {
            abort(404);
        }

        if (! $document->isAccessibleBy(auth()->user())) {
            abort(403, 'You do not have authorization to view this confidential property document.');
        }

        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'The requested document file could not be located on storage.');
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Delete an uploaded property document.
     */
    public function deleteDocument(Property $property, PropertyDocument $document): RedirectResponse
    {
        $this->authorizeDocumentManagement($property);

        if ($document->property_id !== $property->id) {
            abort(404);
        }

        // Delete from disk
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $title = $document->title;
        $document->delete();

        $message = "Document '{$title}' removed.";

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'verifications',
            ]);
        }

        return redirect()->to(route('dashboard').'#verifications')->with('success', $message);
    }

    /**
     * Update property verification level, verified badges, and cadastre reference (Admin Only, SRS §22, §23).
     */
    public function updateVerification(Request $request, Property $property): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        $canCertifyVerification = $user && (
            $user->isFinanceStaff()
            || ($user->role === 'verification_officer' && $user->hasPermission('verifications.identity'))
            || ($user->role === 'verification_company' && $user->hasPermission('verification_jobs.manage'))
        );

        if (! $canCertifyVerification) {
            abort(403, 'Only platform administrators can certify verification levels.');
        }

        $validated = $request->validate([
            'verification_level' => ['required', 'integer', 'min:0', 'max:5'],
            'verification_status' => ['required', 'in:unverified,pending,verified,rejected'],
            'verified_badges' => ['nullable', 'array'],
            'badges' => ['nullable', 'array'],
            'title_deed_number' => ['nullable', 'string', 'max:100'],
            'cadastre_code' => ['nullable', 'string', 'max:100'],
            'verification_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $badges = $request->input('verified_badges', $request->input('badges', []));

        $property->update([
            'verification_level' => $validated['verification_level'],
            'verification_status' => $validated['verification_status'],
            'verified_badges' => array_values($badges),
            'title_deed_number' => $validated['title_deed_number'] ?? null,
            'cadastre_code' => $validated['cadastre_code'] ?? null,
            'verification_notes' => $validated['verification_notes'] ?? null,
            'verified_at' => $validated['verification_level'] >= 4 ? now() : null,
            'verified_by' => $user->id,
        ]);

        $message = "Verification for '{$property->title}' updated to Level {$property->verification_level} ({$property->verification_level_label}).";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'verifications',
            ]);
        }

        return redirect()->to(route('dashboard').'#verifications')->with('success', $message);
    }

    /**
     * Update individual document status (Admin Only, SRS §22: Verified / Rejected).
     */
    public function updateDocumentStatus(Request $request, Property $property, PropertyDocument $document): RedirectResponse|JsonResponse
    {
        $user = auth()->user();
        $canReviewDocument = $user && (
            $user->isFinanceStaff()
            || ($user->role === 'verification_officer' && $user->hasPermission('documents.review_property'))
            || ($user->role === 'verification_company' && $user->hasPermission('property_documents.verify'))
        );

        abort_unless($canReviewDocument, 403, 'Unauthorized.');

        if ($document->property_id !== $property->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,verified,rejected,superseded'],
            'verification_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $document->update([
            'status' => $validated['status'],
            'verification_notes' => $validated['verification_notes'] ?? null,
            'verified_at' => $validated['status'] === 'verified' ? now() : null,
            'verified_by' => $user->id,
        ]);

        $message = "Document status updated to {$document->status}.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'section' => 'verifications',
            ]);
        }

        return redirect()->to(route('dashboard').'#verifications')->with('success', $message);
    }

    /**
     * Authorize that the authenticated user is admin OR the listing's agent OR the property's owner.
     */
    protected function authorizeDocumentManagement(Property $property): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(401, 'Please sign in to manage property documentation.');
        }

        if ($user->isFinanceStaff()) {
            return;
        }

        if ($user->role === 'verification_officer' && $user->hasPermission('documents.review_property')) {
            return;
        }

        if ($user->agentProfile && $property->agent_id === $user->agentProfile->id) {
            return;
        }

        abort(403, 'You do not have permission to manage documents for this property.');
    }
}
