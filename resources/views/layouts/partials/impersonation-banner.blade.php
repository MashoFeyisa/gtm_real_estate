@if (session()->has('impersonating_admin_id'))
    <div class="sticky top-0 z-[100] bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 text-slate-900 px-4 py-2.5 text-xs font-bold shadow-md flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-slate-900 text-amber-300 text-[10px]">⚠️</span>
            <span>You are currently impersonating <strong>{{ auth()->user()->name }}</strong> (Role: <span class="uppercase tracking-wider font-extrabold">{{ auth()->user()->role }}</span>).</span>
        </div>
        <form method="POST" action="{{ route('impersonate.leave') }}" class="inline">
            @csrf
            <button type="submit" class="rounded-full bg-slate-900 px-4 py-1.5 text-xs font-black text-white hover:bg-slate-800 transition shadow-xs cursor-pointer">
                Exit Impersonation &rarr;
            </button>
        </form>
    </div>
@endif
