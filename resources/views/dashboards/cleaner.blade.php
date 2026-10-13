@extends('dashboards.layout')

@section('portal_name', 'Housekeeping Desk')
@section('dashboard-title', 'Housekeeping & Cleaning Operations')
@section('dashboard-subtitle', 'Assigned cleaning tasks, checklists, and turnaround status workflows (Assigned → In Progress → Inspection → Ready).')

@section('sidebar-nav')
    <a href="#overview" data-target-section="overview" class="nav-section-link flex items-center rounded-xl bg-white/10 px-3 py-2.5 text-sm font-semibold text-white">
        <span>📊 Overview</span>
    </a>
    @if (auth()->user()->hasAnyPermission(['housekeeping.manage', 'housekeeping.update', 'housekeeping.create', 'cleaner.tasks']))
        <a href="#tasks" data-target-section="tasks" class="nav-section-link flex items-center justify-between rounded-xl px-3 py-2.5 text-sm font-semibold text-[#dfeee4] transition hover:bg-white/5">
            <span class="flex items-center gap-2.5">
                <span>🧹</span>
                <span>Cleaning Tasks</span>
            </span>
            <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-black uppercase text-white">{{ $stats['total'] }}</span>
        </a>
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Tasks</span>
                <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Assigned</span>
                <div class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['assigned'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wider">In Progress</span>
                <div class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['in_progress'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Inspection</span>
                <div class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['inspection'] }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Ready / Complete</span>
                <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['completed'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Cleaning Tasks List -->
    <div id="tasks" data-section="tasks" class="section-panel hidden scroll-mt-6 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Assigned Cleaning Tasks & Checklists</h3>
        <div class="space-y-6">
            @forelse($tasks as $task)
                <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-base text-slate-900 dark:text-white">Task #{{ $task->id }} - {{ $task->unit_identifier ?? $task->property->title ?? 'General Turnaround' }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold
                                    @if($task->status === 'ready' || $task->status === 'completed') bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                                    @elseif($task->status === 'inspection') bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300
                                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300
                                    @else bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 @endif">
                                    {{ strtoupper(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Scheduled: {{ $task->scheduled_date ? $task->scheduled_date->format('M d, Y') : 'Today' }}
                                @if($task->property) | Location: {{ $task->property->city }}, {{ $task->property->subcity }} @endif
                            </p>
                        </div>
                        <div class="text-xs text-slate-500 text-right">
                            @if($task->started_at) <div>Started: {{ $task->started_at->format('H:i, M d') }}</div> @endif
                            @if($task->inspected_at) <div>Inspected: {{ $task->inspected_at->format('H:i, M d') }}</div> @endif
                            @if($task->ready_at) <div>Ready: {{ $task->ready_at->format('H:i, M d') }}</div> @endif
                        </div>
                    </div>

                    <!-- Checklist Form -->
                    <form method="POST" action="{{ route('cleaner.tasks.update-status', $task) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        @if(!empty($task->checklist))
                            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">Turnaround Checklist</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($task->checklist as $idx => $item)
                                        @php
                                            $itemLabel = is_array($item) ? ($item['item'] ?? 'Task') : $item;
                                            $isDone = is_array($item) ? !empty($item['done']) : false;
                                        @endphp
                                        <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 cursor-pointer">
                                            <input type="checkbox" name="completed_checklist[]" value="{{ is_array($item) ? ($item['item'] ?? $idx) : $item }}"
                                                {{ $isDone ? 'checked' : '' }} class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="{{ $isDone ? 'line-through text-slate-400' : '' }}">{{ $itemLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                            <select name="status" class="w-full sm:w-auto text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                <option value="assigned" {{ $task->status === 'assigned' ? 'selected' : '' }}>Status: Assigned</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>Status: In Progress (Cleaning)</option>
                                <option value="inspection" {{ $task->status === 'inspection' ? 'selected' : '' }}>Status: Inspection</option>
                                <option value="ready" {{ $task->status === 'ready' ? 'selected' : '' }}>Status: Ready (Guest Ready)</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Status: Completed</option>
                            </select>
                            <input type="text" name="notes" placeholder="Notes (e.g. linen restocked, key left in lockbox)" value="{{ $task->notes }}"
                                class="w-full sm:flex-1 text-xs rounded-xl border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-emerald-600 text-white font-medium rounded-xl text-xs hover:bg-emerald-700 transition">
                                Update Workflow
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="text-center py-8 text-slate-500 text-sm">
                    No cleaning tasks currently assigned. Turnarounds will appear here once scheduled.
                </div>
            @endforelse
        </div>
    </div>
@endsection
