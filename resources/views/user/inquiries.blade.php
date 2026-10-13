@extends('dashboards.layout')

@section('title', 'My Inquiries')
@section('portal_name', 'Messages & Direct Inquiries')

@section('content')
<div class="space-y-6">
    <div class="rounded-3xl border border-[#d9cab3] bg-gradient-to-r from-[#1d3c34] to-[#2d5d4d] p-6 text-white shadow-sm md:p-8">
        <span class="rounded-full bg-[#d4af37]/20 border border-[#d4af37]/40 px-3 py-1 text-xs font-bold text-[#f8e7b9] uppercase tracking-wider">Direct Messages</span>
        <h1 class="mt-2 text-2xl md:text-3xl font-black text-white">My Inquiries</h1>
        <p class="mt-1 text-sm text-slate-200">Track and view inquiries sent to property managers and agents.</p>
    </div>

    <div class="rounded-3xl border border-[#e7ddca] bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-8">
        @if ($inquiries->isEmpty())
            <div class="text-center py-12">
                <span class="text-4xl">💬</span>
                <h3 class="mt-4 text-base font-bold text-slate-800 dark:text-slate-100">No inquiries found</h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">You haven't sent any messages or property inquiries yet.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-xs uppercase tracking-wider text-slate-500">
                            <th class="pb-3">Subject / Property</th>
                            <th class="pb-3">Message</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3">Sent</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach ($inquiries as $inquiry)
                            <tr>
                                <td class="py-3 font-semibold text-slate-900 dark:text-white">
                                    {{ $inquiry->property->title ?? ($inquiry->subject ?: 'General Property Inquiry') }}
                                </td>
                                <td class="py-3 text-slate-600 dark:text-slate-300 max-w-sm truncate">{{ $inquiry->message }}</td>
                                <td class="py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                        {{ $inquiry->status ?? 'Sent' }}
                                    </span>
                                </td>
                                <td class="py-3 text-xs text-slate-500">{{ $inquiry->created_at?->diffForHumans() ?? 'Recently' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
