@props([
    'points' => [],
    'selectedTankId' => null,
    'emptyMessage' => 'No location points available.',
])

@php
    $leafletPoints = collect($points)
        ->filter(fn ($point) => isset($point['latitude'], $point['longitude']))
        ->map(fn ($point) => [
            'id' => $point['id'] ?? null,
            'tank_id' => $point['tank_id'] ?? null,
            'tank_name' => $point['tank_name'] ?? 'Tank',
            'province' => $point['province'] ?? null,
            'district' => $point['district'] ?? null,
            'latitude' => (float) $point['latitude'],
            'longitude' => (float) $point['longitude'],
            'source' => $point['source'] ?? null,
            'show_url' => $point['show_url'] ?? null,
            'selected' => (string) ($point['id'] ?? '') === (string) $selectedTankId,
        ])
        ->values();
@endphp

@once
    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIINfQmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            (() => {
                const sriLankaBounds = [[5.85, 79.45], [9.95, 82.05]];
                const maps = new Map();

                const markerIcon = (selected = false) => L.divIcon({
                    className: selected ? 'tank-map-pin tank-map-pin-selected' : 'tank-map-pin',
                    iconSize: [28, 38],
                    iconAnchor: [14, 38],
                    popupAnchor: [0, -34],
                });

                const popupHtml = (point) => {
                    const location = [point.district, point.province].filter(Boolean).join(' / ') || 'Sri Lanka';
                    const link = point.show_url ? `<a href="${point.show_url}" class="tank-map-popup-link">View Location</a>` : '';

                    return `
                        <div class="tank-map-popup">
                            <strong>${point.tank_name || 'Tank'}</strong>
                            <span>${point.tank_id || ''}</span>
                            <span>${location}</span>
                            <small>${point.source || 'Detected location'}</small>
                            ${link}
                        </div>
                    `;
                };

                window.initTankLeafletMaps = () => {
                    if (! window.L) {
                        return;
                    }

                    document.querySelectorAll('[data-tank-leaflet-map]').forEach((element) => {
                        const mapId = element.dataset.mapId || element.id;
                        const points = JSON.parse(element.dataset.points || '[]');

                        if (maps.has(mapId)) {
                            setTimeout(() => maps.get(mapId).invalidateSize(), 80);
                            return;
                        }

                        const map = L.map(element, {
                            scrollWheelZoom: true,
                            maxBounds: sriLankaBounds,
                            maxBoundsViscosity: 0.65,
                        }).setView([7.8731, 80.7718], 7);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 18,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(map);

                        if (points.length > 0) {
                            const bounds = [];

                            points.forEach((point) => {
                                const latLng = [point.latitude, point.longitude];
                                bounds.push(latLng);

                                L.marker(latLng, {
                                    icon: markerIcon(point.selected),
                                }).addTo(map).bindPopup(popupHtml(point));
                            });

                            map.fitBounds(bounds, {
                                padding: [28, 28],
                                maxZoom: points.length === 1 ? 11 : 9,
                            });
                        }

                        maps.set(mapId, map);
                        setTimeout(() => map.invalidateSize(), 120);
                    });
                };

                document.addEventListener('DOMContentLoaded', window.initTankLeafletMaps);
                document.addEventListener('tank-map-modal-opened', () => {
                    setTimeout(window.initTankLeafletMaps, 120);
                });
            })();
        </script>
    @endpush
@endonce

<div {{ $attributes->merge(['class' => 'tank-leaflet-shell']) }}>
    <div
        id="tank-map-{{ uniqid() }}"
        class="tank-leaflet-map"
        data-map-id="{{ uniqid('tank-map-') }}"
        data-tank-leaflet-map
        data-points='@json($leafletPoints)'
    ></div>

    @if ($leafletPoints->isEmpty())
        <div class="tank-map-empty">{{ $emptyMessage }}</div>
    @endif
</div>
