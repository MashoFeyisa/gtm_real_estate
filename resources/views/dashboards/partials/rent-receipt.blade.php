<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rent Receipt #{{ $schedule->id }} - {{ config('app.name', 'GTM Real Estate') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.theme-styles')
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .receipt-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-[var(--token-surface-subtle)] font-['Plus_Jakarta_Sans',sans-serif] text-[var(--token-text)] antialiased p-4 md:p-10">

    <div class="mx-auto max-w-2xl">
        <div class="no-print mb-6 flex items-center justify-between">
            <a href="{{ route('tenant.dashboard') }}#rent-schedule" class="token-btn token-btn-secondary rounded-full !text-xs gap-1.5 transition shadow-xs cursor-pointer">
                &larr; Back to Tenant Dashboard
            </a>
            <button type="button" onclick="window.print()" class="token-btn token-btn-primary rounded-full !text-xs gap-1.5 transition shadow-xs cursor-pointer">
                <span>Print Receipt / PDF</span>
            </button>
        </div>

        <div class="receipt-card relative rounded-3xl border-2 border-[var(--token-border)] bg-[var(--token-surface)] p-6 sm:p-10 shadow-xl overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[var(--token-flag-green)] via-[var(--token-flag-yellow)] to-[var(--token-flag-red)]"></div>

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 border-b border-[var(--token-border-subtle)] pb-6 mt-1">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="rounded bg-[var(--token-primary)] px-2.5 py-0.5 text-[10px] font-black uppercase text-[var(--token-accent-light)]">Ethiopian Tenancy System</span>
                        <span class="token-muted text-xs font-bold">የኪራይ ክፍያ ደረሰኝ</span>
                    </div>
                    <h1 class="mt-2 text-2xl font-black tracking-tight">Rent Installment Receipt</h1>
                    <p class="token-muted text-xs font-mono mt-0.5">REC-LSE-{{ $lease?->lease_code ?? $schedule->id }}-#{{ $schedule->installment_number }}</p>
                </div>

                <div class="text-right sm:self-start">
                    <span class="token-badge token-badge-success">
                        <span class="h-2 w-2 rounded-full token-fill-success"></span>
                        Settled &amp; Paid
                    </span>
                    <p class="token-muted mt-1 text-[11px]">{{ $schedule->paid_at ? $schedule->paid_at->format('M d, Y') : now()->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="my-6 rounded-2xl bg-[var(--token-surface-subtle)] border border-[var(--token-border)] p-6 text-center">
                <span class="token-muted text-xs font-bold uppercase tracking-wider">Amount Paid</span>
                <div class="mt-1 text-3xl sm:text-4xl font-black text-[var(--token-success)] font-mono">
                    {{ number_format($schedule->amount_paid ?: $schedule->amount_due, 2) }} <span class="text-lg">ETB</span>
                </div>
                <p class="mt-1 text-xs">Period: <strong>{{ $schedule->period_label ?? ('Installment #' . $schedule->installment_number) }}</strong></p>
            </div>

            <div class="space-y-3 text-xs border-t border-[var(--token-border-subtle)] pt-4">
                <div class="flex justify-between py-1 border-b border-[var(--token-border-subtle)]">
                    <span class="token-muted">Lease Reference</span>
                    <span class="font-bold">{{ $lease?->lease_code ?? '—' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[var(--token-border-subtle)]">
                    <span class="token-muted">Rented Property</span>
                    <span class="font-bold">{{ $lease?->property?->title ?? 'Tenancy Property' }}</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[var(--token-border-subtle)]">
                    <span class="token-muted">Tenant</span>
                    <span class="font-bold">{{ $user->name }} ({{ $user->email }})</span>
                </div>
                <div class="flex justify-between py-1 border-b border-[var(--token-border-subtle)]">
                    <span class="token-muted">Payment Method</span>
                    <span class="font-bold">{{ ucfirst(str_replace('_', ' ', $schedule->payment_method ?? 'Telebirr')) }}</span>
                </div>
                <div class="flex justify-between py-1">
                    <span class="token-muted">Settlement Date</span>
                    <span class="font-bold">{{ $schedule->paid_at?->format('M d, Y H:i') ?? now()->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="mt-8 pt-4 border-t border-[var(--token-border)] text-center text-[10px] token-muted">
                <p>Official Ethiopian Tenancy Voucher &bull; Verified via {{ config('app.name', 'GTM Real Estate') }} Digital Platform</p>
            </div>
        </div>
    </div>
</body>
</html>
