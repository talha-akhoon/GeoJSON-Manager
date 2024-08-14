import L from "leaflet";
import 'leaflet-draw'
import 'leaflet/dist/leaflet.css';
import 'leaflet-draw/dist/leaflet.draw.css';

document.addEventListener('DOMContentLoaded', function() {
    let map, drawnItems;

    if (document.getElementById('map')) {
        initializeMap();
    }

    function initializeMap() {
        map = L.map('map').setView([0, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems
            },
            draw: {
                polygon: true,
                polyline: false,
                rectangle: true,
                circle: false,
                marker: false,
                circlemarker: false
            }
        });
        map.addControl(drawControl);

        map.on(L.Draw.Event.CREATED, function (event) {
            const layer = event.layer;
            drawnItems.addLayer(layer);
            updateDrawnGeoJSON();
        });

        map.on(L.Draw.Event.EDITED, function () {
            updateDrawnGeoJSON();
        });

        map.on(L.Draw.Event.DELETED, function () {
            updateDrawnGeoJSON();
        });

        // Load existing GeoJSON if available
        let drawnGeoJSONContainer = getDrawnGeoJSONComponent()
        const existingGeoJSON = drawnGeoJSONContainer.get('drawnGeoJSON');
        if (existingGeoJSON) {
            loadGeoJSON(existingGeoJSON);
        }
    }

    function updateDrawnGeoJSON() {
        const drawnGeoJSON = drawnItems.toGeoJSON();
        let drawnGeoJSONContainer = getDrawnGeoJSONComponent()
        drawnGeoJSONContainer.call('drawnGeoJSONUpdated', JSON.stringify(drawnGeoJSON));
    }

    function getDrawnGeoJSONComponent() {
        return window.Livewire.find(document.getElementById('map').closest('[wire\\:id]').getAttribute('wire:id'));
    }

    function loadGeoJSON(geoJSON) {
        try {
            const getJSONData = JSON.parse(geoJSON);
            drawnItems.clearLayers();
            L.geoJSON(getJSONData, {
                onEachFeature: function(feature, layer) {
                    drawnItems.addLayer(layer);
                }
            });
            map.fitBounds(drawnItems.getBounds());
        } catch (error) {
            console.error('Error parsing GeoJSON:', error);
        }
    }

    window.Livewire.on('geoJSONUploaded', function(getJSON) {
        loadGeoJSON(getJSON);
        updateDrawnGeoJSON()
    });

});
