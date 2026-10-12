/**
 * Interactive Luxury Real Estate Map Engine (Leaflet & OpenStreetMap)
 * Features bespoke Gold & Emerald luxury pins, interactive popups,
 * multi-currency synchronization, and bidirectional hover highlighting with the grid.
 */

import L from 'leaflet';
import { formatPrice, getActiveCurrency } from './currency';

const Leaflet = typeof window !== 'undefined' && window.L ? window.L : L;
if (typeof window !== 'undefined') {
    window.L = Leaflet;
}

export function initPropertyMaps() {
    initIndexPropertiesMap();
    initSinglePropertyMap();
    initProjectLocationMap();
}

/**
 * Registration-time location pickers: click or drag to capture latitude/longitude
 * while registering a property, guest house, or development project.
 */
const locationPickers = new Map();

const CARTO_TILES = 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png';
const CARTO_ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/">CARTO</a>';
const OSM_TILES = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
const ADDIS_ABABA_CENTER = [9.0192, 38.7525];

/**
 * Creates resilient tile layer with OpenStreetMap / CARTO (0 cost, no key required),
 * or Google Maps tiles if GOOGLE_MAPS_API_KEY is configured.
 */
export function createInteractiveTileLayer() {
    const googleKey = (typeof window !== 'undefined' && window.GOOGLE_MAPS_API_KEY) || '';
    if (googleKey) {
        return Leaflet.tileLayer(
            `https://maps.googleapis.com/maps/vt?pb=!1m5!1m4!1i{z}!2i{x}!3i{y}!4i256!2m3!1e0!2sm!3i420120488!3m14!2sen!3sUS!5e18!12m1!1e47!12m3!1e37!2m1!1ssmartmaps!12m4!1e26!2m2!1sstyles!2z!4e0&key=${googleKey}`,
            {
                attribution: '&copy; Google Maps',
                maxZoom: 20,
            }
        );
    }

    const tileLayer = Leaflet.tileLayer(CARTO_TILES, {
        attribution: CARTO_ATTRIBUTION,
        subdomains: 'abcd',
        maxZoom: 19,
        crossOrigin: true,
    });

    tileLayer.on('tileerror', function () {
        if (!tileLayer._hasFallenBack) {
            tileLayer._hasFallenBack = true;
            console.warn('CARTO basemap tile failed to load. Switching dynamically to OpenStreetMap standard tiles.');
            tileLayer.setUrl(OSM_TILES);
        }
    });

    return tileLayer;
}

export function initLocationPickerMaps() {
    document.querySelectorAll('[data-location-picker]').forEach((element) => {
        initLocationPicker(element);
    });
}

