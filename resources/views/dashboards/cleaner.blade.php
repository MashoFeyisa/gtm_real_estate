@extends('dashboards.layout')

@section('portal_name', 'Housekeeping Desk')
@section('dashboard-title', 'Housekeeping & Cleaning Operations')
@section('dashboard-subtitle', 'Assigned cleaning tasks, checklists, and turnaround status workflows (Assigned → In Progress → Inspection → Ready).')

@section('sidebar-nav')
    @if (auth()->user()->hasAnyPermission(['housekeeping.manage', 'housekeeping.update', 'housekeeping.create', 'cleaner.tasks']))
        <x-sidebar-item href="#tasks" icon="🧹" label="Cleaning Tasks" :count="$stats['total']" />
    @endif
@endsection

@section('dashboard-content')
    <!-- Overview Section (Analytics & Charts) -->
    <div id="overview" data-section="overview" class="section-panel space-y-8">
        <!-- Metric Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="token-stat-card">
                <span class="token-stat-title">Total Tasks</span>
                <div class="token-stat-value">{{ $stats['total'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Assigned</span>
                <div class="token-stat-value token-stat-warning">{{ $stats['assigned'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">In Progress</span>
                <div class="token-stat-value token-stat-info">{{ $stats['in_progress'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Inspection</span>
                <div class="token-stat-value token-stat-accent">{{ $stats['inspection'] }}</div>
            </div>
            <div class="token-stat-card">
                <span class="token-stat-title">Ready / Complete</span>
                <div class="token-stat-value token-stat-success">{{ $stats['completed'] }}</div>
            </div>
        </div>

        @include('dashboards.partials.analytics-charts')
    </div>

    <!-- Cleaning Tasks List -->
    <div id="tasks" data-section="tasks" class="section-panel hidden scroll-mt-6 token-card">
        <h3 class="text-lg font-bold mb-4">Assigned Cleaning Tasks & Checklists</h3>
        <div class="space-y-6">
            @forelse($tasks as $task)
                <div class="token-chart-tile">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-base">Task #{{ $task->id }} - {{ $task->unit_identifier ?? $task->property->title ?? 'General Turnaround' }}</span>
                                <span class="token-badge
                                    @if($task->status === 'ready' || $task->status === 'completed') token-badge-success
                                    @elseif($task->status === 'inspection') token-badge-accent
                                    @elseif($task->status === 'in_progress') token-badge-info
                                    @else token-badge-warning @endif">
                                    {{ strtoupper(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                            <p class="text-xs token-muted mt-1">
                                Scheduled: {{ $task->scheduled_date ? $task->scheduled_date->format('M d, Y') : 'Today' }}
                                @if($task->property) | Location: {{ $task->property->city }}, {{ $task->property->subcity }} @endif
                            </p>
                        </div>
                        <div class="text-xs token-muted text-right">
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
                            <div class="token-chart-tile">
                                <h4 class="text-xs font-bold uppercase tracking-wider token-muted mb-2">Turnaround Checklist</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($task->checklist as $idx => $item)
                                        @php
                                            $itemLabel = is_array($item) ? ($item['item'] ?? 'Task') : $item;
                                            $isDone = is_array($item) ? !empty($item['done']) : false;
                                        @endphp
                                        <label class="flex items-center gap-2 text-xs cursor-pointer">
                                            <input type="checkbox" name="completed_checklist[]" value="{{ is_array($item) ? ($item['item'] ?? $idx) : $item }}"
                                                {{ $isDone ? 'checked' : '' }} class="rounded accent-[var(--token-success)]">
                                            <span class="{{ $isDone ? 'line-through token-muted' : '' }}">{{ $itemLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                            <select name="status" class="token-field sm:w-auto">
                                <option value="assigned" {{ $task->status === 'assigned' ? 'selected' : '' }}>Status: Assigned</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>Status: In Progress (Cleaning)</option>
                                <option value="inspection" {{ $task->status === 'inspection' ? 'selected' : '' }}>Status: Inspection</option>
                                <option value="ready" {{ $task->status === 'ready' ? 'selected' : '' }}>Status: Ready (Guest Ready)</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Status: Completed</option>
                            </select>
                            <input type="text" name="notes" placeholder="Notes (e.g. linen restocked, key left in lockbox)" value="{{ $task->notes }}"
                                class="sm:flex-1 token-field">
                            <button type="submit" class="token-btn token-btn-primary transition">
                                Update Workflow
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="text-center py-8 token-muted text-sm">
                    No cleaning tasks currently assigned. Turnarounds will appear here once scheduled.
                </div>
            @endforelse
        </div>
    </div>
@endsection
