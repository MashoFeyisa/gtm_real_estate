<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExperienceController extends Controller
{
    /**
     * Display a listing of verified local Ethiopian tours and cultural experiences.
     */
    public function index(Request $request): View
    {
        $query = Experience::query()->published();

        if ($request->filled('city')) {
            $query->where('city', $request->query('city'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhere('location_address', 'like', $search);
            });
        }

        $experiences = $query->latest()->paginate(12)->withQueryString();

        return view('experiences.index', [
            'experiences' => $experiences,
            'selectedCity' => $request->query('city'),
            'selectedCategory' => $request->query('category'),
            'searchQuery' => $request->query('search'),
        ]);
    }

    /**
     * Display experience detail page with itinerary and booking form.
     */
    public function show(Experience $experience): View
    {
        $experience->load('host');

        $relatedExperiences = Experience::query()
            ->published()
            ->where('id', '!=', $experience->id)
            ->where('city', $experience->city)
            ->limit(3)
            ->get();

        return view('experiences.show', [
            'experience' => $experience,
            'relatedExperiences' => $relatedExperiences,
        ]);
    }

    /**
     * Submit an experience reservation booking.
     */
    public function book(Request $request, Experience $experience): RedirectResponse
    {
        $validated = $request->validate([
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'scheduled_time' => ['required', 'string', 'max:20'],
            'participants_count' => ['required', 'integer', 'min:1', 'max:'.$experience->max_participants],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:50'],
            'payment_method' => ['required', 'in:telebirr,cbe_birr,cash,card'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ]);

        $totalPrice = $experience->price_etb * $validated['participants_count'];

        $booking = ExperienceBooking::create([
            'experience_id' => $experience->id,
            'user_id' => auth()->id(),
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'],
            'participants_count' => $validated['participants_count'],
            'total_price' => $totalPrice,
            'currency' => 'ETB',
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_method'] === 'cash' ? 'unpaid' : 'paid',
            'status' => 'confirmed',
            'special_requests' => $validated['special_requests'] ?? null,
        ]);

        ActivityLog::record(
            'experience_booked',
            "Client {$validated['contact_name']} booked \"{$experience->title}\" (Ref: {$booking->booking_reference})",
            $booking
        );

        return redirect()->route('experiences.show', $experience)
            ->with('success', "Experience booked successfully! Reservation reference: {$booking->booking_reference}.");
    }
}