function initLocationPicker(element) {
    if (typeof Leaflet === 'undefined' || !element.id || locationPickers.has(element.id)) {
        return;
    }

    const latInput = document.getElementById(element.dataset.latInput || '');
    const lngInput = document.getElementById(element.dataset.lngInput || '');
    if (!latInput || !lngInput) {
        return;
    }

    const subcityCenters = parseSubcityCenters(element.dataset.subcityCenters);
    const subcityField = element.closest('form')?.querySelector('[name="subcity"]');
    const initial = resolveInitialPosition(element, latInput, lngInput, subcityCenters, subcityField);

    const map = Leaflet.map(element, {
        center: [initial.lat, initial.lng],
        zoom: 14,
        zoomControl: true,
        scrollWheelZoom: false,
    });

    createInteractiveTileLayer().addTo(map);

    const marker = Leaflet.marker([initial.lat, initial.lng], {
        draggable: true,
        autoPan: true,
    }).addTo(map);

    function writeCoordinates(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
    }

    function placePin(lat, lng, { pan = false } = {}) {
        marker.setLatLng([lat, lng]);
        writeCoordinates(lat, lng);
        if (pan) {
            map.panTo([lat, lng]);
        }
    }

    // Never invent a location: only the stored pin, a known district centroid,
    // or an explicit user click fills the coordinate inputs.
    if (initial.confirmed) {
        writeCoordinates(initial.lat, initial.lng);
    }

    map.on('click', (event) => {
        placePin(event.latlng.lat, event.latlng.lng);
    });

    marker.on('dragend', () => {
        const position = marker.getLatLng();
        writeCoordinates(position.lat, position.lng);
    });

    // Typing coordinates by hand moves the pin too.
    [latInput, lngInput].forEach((input) => {
        input.addEventListener('change', () => {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                placePin(lat, lng, { pan: true });
            }
        });
    });

    // "Use my current location" — for agents standing at the plot.
    const locateButton = element.parentElement?.querySelector('[data-location-picker-locate]');
    if (locateButton && navigator.geolocation) {
        locateButton.addEventListener('click', () => {
            locateButton.disabled = true;
            locateButton.classList.add('opacity-60');

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    placePin(position.coords.latitude, position.coords.longitude, { pan: true });
                    map.setZoom(17);
                    locateButton.disabled = false;
                    locateButton.classList.remove('opacity-60');
                },
                () => {
                    locateButton.disabled = false;
                    locateButton.classList.remove('opacity-60');
                },
                { enableHighAccuracy: true, timeout: 10000 },
            );
        });
    }

    // Picking a sub-city recenters the map on that district's centroid.
    if (subcityField && Object.keys(subcityCenters).length) {
        const recenter = () => {
            const center = subcityCenters[String(subcityField.value).trim().toLowerCase()];
            if (!center) {
                return;
            }
            map.setView(center, 15);
            placePin(center[0], center[1]);
        };

        subcityField.addEventListener('change', recenter);
        if (subcityField.tagName === 'INPUT') {
            subcityField.addEventListener('blur', recenter);
        }
    }

    // Pickers inside modals are laid out only once the modal is shown.
    const modal = element.closest('[id$="-modal"]');
    if (modal) {
        new MutationObserver(() => {
            if (!modal.classList.contains('hidden')) {
                setTimeout(() => map.invalidateSize(), 60);
            }
        }).observe(modal, { attributes: true, attributeFilter: ['class'] });
    }

    setTimeout(() => map.invalidateSize(), 0);

    locationPickers.set(element.id, { map, marker, placePin });
}

function resolveInitialPosition(element, latInput, lngInput, subcityCenters, subcityField) {
    const candidates = [
        [parseFloat(element.dataset.lat), parseFloat(element.dataset.lng)],
        [parseFloat(latInput.value), parseFloat(lngInput.value)],
    ];

    for (const [lat, lng] of candidates) {
        if (Number.isFinite(lat) && Number.isFinite(lng) && !(lat === 0 && lng === 0)) {
            return { lat, lng, confirmed: true };
        }
    }

    // No pin yet: start on the district the user already selected, if we know it.
    const subcity = String(subcityField?.value ?? '').trim().toLowerCase();
    if (subcity && subcityCenters[subcity]) {
        return { lat: subcityCenters[subcity][0], lng: subcityCenters[subcity][1], confirmed: true };
    }

    return { lat: ADDIS_ABABA_CENTER[0], lng: ADDIS_ABABA_CENTER[1], confirmed: false };
}

function parseLatLngPair(raw) {
    const parts = String(raw || '').split(',');
    if (parts.length !== 2) {
        return null;
    }

    const lat = parseFloat(parts[0]);
    const lng = parseFloat(parts[1]);

    return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
}

function parseLatLngBounds(raw) {
    let parsed;
    try {
        parsed = JSON.parse(raw || '');
    } catch (e) {
        return null;
    }

    if (!Array.isArray(parsed) || parsed.length !== 2 || !parsed.every((corner) => Array.isArray(corner) && corner.length === 2)) {
        return null;
    }

    return parsed.map((corner) => [parseFloat(corner[0]), parseFloat(corner[1])]);
}

function parseSubcityCenters(raw) {
    try {
        const parsed = JSON.parse(raw || '{}');
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch (e) {
        return {};
    }
}

/**
 * Move an already-initialized picker's pin, e.g. when an edit form is populated by JavaScript.
 */
export function setLocationPickerPosition(pickerId, lat, lng, { zoom = 15 } = {}) {
    const picker = locationPickers.get(pickerId);
    if (!picker) {
        return;
    }

    const latitude = parseFloat(lat);
    const longitude = parseFloat(lng);
    if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
        return;
    }

    picker.placePin(latitude, longitude, { pan: true });
    picker.map.setZoom(zoom);
    setTimeout(() => picker.map.invalidateSize(), 60);
}

if (typeof window !== 'undefined') {
    window.setLocationPickerPosition = setLocationPickerPosition;
}

