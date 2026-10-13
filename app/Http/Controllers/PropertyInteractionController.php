<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyFavorite;
use App\Models\PropertyLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyInteractionController extends Controller
{
    /**
     * Toggle like on a property for the authenticated user.
     */
    public function toggleLike(Request $request, Property $property)
    {
        if (! Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Register / Login to continue: Please create an account or sign in to save properties.',
                    'redirect' => route('register'),
                ], 401);
            }

            return redirect()->route('register')->with('signup_prompt', 'Register / Login to continue: Please create an account or sign in to save properties.');
        }

        $userId = Auth::id();

        $existing = PropertyLike::where('user_id', $userId)
            ->where('property_id', $property->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            PropertyLike::create([
                'user_id' => $userId,
                'property_id' => $property->id,
            ]);
            $liked = true;
        }

        $count = PropertyLike::where('property_id', $property->id)->count();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $count,
        ]);
    }

    /**
     * Toggle favorite on a property for the authenticated user.
     */
    public function toggleFavorite(Request $request, Property $property)
    {
        if (! Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Register / Login to continue: Please create an account or sign in to save properties.',
                    'redirect' => route('register'),
                ], 401);
            }

            return redirect()->route('register')->with('signup_prompt', 'Register / Login to continue: Please create an account or sign in to save properties.');
        }

        $userId = Auth::id();

        $existing = PropertyFavorite::where('user_id', $userId)
            ->where('property_id', $property->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            PropertyFavorite::create([
                'user_id' => $userId,
                'property_id' => $property->id,
            ]);
            $favorited = true;
        }

        $count = PropertyFavorite::where('property_id', $property->id)->count();

        return response()->json([
            'favorited' => $favorited,
            'favorites_count' => $count,
        ]);
    }
}
