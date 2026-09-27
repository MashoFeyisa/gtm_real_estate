<?php

namespace App\Http\Controllers;

use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'agent_id' => ['nullable', 'exists:agents,id'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10'],
        ]);

        $inquiry = Inquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'agent_id' => $validated['agent_id'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        $recipients = collect([config('mail.from.address', 'gtmrealstate@gmail.com')]);

        if ($inquiry->agent_id && $inquiry->agent?->email) {
            $recipients->push($inquiry->agent->email);
        }

        $recipients->unique()->each(fn (string $email) => Mail::to($email)->send(new InquiryReceived($inquiry)));

        $message = $inquiry->agent
            ? 'Your message has been sent to '.$inquiry->agent->name.' successfully.'
            : 'Your message has been sent successfully.';

        if ($inquiry->agent_id) {
            return redirect()->route('agents.show', $inquiry->agent)->with('success', $message);
        }

        return redirect('/app#contact')->with('success', $message);
    }
}