function createLuxuryPinHtml(priceText = '', isFeatured = false) {
    const ringColor = isFeatured ? '#ffd700' : '#d4af37';
    return `
        <div class="luxury-map-pin group relative flex items-center justify-center cursor-pointer transition-transform hover:scale-110 active:scale-95" style="filter: drop-shadow(0 8px 16px rgba(16,43,37,0.35));">
            <div class="relative flex items-center gap-1.5 rounded-full bg-[#102b25] px-2.5 py-1 text-xs font-black text-[#fdf8f0] border-2" style="border-color: ${ringColor};">
                <svg class="h-3 w-3 text-[#d4af37]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <span class="luxury-pin-price tracking-tight whitespace-nowrap">${priceText}</span>
            </div>
            <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-2.5 h-2.5 rotate-45 bg-[#102b25] border-r-2 border-b-2" style="border-color: ${ringColor};"></div>
        </div>
    `;
}

function initIndexPropertiesMap() {
    const mapElement = document.getElementById('luxury-properties-map');
    if (!mapElement || typeof Leaflet === 'undefined' || mapElement._leaflet_id) return;

    const rawData = mapElement.getAttribute('data-properties');
    let properties = [];
    try {
        properties = JSON.parse(rawData || '[]');
    } catch (e) {
        console.error('Failed to parse property map data', e);
    }

    // Center on first property or Addis Ababa center
    const initialLat = properties[0]?.lat || ADDIS_ABABA_CENTER[0];
    const initialLng = properties[0]?.lng || ADDIS_ABABA_CENTER[1];

    // Reopen the map exactly where the visitor searched from last time.
    const storedBounds = parseLatLngBounds(mapElement.getAttribute('data-initial-bounds'));
    const storedCenter = parseLatLngPair(mapElement.getAttribute('data-initial-center'));
    const storedZoom = parseInt(mapElement.getAttribute('data-initial-zoom') || '', 10);

    const map = Leaflet.map(mapElement, {
        center: storedCenter || [initialLat, initialLng],
        zoom: Number.isFinite(storedZoom) ? storedZoom : 12,
        zoomControl: true,
        scrollWheelZoom: false,
    });

    createInteractiveTileLayer().addTo(map);

    // Multiple reflow invalidations so tiles always render without grey gaps
    setTimeout(() => map.invalidateSize(), 60);
    setTimeout(() => map.invalidateSize(), 250);
    setTimeout(() => map.invalidateSize(), 600);
    if (typeof window !== 'undefined') {
        window.addEventListener('load', () => map.invalidateSize());
        window.addEventListener('resize', () => map.invalidateSize());
    }

    const markersMap = new Map();
    const bounds = Leaflet.latLngBounds();

    function renderMarkers() {
        // Clear previous markers
        markersMap.forEach((marker) => map.removeLayer(marker));
        markersMap.clear();

        const currentCurrency = getActiveCurrency();

        properties.forEach((prop) => {
            if (!prop.lat || !prop.lng) return;

            const formattedPrice = formatPrice(prop.price, currentCurrency);
            const customIcon = Leaflet.divIcon({
                className: 'custom-leaflet-pin',
                html: createLuxuryPinHtml(formattedPrice, prop.featured),
                iconSize: [110, 36],
                iconAnchor: [55, 36],
                popupAnchor: [0, -38],
            });

            const marker = Leaflet.marker([prop.lat, prop.lng], { icon: customIcon }).addTo(map);

            const popupContent = `
                <div class="overflow-hidden rounded-2xl bg-white font-sans shadow-xl text-slate-800" style="min-width: 240px; max-width: 280px;">
                    <div class="relative h-32 w-full overflow-hidden bg-slate-900">
                        <img src="${prop.image}" alt="${prop.title}" class="h-full w-full object-cover">
                        <span class="absolute top-2.5 left-2.5 rounded-full bg-[#102b25]/90 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-white backdrop-blur-xs">
                            ${prop.type === 'rent' ? 'For Rent' : 'For Sale'}
                        </span>
                        <span class="absolute bottom-2.5 right-2.5 rounded-full bg-black/75 px-2 py-0.5 text-[11px] font-black text-[#d4af37] backdrop-blur-xs">
                            ${formattedPrice}${prop.type === 'rent' ? ' / mo' : ''}
                        </span>
                    </div>
                    <div class="p-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#587165]">${prop.category}${prop.subcity ? ' · ' + prop.subcity : ''}</p>
                        <h4 class="mt-0.5 font-bold text-sm text-[#102b25] line-clamp-1">${prop.title}</h4>
                        <p class="text-xs text-slate-500 line-clamp-1">${prop.neighborhood ? prop.neighborhood + ', ' : ''}${prop.address}</p>
                        <div class="mt-2 flex items-center justify-between border-t border-[#f0e4cf] pt-2 text-[11px] text-slate-600">
                            <span>${prop.bedrooms} beds · ${prop.bathrooms} baths</span>
                            <span>${prop.area} sqm</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[10px]">
                            <span class="font-bold text-emerald-800">🚶 Walk Score: ${prop.walk_score || 85}/100</span>
                            <span class="font-bold text-sky-800">🚊 Transit: ${prop.transit_score || 80}/100</span>
                        </div>
                        <a href="${prop.url}" class="mt-2.5 block w-full rounded-xl bg-[#102b25] py-2 text-center text-xs font-bold text-[#fdf8f0] transition hover:bg-[#1d3c34]">
                            View Property &rarr;
                        </a>
                    </div>
                </div>
            `;

            marker.bindPopup(popupContent, {
                maxWidth: 300,
                className: 'luxury-leaflet-popup',
            });

            markersMap.set(prop.id, marker);
            bounds.extend([prop.lat, prop.lng]);
        });

        if (storedBounds) {
            map.fitBounds(storedBounds, { padding: [10, 10], animate: false });
        } else if (properties.length > 1 && bounds.isValid()) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
        }
    }

    renderMarkers();

    // Fetch and render Subcity Polygon Boundaries overlay
    fetch('/api/geo/subcities/boundaries')
        .then((res) => (res.ok ? res.json() : null))
        .then((geoJson) => {
            if (geoJson && geoJson.features && geoJson.features.length > 0) {
                Leaflet.geoJSON(geoJson, {
                    style: {
                        color: '#d4af37',
                        weight: 1.5,
                        opacity: 0.65,
                        fillColor: '#102b25',
                        fillOpacity: 0.04,
                        dashArray: '3, 4',
                    },
                    onEachFeature: (feature, layer) => {
                        const props = feature.properties || {};
                        const name = props.name || '';
                        const amharic = props.amharic_name ? ` (${props.amharic_name})` : '';
                        layer.bindTooltip(`<b>${name}</b>${amharic}`, {
                            sticky: true,
                            className: 'subcity-boundary-tooltip',
                        });
                        layer.on({
                            mouseover: (e) => {
                                e.target.setStyle({
                                    weight: 2.5,
                                    opacity: 0.9,
                                    fillOpacity: 0.12,
                                    color: '#ffd700',
                                });
                            },
                            mouseout: (e) => {
                                e.target.setStyle({
                                    weight: 1.5,
                                    opacity: 0.65,
                                    fillOpacity: 0.04,
                                    color: '#d4af37',
                                });
                            },
                        });
                    },
                }).addTo(map);
            }
        })
        .catch((err) => console.debug('Subcity boundaries overlay skipped:', err));

    // "Search this area" — only offered once the visitor moves the map themselves.
    const searchAreaButton = document.getElementById('search-map-area-btn');
    if (searchAreaButton) {
        let userHasMoved = false;
        setTimeout(() => {
            userHasMoved = true;
        }, 500);

        map.on('moveend', () => {
            if (!userHasMoved) {
                return;
            }
            searchAreaButton.classList.remove('hidden');
            searchAreaButton.classList.add('inline-flex');
        });

        searchAreaButton.addEventListener('click', () => {
            const box = map.getBounds();
            const southWest = box.getSouthWest();
            const northEast = box.getNorthEast();
            const centre = map.getCenter();

            const url = new URL(window.location.href);
            url.searchParams.set('map_bounds', [southWest.lat, southWest.lng, northEast.lat, northEast.lng]
                .map((value) => value.toFixed(6))
                .join(','));
            url.searchParams.set('map_center', [centre.lat, centre.lng].map((value) => value.toFixed(6)).join(','));
            url.searchParams.set('map_zoom', String(map.getZoom()));

            window.location.assign(url.toString());
        });
    }

    // Re-render pins when currency switches
    window.addEventListener('currency-changed', () => {
        renderMarkers();
    });

    // Synchronize hover on property cards in the grid
    document.querySelectorAll('[data-property-card-id]').forEach((card) => {
        const id = parseInt(card.getAttribute('data-property-card-id'), 10);
        card.addEventListener('mouseenter', () => {
            const marker = markersMap.get(id);
            if (marker) {
                marker.openPopup();
                map.panTo(marker.getLatLng(), { animate: true, duration: 0.5 });
            }
        });
    });

    // Map toggle visibility
    const mapToggleBtn = document.getElementById('toggle-map-view-btn');
    const mapWrapper = document.getElementById('map-view-wrapper');
    if (mapToggleBtn && mapWrapper) {
        mapToggleBtn.addEventListener('click', () => {
            const isHidden = mapWrapper.classList.toggle('hidden');
            mapToggleBtn.setAttribute('aria-expanded', !isHidden);
            if (!isHidden) {
                mapToggleBtn.innerHTML = `
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    <span>Hide Map</span>
                `;
                setTimeout(() => map.invalidateSize(), 200);
            } else {
                mapToggleBtn.innerHTML = `
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span>Show Map View</span>
                `;
            }
        });
    }
}

