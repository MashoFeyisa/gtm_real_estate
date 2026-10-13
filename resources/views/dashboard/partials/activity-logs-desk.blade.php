        <section id="activity-logs" data-section="activity-logs" class="section-panel mt-10 hidden scroll-mt-24 rounded-[1.75rem] border border-[#d9cab3] bg-white p-6 md:p-8 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#e7ddca] pb-5">
                <div>
                    <span class="rounded-full bg-[#102b25] px-3 py-1 text-xs font-bold uppercase tracking-wider text-[#d4af37]">Security &bull; Real-Time Audit</span>
                    <h2 class="mt-2 text-2xl font-black text-[#1d3c34]">System Activity &amp; Audit Log</h2>
                    <p class="mt-1 text-sm text-slate-600">Track all business events, actor actions, state transitions, financial settlements, and operational activities across the platform.</p>
                </div>
                <div>
                    <span class="rounded-full bg-[#edf3ee] px-3 py-1.5 text-xs font-bold text-[#2d5d4d]">
                        {{ $overviewStats['totalActivityLogs'] ?? 0 }} Total Audit Events
                    </span>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                    <thead>
                        <tr class="bg-[#fcfaf7] text-left text-xs font-black uppercase tracking-wider text-[#587165]">
                            <th class="py-3 px-4">Timestamp</th>
                            <th class="py-3 px-4">Actor / User</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">Action</th>
                            <th class="py-3 px-4">Target Entity</th>
                            <th class="py-3 px-4">Details / Metadata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e7ddca]">
                        @forelse ($activityLogs as $log)
                            <tr class="hover:bg-[#fbf9f5] transition">
                                <td class="py-3 px-4 whitespace-nowrap text-xs text-slate-500">
                                    {{ $log->created_at->format('M d, Y H:i:s') }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-[#1d3c34]">
                                    {{ $log->user?->name ?? 'System' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black uppercase text-slate-700">
                                        {{ $log->role ?? ($log->user?->role ?? 'system') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-xs text-[#2d5d4d]">
                                    {{ $log->action }}
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600">
                                    @if ($log->entity_type)
                                        <span class="font-semibold">{{ class_basename($log->entity_type) }}</span> #{{ $log->entity_id }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-500 max-w-xs truncate">
                                    @if ($log->metadata)
                                        {{ json_encode($log->metadata) }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    No activity logs recorded yet. Business events will appear here in real-time.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activityLogs->hasPages())
                <div class="mt-6 border-t border-[#e7ddca] pt-4">
                    {{ $activityLogs->fragment('activity-logs')->links() }}
                </div>
            @endif

            {{-- ═══════════════════ SECURITY AUDIT LOGS (Permissions & System Changes) ═══════════════════ --}}
            <div class="mt-12 border-t-2 border-[#d9cab3] pt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <span class="rounded-full bg-rose-900 text-rose-100 px-3 py-1 text-xs font-bold uppercase tracking-wider">Immutable Security Ledger</span>
                        <h3 class="mt-2 text-xl font-black text-[#1d3c34]">Permission &amp; Access Control Audit Trail</h3>
                        <p class="mt-1 text-xs text-slate-600">Tamper-evident log of all role modifications, permission assignments, access toggles, and user approvals.</p>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e7ddca] text-sm">
                        <thead>
                            <tr class="bg-[#1d3c34] text-left text-xs font-black uppercase tracking-wider text-[#f8f3eb]">
                                <th class="py-3 px-4">Timestamp</th>
                                <th class="py-3 px-4">Actor</th>
                                <th class="py-3 px-4">Action</th>
                                <th class="py-3 px-4">Entity</th>
                                <th class="py-3 px-4">IP / Device</th>
                                <th class="py-3 px-4">Reason / Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7ddca] bg-white">
                            @forelse ($auditLogs ?? [] as $audit)
                                <tr class="hover:bg-[#fbf9f5] transition">
                                    <td class="py-3 px-4 whitespace-nowrap text-xs text-slate-500">
                                        {{ $audit->created_at->format('M d, Y H:i:s') }}
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-[#1d3c34]">
                                        {{ $audit->actor_name ?? ($audit->user?->name ?? 'System') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-mono font-bold text-[#1d3c34]">
                                            {{ $audit->action }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-slate-700">
                                        <span class="font-bold">{{ $audit->entity_type }}</span> #{{ $audit->entity_id }}
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-slate-500">
                                        {{ $audit->ip_address ?? '127.0.0.1' }}
                                    </td>
                                    <td class="py-3 px-4 text-xs text-slate-600 max-w-xs truncate">
                                        {{ $audit->reason ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">
                                        No security audit logs recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (isset($auditLogs) && $auditLogs->hasPages())
                    <div class="mt-4 border-t border-[#e7ddca] pt-3">
                        {{ $auditLogs->fragment('activity-logs')->links() }}
                    </div>
                @endif
            </div>
        </section>
