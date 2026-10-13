{{--
    Interactive Leaflet location picker used by the property and development registration forms.
    Picking a pin on the map writes the latitude/longitude straight into the two number inputs,
    which are the real form fields (so the form still works with JavaScript disabled).

    Expects:
      $pickerId  — unique DOM id for this picker (several pickers can live on one page)
      $latInputId / $lngInputId — DOM ids of the latitude and longitude inputs
      $lat / $lng — current coordinates (nullable)
      $pickerTitle / $pickerHelp — optional copy overrides

    Behaviour: resources/js/property-map.js → initLocationPickerMaps()
--}}
@php
    $pickerLat = ($lat ?? null) !== null && ($lat ?? '') !== '' ? (float) $lat : null;
    $pickerLng = ($lng ?? null) !== null && ($lng ?? '') !== '' ? (float) $lng : null;

    $subcityCenters = collect(app(App\Services\EthiopianGeoService::class)->getSubcities())
        ->mapWithKeys(fn (array $subcity) => [strtolower($subcity['name']) => [(float) $subcity['lat'], (float) $subcity['lng']]])
        ->all();
@endphp

<div class="sm:col-span-2 rounded-2xl border border-[#ecd9be] bg-[#fffaf2] p-4">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#ecd9be] pb-2.5">
        <div>
            <h4 class="text-sm font-black text-[#1d3c34]">{{ $pickerTitle ?? 'Map Location (GPS Coordinates)' }}</h4>
            <p class="text-xs text-slate-500">{{ $pickerHelp ?? 'Click the map or drag the pin to set the exact latitude and longitude.' }}</p>
        </div>
        <button type="button" data-location-picker-locate class="inline-flex items-center gap-1.5 rounded-xl border border-[#d9cab3] bg-white px-3 py-1.5 text-[11px] font-bold text-[#1d3c34] transition hover:bg-[#f1e4cf] cursor-pointer">
            <span>📍</span>
            <span>Use my current location</span>
        </button>
    </div>

    <div id="{{ $pickerId }}"
         data-location-picker
         data-lat-input="{{ $latInputId }}"
         data-lng-input="{{ $lngInputId }}"
         data-lat="{{ $pickerLat }}"
         data-lng="{{ $pickerLng }}"
         data-subcity-centers="{{ json_encode($subcityCenters) }}"
         class="relative z-0 mt-3 h-72 w-full overflow-hidden rounded-2xl border border-[#d9cab3] bg-[#e9f0ea]"
         role="application"
         aria-label="Location map — click to place the pin"></div>

    <div class="mt-3 grid gap-3 sm:grid-cols-2">
        <div>
            <label for="{{ $latInputId }}" class="mb-1 block text-xs font-bold text-slate-700">Latitude (GPS)</label>
            <input type="number" step="any" min="-90" max="90" id="{{ $latInputId }}" name="latitude" value="{{ old('latitude', $pickerLat) }}" placeholder="e.g. 9.0016" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
        </div>
        <div>
            <label for="{{ $lngInputId }}" class="mb-1 block text-xs font-bold text-slate-700">Longitude (GPS)</label>
            <input type="number" step="any" min="-180" max="180" id="{{ $lngInputId }}" name="longitude" value="{{ old('longitude', $pickerLng) }}" placeholder="e.g. 38.7844" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2 text-xs font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-[#dfeee4]">
        </div>
    </div>

    <p class="mt-2 text-[11px] text-slate-500">
        Addis Ababa references — Bole 9.0016 / 38.7844 · Kazanchis 9.0205 / 38.7675 · CMC 9.0250 / 38.8350 · Sarbet 8.9950 / 38.7420
    </p>
</div>