/**
 * Development project site marker on the public project page.
 * Coordinates come from the map picker used at registration time.
 */
function initProjectLocationMap() {
    const element = document.getElementById('project-location-map');
    if (!element || typeof Leaflet === 'undefined' || element._leaflet_id) {
        return;
    }

    const lat = parseFloat(element.dataset.lat) || ADDIS_ABABA_CENTER[0];
    const lng = parseFloat(element.dataset.lng) || ADDIS_ABABA_CENTER[1];
    const name = element.dataset.name || 'Development Project';
    const developer = element.dataset.developer || '';
    const address = element.dataset.address || 'Addis Ababa';
    const price = parseFloat(element.dataset.price) || 0;
    const units = element.dataset.units || '0';
    const handover = element.dataset.handover || 'TBD';
    const projectUrl = element.dataset.url || '#';

    const map = Leaflet.map(element, {
        center: [lat, lng],
        zoom: 14,
        zoomControl: true,
        scrollWheelZoom: false,
    });

    createInteractiveTileLayer().addTo(map);

    setTimeout(() => map.invalidateSize(), 60);
    setTimeout(() => map.invalidateSize(), 250);

    let marker = null;

    function renderMarker() {
        if (marker) {
            map.removeLayer(marker);
        }

        const icon = Leaflet.divIcon({
            className: 'custom-leaflet-pin',
            html: createLuxuryPinHtml(formatPrice(price, getActiveCurrency()), true),
            iconSize: [110, 36],
            iconAnchor: [55, 36],
            popupAnchor: [0, -38],
        });

        marker = Leaflet.marker([lat, lng], { icon }).addTo(map);
        marker.bindPopup(`
            <div class="overflow-hidden rounded-2xl bg-white font-sans shadow-xl text-slate-800" style="min-width: 230px; max-width: 280px;">
                <div class="p-3">
                    <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Development Project</span>
                    <h4 class="mt-1.5 font-bold text-sm text-[#102b25]">${name}</h4>
                    ${developer ? `<p class="text-[11px] font-semibold text-slate-500">${developer}</p>` : ''}
                    <p class="text-xs text-slate-500 mt-1">${address}</p>
                    <div class="mt-2 flex items-center justify-between border-t border-[#f0e4cf] pt-2 text-[11px] text-slate-600">
                        <span>${units} units</span>
                        <span>Handover: ${handover}</span>
                    </div>
                    <a href="${projectUrl}" class="mt-2.5 block w-full rounded-xl bg-[#102b25] py-2 text-center text-xs font-bold text-[#fdf8f0] transition hover:bg-[#1d3c34]">
                        View Project &rarr;
                    </a>
                </div>
            </div>
        `, { maxWidth: 300, className: 'luxury-leaflet-popup' });
    }

    renderMarker();

    window.addEventListener('currency-changed', renderMarker);
}

