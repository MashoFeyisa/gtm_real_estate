<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workers Attendance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f0e7] text-slate-800 antialiased">
    <nav class="border-b border-[#d9cab3] bg-[#f9f5ee] shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight text-[#1d3c34]">
                Real <span class="text-[#6d7f6a]">Estate</span>
            </a>
            <div class="flex items-center gap-6 text-sm font-semibold text-[#1d3c34]">
                <a href="{{ route('home') }}" class="transition hover:text-[#2d5d4d]">Home</a>
                <a href="{{ route('attendance') }}" class="text-[#2d5d4d]">Workers Attendance</a>
                <a href="{{ route('dashboard') }}" class="transition hover:text-[#2d5d4d]">Admin Dashboard</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-6 py-12">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-[#587165]">Operations</p>
                <h1 class="mt-2 text-4xl font-black text-[#1d3c34]">Workers Attendance</h1>
            </div>
            <div class="rounded-full border border-[#d9cab3] bg-[#f1e4cf] px-4 py-2 text-sm font-semibold text-[#1d3c34]">
                Updated today • {{ now('Africa/Addis_Ababa')->format('h:i A') }}
            </div>
        </div>
        <p class="mt-4 max-w-2xl text-lg text-slate-600">Track daily presence, time in, and leave status across the team using local Ethiopia time.</p>

        <div class="mt-8 grid gap-6 md:grid-cols-4">
            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Present</p>
                <p class="mt-3 text-3xl font-black text-[#1d3c34]">{{ $attendanceSummary['present'] }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#f7f0e6] p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Late</p>
                <p class="mt-3 text-3xl font-black text-[#c58b2b]">{{ $attendanceSummary['late'] }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#fbe9e5] p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Absent</p>
                <p class="mt-3 text-3xl font-black text-[#b14d42]">{{ $attendanceSummary['absent'] }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-[#d9cab3] bg-[#eaf2ee] p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Workers</p>
                <p class="mt-3 text-3xl font-black text-[#2d5d4d]">{{ $records->count() }}</p>
            </div>
        </div>

        <div class="mt-10 overflow-hidden rounded-[1.75rem] border border-[#d9cab3] bg-white shadow-sm">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-[#1d3c34] text-[#f8f3eb]">
                    <tr>
                        <th class="px-6 py-4 font-semibold">Employee</th>
                        <th class="px-6 py-4 font-semibold">Department</th>
                        <th class="px-6 py-4 font-semibold">Check In</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr class="border-t border-[#e7ddca] bg-white">
                            <td class="px-6 py-4 font-semibold text-[#1d3c34]">{{ $record->worker->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $record->worker->department }}</td>
                            <td class="px-6 py-4 text-slate-600">
                                @if ($record->recorded_at)
                                    {{ $record->recorded_at->setTimezone('Africa/Addis_Ababa')->format('h:i A') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($record->status === 'present')
                                    <span class="rounded-full bg-[#dfeee4] px-2.5 py-1 text-xs font-bold text-[#1d3c34]">Present</span>
                                @elseif ($record->status === 'late')
                                    <span class="rounded-full bg-[#f9ecd0] px-2.5 py-1 text-xs font-bold text-[#9b6c17]">Late</span>
                                @elseif ($record->status === 'absent')
                                    <span class="rounded-full bg-[#f8ddd9] px-2.5 py-1 text-xs font-bold text-[#a24339]">Absent</span>
                                @else
                                    <span class="rounded-full bg-[#edf2ee] px-2.5 py-1 text-xs font-bold text-[#2d5d4d]">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">No attendance recorded yet today.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
