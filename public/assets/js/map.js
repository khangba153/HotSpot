// Leaflet dùng chung: Detail T07 và chọn vị trí trên form T08. Không xin vị trí người xem.
(function () {
    'use strict';
    var picker = document.querySelector('[data-place-map-picker]');
    if (picker) {
        initializePicker(picker);
        return;
    }
    var element = document.querySelector('[data-place-map]');
    var status = document.getElementById('place-map-status');
    if (!element || element.getAttribute('data-map-ready') === 'true') {
        return;
    }
    function showStatus(message) {
        if (status) {
            status.textContent = message;
            status.hidden = false;
        }
    }
    var rawLatitude = element.getAttribute('data-latitude') || '';
    var rawLongitude = element.getAttribute('data-longitude') || '';
    var latitude = Number(rawLatitude);
    var longitude = Number(rawLongitude);
    if (!rawLatitude.trim() || !rawLongitude.trim() || !Number.isFinite(latitude) || !Number.isFinite(longitude)
        || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180
        || (latitude === 0 && longitude === 0)) {
        element.hidden = true;
        showStatus('Địa điểm chưa có tọa độ hợp lệ để hiển thị trên bản đồ.');
        return;
    }

    function initializeMap() {
        if (!window.L) {
            element.hidden = true;
            showStatus('Chưa tải được bản đồ tương tác. Bạn vẫn có thể xem vị trí trên OpenStreetMap.');
            return;
        }
        var map;
        try {
            map = window.L.map(element, { scrollWheelZoom: false }).setView([latitude, longitude], 16);
            var title = element.getAttribute('data-place-title') || 'Vị trí địa điểm';
            var popup = document.createElement('span');
            popup.textContent = title;
            window.L.marker([latitude, longitude], { title: title, alt: 'Vị trí ' + title }).addTo(map).bindPopup(popup);
            var tileErrors = 0;
            var tiles = window.L.tileLayer(element.getAttribute('data-tile-url'), {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors'
            });
            showStatus('Đang tải bản đồ...');
            tiles.on('tileerror', function () {
                tileErrors += 1;
                showStatus('Chưa tải được nền bản đồ. Bạn vẫn có thể xem vị trí trên OpenStreetMap.');
            });
            tiles.on('load', function () {
                if (tileErrors === 0 && status) {
                    status.hidden = true;
                }
            });
            tiles.addTo(map);
            element.setAttribute('data-map-ready', 'true');
        } catch (error) {
            if (map) {
                map.remove();
            }
            element.hidden = true;
            showStatus('Chưa thể hiển thị bản đồ. Bạn vẫn có thể xem vị trí trên OpenStreetMap.');
        }
    }

    // Chỉ tải tile khi bản đồ ở gần vùng nhìn thấy, không tải trước cả khu vực.
    if (typeof IntersectionObserver === 'function') {
        var observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                observer.disconnect();
                initializeMap();
            }
        }, { rootMargin: '100px' });
        observer.observe(element);
    } else {
        initializeMap();
    }
    // Dùng lại Leaflet T07; bản đồ mới không tự tạo marker hay điền tọa độ mặc định.
    function initializePicker(container) {
        var latitudeInput = document.getElementById('place-latitude');
        var longitudeInput = document.getElementById('place-longitude');
        var pickerStatus = document.getElementById('place-map-picker-status');
        var map = null;
        var marker = null;
        var selecting = false;
        var hasTileError = false;
        var instructions = 'Chạm bản đồ để đặt ghim, kéo ghim để chỉnh vị trí. Dùng phím Enter để chọn tâm bản đồ.';

        function coordinates() {
            var latitude = Number(latitudeInput.value.trim());
            var longitude = Number(longitudeInput.value.trim());
            if (!latitudeInput.value.trim() || !longitudeInput.value.trim() || !Number.isFinite(latitude)
                || !Number.isFinite(longitude) || Math.abs(latitude) > 90 || Math.abs(longitude) > 180
                || (latitude === 0 && longitude === 0)) {
                return null;
            }
            return [latitude, longitude];
        }

        function syncMarker() {
            if (!map) {
                return;
            }
            var pair = coordinates();
            if (!pair) {
                if (marker) {
                    marker.remove();
                    marker = null;
                }
                return;
            }
            if (!marker) {
                marker = window.L.marker(pair, { draggable: true, title: 'Kéo để chỉnh vị trí', alt: 'Vị trí đã chọn' }).addTo(map);
                marker.on('dragend', function () { selectPosition(marker.getLatLng()); });
            } else {
                marker.setLatLng(pair);
            }
            if (!selecting) {
                map.setView(pair, 16);
            }
        }

        function selectPosition(position) {
            var wrapped = map.wrapLatLng(position);
            if (!Number.isFinite(wrapped.lat) || !Number.isFinite(wrapped.lng) || Math.abs(wrapped.lat) > 90
                || (Number(wrapped.lat.toFixed(7)) === 0 && Number(wrapped.lng.toFixed(7)) === 0)) {
                pickerStatus.textContent = 'Vị trí chưa hợp lệ. Hãy chọn điểm khác hoặc để trống tọa độ.';
                syncMarker();
                return;
            }
            latitudeInput.value = wrapped.lat.toFixed(7);
            longitudeInput.value = wrapped.lng.toFixed(7);
            selecting = true;
            latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
            longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
            selecting = false;
            if (!hasTileError) {
                pickerStatus.textContent = 'Đã chọn vị trí. ' + instructions;
            }
        }

        function start() {
            if (!window.L) {
                container.hidden = true;
                pickerStatus.textContent = 'Chưa tải được bản đồ. Bạn vẫn có thể tìm theo địa chỉ, nhập hoặc bỏ trống tọa độ.';
                return;
            }
            try {
                var pair = coordinates();
                var initialCenter = [16.0471, 108.2068];
                var initialZoom = 5;
                if (pair) {
                    initialCenter = pair;
                    initialZoom = 16;
                }
                map = window.L.map(container, { scrollWheelZoom: false }).setView(initialCenter, initialZoom);
                var tiles = window.L.tileLayer(container.getAttribute('data-tile-url'), { maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors' });
                tiles.on('tileerror', function () {
                    hasTileError = true;
                    pickerStatus.textContent = 'Chưa tải được nền bản đồ. Bạn vẫn có thể tìm theo địa chỉ, nhập hoặc bỏ trống tọa độ.';
                });
                tiles.addTo(map);
                map.on('click', function (event) { selectPosition(event.latlng); });
                container.addEventListener('keydown', function (event) {
                    if (event.target === container && (event.key === 'Enter' || event.key === ' ')) {
                        event.preventDefault();
                        selectPosition(map.getCenter());
                    }
                });
                syncMarker();
                container.setAttribute('data-map-ready', 'true');
            } catch (error) {
                if (map) {
                    map.remove();
                    map = null;
                }
                container.hidden = true;
                pickerStatus.textContent = 'Chưa thể mở bản đồ. Bạn vẫn có thể nhập hoặc bỏ trống tọa độ.';
            }
        }

        latitudeInput.addEventListener('input', syncMarker);
        longitudeInput.addEventListener('input', syncMarker);
        if (typeof IntersectionObserver === 'function') {
            var observer = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting) {
                    observer.disconnect();
                    start();
                }
            }, { rootMargin: '100px' });
            observer.observe(container);
        } else {
            start();
        }
    }
})();