function initSinglePropertyMap() {
    const singleMapEl = document.getElementById('single-property-map');
    if (!singleMapEl || typeof Leaflet === 'undefined' || singleMapEl._leaflet_id) return;

    const lat = parseFloat(singleMapEl.getAttribute('data-lat')) || ADDIS_ABABA_CENTER[0];
    const lng = parseFloat(singleMapEl.getAttribute('data-lng')) || ADDIS_ABABA_CENTER[1];
    const title = singleMapEl.getAttribute('data-title') || 'Property Location';
    const address = singleMapEl.getAttribute('data-address') || 'Addis Ababa';
    const price = parseFloat(singleMapEl.getAttribute('data-price')) || 0;

    let amenitiesGrouped = {};
    try {
        amenitiesGrouped = JSON.parse(singleMapEl.getAttribute('data-amenities') || '{}');
    } catch (e) {
        amenitiesGrouped = {};
    }

    const map = Leaflet.map(singleMapEl, {
        center: [lat, lng],
        zoom: 14,
        zoomControl: true,
        scrollWheelZoom: false,
    });

    createInteractiveTileLayer().addTo(map);

    setTimeout(() => map.invalidateSize(), 60);
    setTimeout(() => map.invalidateSize(), 250);

    const bounds = Leaflet.latLngBounds([[lat, lng]]);

    function updateSinglePin() {
        const formatted = formatPrice(price, getActiveCurrency());
        const customIcon = Leaflet.divIcon({
            className: 'custom-leaflet-pin',
            html: createLuxuryPinHtml(formatted, true),
            iconSize: [110, 36],
            iconAnchor: [55, 36],
            popupAnchor: [0, -38],
        });

        const marker = Leaflet.marker([lat, lng], { icon: customIcon }).addTo(map);
        marker.bindPopup(`
            <div class="p-2 text-slate-800">
                <span class="rounded bg-[#102b25] px-2 py-0.5 text-[9px] font-black uppercase text-[#d4af37]">Featured Property</span>
                <h4 class="font-bold text-sm text-[#102b25] mt-1">${title}</h4>
                <p class="text-xs text-slate-500 mt-0.5">${address}</p>
                <a href="https://www.google.com/maps/search/?api=1&query=${lat},${lng}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-[#102b25] hover:text-[#d4af37]">
                    Open in Google Maps &rarr;
                </a>
            </div>
        `).openPopup();
    }

    updateSinglePin();

    // Plot Amenity Pins on Map
    const categoryColors = {
        education: '#2563eb',
        health: '#dc2626',
        transit: '#059669',
        shopping: '#d97706',
        diplomatic: '#7c3aed',
        lifestyle: '#16a34a',
    };

    Object.entries(amenitiesGrouped).forEach(([category, items]) => {
        if (!Array.isArray(items)) return;

        items.forEach((item) => {
            if (!item.lat || !item.lng) return;

            const color = categoryColors[category] || '#102b25';
            const iconHtml = `
                <div class="flex items-center justify-center h-7 w-7 rounded-full text-white shadow-md border-2 border-white transition-transform hover:scale-125 cursor-pointer" style="background-color: ${color};">
                    <span class="text-xs">${item.icon || '📍'}</span>
                </div>
            `;

            const icon = Leaflet.divIcon({
                className: 'amenity-leaflet-icon',
                html: iconHtml,
                iconSize: [28, 28],
                iconAnchor: [14, 14],
                popupAnchor: [0, -14],
            });

            const poiMarker = Leaflet.marker([item.lat, item.lng], { icon }).addTo(map);
            poiMarker.bindPopup(`
                <div class="p-2 text-slate-800 text-xs">
                    <div class="flex items-center gap-1.5 font-bold capitalize text-slate-500">
                        <span>${item.icon || '📍'}</span>
                        <span>${item.category || category}</span>
                    </div>
                    <h5 class="font-black text-sm text-[#102b25] mt-0.5">${item.name}</h5>
                    ${item.amharic ? `<p class="text-[11px] text-slate-600 font-medium">${item.amharic}</p>` : ''}
                    <div class="mt-2 pt-2 border-t border-slate-200 flex items-center gap-3 text-[11px]">
                        <span class="font-black text-[#102b25]">${item.formatted_distance || (item.distance_km + ' km')}</span>
                        <span>•</span>
                        <span class="text-slate-600">🚶 ${item.walk_minutes} min walk</span>
                        <span>•</span>
                        <span class="text-slate-600">🚗 ${item.drive_minutes} min drive</span>
                    </div>
                </div>
            `);

            bounds.extend([item.lat, item.lng]);
        });
    });

    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
    }

    window.addEventListener('currency-changed', () => {
        updateSinglePin();
    });
}
