@props([
    'name' => 'date',
    'id' => null,
    'value' => null,
    'label' => 'Preferred Date',
    'min' => null,
    'required' => false,
])

@php
    $inputId = $id ?? 'eth_datepicker_' . uniqid();
    $initialValue = $value ?: now()->format('Y-m-d');
    $dateService = app(\App\Services\EthiopianDateService::class);
    $initialEth = $dateService->toEthiopian($initialValue);
    $months = $dateService->getMonthNames(app()->getLocale() === 'om' ? 'om' : 'am');
@endphp

<div class="ethiopian-date-picker-wrapper space-y-2" id="{{ $inputId }}-wrapper"
     x-data="{
        calendarType: 'gregorian',
        gregorianDate: '{{ $initialValue }}',
        ethYear: {{ $initialEth['year'] }},
        ethMonth: {{ $initialEth['month'] }},
        ethDay: {{ $initialEth['day'] }},
        months: {{ json_encode($months) }},
        syncFromEthiopian() {
            // JD calculation: (1724221 - 1) + 365*(ey-1) + floor(ey/4) + 30*(em-1) + ed
            const jd = (1724221 - 1) + 365 * (this.ethYear - 1) + Math.floor(this.ethYear / 4) + 30 * (this.ethMonth - 1) + parseInt(this.ethDay);
            const l = jd + 68569;
            const n = Math.floor((4 * l) / 146097);
            const l1 = l - Math.floor((146097 * n + 3) / 4);
            const i = Math.floor((4000 * (l1 + 1)) / 1461001);
            const l2 = l1 - Math.floor((1461 * i) / 4) + 31;
            const j = Math.floor((80 * l2) / 2447);
            const d = l2 - Math.floor((2447 * j) / 80);
            const l3 = Math.floor(j / 11);
            const m = j + 2 - 12 * l3;
            const y = 100 * (n - 49) + i + l3;
            const mm = String(m).padStart(2, '0');
            const dd = String(d).padStart(2, '0');
            this.gregorianDate = `${y}-${mm}-${dd}`;
        },
        syncFromGregorian() {
            if (!this.gregorianDate) return;
            const parts = this.gregorianDate.split('-');
            const gy = parseInt(parts[0]);
            const gm = parseInt(parts[1]);
            const gd = parseInt(parts[2]);
            const a = Math.floor((14 - gm) / 12);
            const y = gy + 4800 - a;
            const m = gm + 12 * a - 3;
            const jd = gd + Math.floor((153 * m + 2) / 5) + 365 * y + Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400) - 32045;
            const r = (jd - 1724221) % 1461;
            const n = (r % 365) + 365 * Math.floor(r / 1460);
            this.ethYear = 4 * Math.floor((jd - 1724221) / 1461) + Math.floor(r / 365) - Math.floor(r / 1460) + 1;
            this.ethMonth = Math.floor(n / 30) + 1;
            this.ethDay = (n % 30) + 1;
        }
     }">
    <div class="flex items-center justify-between">
        @if ($label)
            <label for="{{ $inputId }}" class="text-sm font-semibold text-slate-700">
                {{ $label }}
            </label>
        @endif

        {{-- Calendar Mode Toggle Switch --}}
        <div class="inline-flex items-center rounded-xl border border-[#d9cab3] bg-[#f9f4ed] p-0.5 text-xs font-bold text-[#1d3c34]">
            <button type="button"
                    class="rounded-lg px-2.5 py-1 transition cursor-pointer"
                    :class="calendarType === 'gregorian' ? 'bg-[#102b25] text-white shadow-xs' : 'text-slate-600 hover:text-[#102b25]'"
                    @click="calendarType = 'gregorian'">
                Gregorian (ፈረንጅ)
            </button>
            <button type="button"
                    class="rounded-lg px-2.5 py-1 transition cursor-pointer"
                    :class="calendarType === 'ethiopian' ? 'bg-[#d4af37] text-[#102b25] shadow-xs' : 'text-slate-600 hover:text-[#102b25]'"
                    @click="calendarType = 'ethiopian'; syncFromGregorian()">
                Ethiopian (ዘመን አቆጣጠር)
            </button>
        </div>
    </div>

    {{-- Gregorian Input View --}}
    <div x-show="calendarType === 'gregorian'">
        <input type="date"
               name="{{ $name }}"
               id="{{ $inputId }}"
               x-model="gregorianDate"
               @change="syncFromGregorian()"
               min="{{ $min }}"
               {{ $required ? 'required' : '' }}
               class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-3.5 py-2.5 text-sm font-semibold text-slate-800 shadow-2xs focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
    </div>

    {{-- Ethiopian Inputs View --}}
    <div x-show="calendarType === 'ethiopian'" style="display: none;" class="grid grid-cols-3 gap-2">
        <div>
            <label class="text-[11px] font-bold text-[#587165]">ወር (Month)</label>
            <select x-model="ethMonth" @change="syncFromEthiopian()"
                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-2 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                @foreach ($months as $mNum => $mLabel)
                    <option value="{{ $mNum }}">{{ $mNum }} - {{ $mLabel }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] font-bold text-[#587165]">ቀን (Day)</label>
            <select x-model="ethDay" @change="syncFromEthiopian()"
                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-2 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                @for ($d = 1; $d <= 30; $d++)
                    <option value="{{ $d }}">{{ $d }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="text-[11px] font-bold text-[#587165]">ዓ.ም (Year)</label>
            <select x-model="ethYear" @change="syncFromEthiopian()"
                    class="w-full rounded-xl border border-[#d9cab3] bg-[#fdfaf5] px-2 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
                @for ($y = 2015; $y <= 2025; $y++)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>

        {{-- Mirrored hidden input when Ethiopian mode is active so form submits standard Gregorian format --}}
        <template x-if="calendarType === 'ethiopian'">
            <input type="hidden" name="{{ $name }}" :value="gregorianDate">
        </template>
    </div>
</div>
