<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaAccessController extends Controller
{
    /**
     * Serve a property image or return a placeholder if denied.
     */
    public function servePropertyImage(Request $request, PropertyImage $image): Response|BinaryFileResponse
    {
        $user = $request->user();

        // Check if media access is revoked for this user
        if ($user && ($user->status === 'suspended' || ! $user->media_access_enabled)) {
            return $this->returnPlaceholderSvg('Access Denied: Media Access Revoked', 403);
        }

        $path = $image->image_path;
        if (! $path) {
            return $this->returnPlaceholderSvg('No Image Available');
        }

        // If local public storage
        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }

        // If in public/images/
        if (file_exists(public_path($path))) {
            return response()->file(public_path($path));
        }

        return $this->returnPlaceholderSvg('Property Photo (Demo)');
    }

    /**
     * Serve a user avatar or return placeholder.
     */
    public function serveAvatar(Request $request, User $targetUser): Response|BinaryFileResponse
    {
        $viewer = $request->user();

        if ($viewer && ($viewer->status === 'suspended' || ! $viewer->media_access_enabled)) {
            return $this->returnPlaceholderSvg('Access Denied', 403);
        }

        if ($targetUser->photo_path && Storage::disk('public')->exists($targetUser->photo_path)) {
            return response()->file(Storage::disk('public')->path($targetUser->photo_path));
        }

        $initials = strtoupper(substr($targetUser->name, 0, 2));

        return $this->returnAvatarSvg($initials);
    }

    /**
     * Securely serve a property document respecting the 5 access levels.
     */
    public function serveDocument(Request $request, PropertyDocument $document): StreamedResponse|Response
    {
        $user = $request->user();

        if (! $document->isAccessibleBy($user)) {
            abort(403, 'You do not have authorization to view this document.');
        }

        if (! Storage::disk('public')->exists($document->file_path)) {
            // Return dummy PDF / document response for testing/demo
            return response("Dummy Protected Document Content: {$document->title} (#{$document->id})", 200, [
                'Content-Type' => $document->mime_type ?? 'text/plain',
                'Content-Disposition' => "inline; filename=\"{$document->file_name}\"",
            ]);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Generate an SVG placeholder for denied or missing images.
     */
    private function returnPlaceholderSvg(string $label, int $status = 200): Response
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400" viewBox="0 0 600 400">
    <rect width="600" height="400" fill="#1e293b"/>
    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="20" font-weight="bold" fill="#94a3b8">
        {$label}
    </text>
</svg>
SVG;

        return response($svg, $status, ['Content-Type' => 'image/svg+xml']);
    }

    /**
     * Generate an SVG avatar.
     */
    private function returnAvatarSvg(string $initials): Response
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">
    <rect width="120" height="120" rx="60" fill="#0f766e"/>
    <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-family="sans-serif" font-size="44" font-weight="bold" fill="#ffffff">
        {$initials}
    </text>
</svg>
SVG;

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
