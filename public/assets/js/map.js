// Bản đồ chỉ đọc cho Detail. Không gọi geocode hoặc yêu cầu vị trí của người xem.
(function () {
    'use strict';
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
})();
