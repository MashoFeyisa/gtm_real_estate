<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CleaningTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CleanerDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $hasCleanerPerm = $user?->hasAnyPermission([
            'cleaning.view',
            'housekeeping.view',
            'housekeeping.manage',
            'housekeeping.update',
            'housekeeping.create',
        ]);

        if (! $user || (! in_array($user->role, ['cleaner', 'housekeeping'], true) && ! $user->isAdmin() && ! $hasCleanerPerm)) {
            abort(403, 'Permission denied: Cleaning and housekeeping desk access required.');
        }

        $query = CleaningTask::query()
            ->with(['property', 'cleaner', 'inspector', 'booking'])
            ->latest();

        // If not super admin or manager, limit to tasks assigned to this cleaner
        if (! $user->isAdmin() && ! $user->isPropertyManager()) {
            $query->where('cleaner_user_id', $user->id);
        }

        $allTasks = $query->get();

        $stats = [
            'total' => $allTasks->count(),
            'assigned' => $allTasks->where('status', 'assigned')->count(),
            'in_progress' => $allTasks->where('status', 'in_progress')->count(),
            'inspection' => $allTasks->where('status', 'inspection')->count(),
            'completed' => $allTasks->whereIn('status', ['ready', 'completed'])->count(),
        ];

        return view('dashboards.cleaner', [
            'user' => $user,
            'stats' => $stats,
            'tasks' => $allTasks,
        ]);
    }

    public function updateStatus(Request $request, CleaningTask $task): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isPropertyManager() && $task->cleaner_user_id !== $user->id) {
            abort(403, 'Unauthorized to update this cleaning assignment.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:assigned,in_progress,inspection,ready,completed'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'completed_checklist' => ['nullable', 'array'],
        ]);

        $oldStatus = $task->status;
        $updates = [
            'status' => $validated['status'],
        ];

        if (isset($validated['notes'])) {
            $updates['notes'] = $validated['notes'];
        }

        if ($validated['status'] === 'in_progress' && ! $task->started_at) {
            $updates['started_at'] = now();
        } elseif ($validated['status'] === 'inspection' && ! $task->inspected_at) {
            $updates['inspected_at'] = now();
            $updates['inspected_by'] = $user->id;
        } elseif (in_array($validated['status'], ['ready', 'completed'], true) && ! $task->ready_at) {
            $updates['ready_at'] = now();
        }

        if ($request->has('completed_checklist')) {
            $checklist = $task->checklist ?? [];
            $completed = $request->input('completed_checklist', []);
            foreach ($checklist as $i => $item) {
                if (is_array($item)) {
                    $checklist[$i]['done'] = in_array((string) $i, $completed, true) || in_array($item['item'] ?? '', $completed, true);
                }
            }
            $updates['checklist'] = $checklist;
        }

        $task->update($updates);

        AuditLog::log(
            user: $user,
            action: 'cleaning.status_update',
            entity: $task,
            oldValue: ['status' => $oldStatus],
            newValue: ['status' => $task->status, 'updates' => $updates],
            reason: $validated['notes'] ?? 'Cleaner updated housekeeping status',
            request: $request
        );

        return back()->with('success', "Cleaning task #{$task->id} status updated to ".ucfirst(str_replace('_', ' ', $task->status)).'.');
    }
}
