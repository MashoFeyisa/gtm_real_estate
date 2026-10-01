<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Subscribe a client email to the newsletter and count as a happy buyer.
     */
    public function subscribe(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validateWithBag('newsletter', [
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));

        $subscriber = NewsletterSubscriber::firstOrNew([
            'email' => $normalizedEmail,
        ]);

        $isNew = ! $subscriber->exists;

        if ($isNew) {
            $subscriber->ip_address = $request->ip();
            $subscriber->subscribed_at = now();
            $subscriber->save();
        }

        $baseCount = (int) SiteSetting::get('happy_buyers_base_count', 0);
        $totalCount = $baseCount + NewsletterSubscriber::count();

        $message = $isNew
            ? 'Thank you for subscribing to our updates!'
            : 'You are already subscribed to our newsletter.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_new' => $isNew,
                'message' => $message,
                'count' => $totalCount,
            ]);
        }

        $previousUrl = url()->previous();
        $targetUrl = str_contains($previousUrl, '#')
            ? preg_replace('/#.*$/', '#newsletter', $previousUrl)
            : (rtrim($previousUrl, '/').'/#newsletter');

        return redirect()->to($targetUrl)
            ->with('newsletter_success', $message);
    }

    /**
     * Remove a newsletter subscriber (admin only).
     */
    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return redirect()->route('dashboard', ['tab' => 'subscribers'])
            ->with('success', 'Subscriber removed successfully.');
    }
}
