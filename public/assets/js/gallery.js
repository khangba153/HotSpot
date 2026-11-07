// Chọn ảnh, xem ảnh lớn và điều khiển bằng bàn phím trên trang Detail.
(function () {
    'use strict';
    var gallery = document.querySelector('[data-place-gallery]');
    if (!gallery) {
        return;
    }
    var thumbnails = Array.prototype.slice.call(gallery.querySelectorAll('[data-gallery-thumbnail]'));
    var mainImage = gallery.querySelector('[data-gallery-main]');
    var opener = gallery.querySelector('[data-gallery-open]');
    var dialog = gallery.querySelector('[data-gallery-dialog]');
    var fullImage = gallery.querySelector('[data-gallery-full]');
    var counter = gallery.querySelector('[data-gallery-counter]');
    var dialogCounter = gallery.querySelector('[data-gallery-dialog-counter]');
    var currentIndex = 0;
    if (!mainImage || !opener || !dialog || !fullImage || thumbnails.length === 0) {
        return;
    }

    // Lỗi ảnh gốc thử ảnh dự phòng một lần; nếu cả hai lỗi thì hiện thông báo.
    function showFallback(image) {
        var fallback = image.getAttribute('data-fallback-src');
        if (image.getAttribute('data-fallback-applied') !== 'true' && fallback) {
            image.setAttribute('data-fallback-applied', 'true');
            image.src = fallback;
            return;
        }
        image.hidden = true;
        var picture = image.closest('[data-gallery-picture]');
        var message = picture.querySelector('[data-gallery-unavailable]');
        if (message) {
            message.hidden = false;
        }
    }

    function setPicture(image, source) {
        image.removeAttribute('data-fallback-applied');
        image.hidden = false;
        var picture = image.closest('[data-gallery-picture]');
        var message = picture.querySelector('[data-gallery-unavailable]');
        if (message) {
            message.hidden = true;
        }
        image.src = source;
    }

    var images = gallery.querySelectorAll('[data-gallery-image]');
    for (var imageIndex = 0; imageIndex < images.length; imageIndex++) {
        images[imageIndex].addEventListener('error', function (event) {
            showFallback(event.target);
        });
        if (images[imageIndex].getAttribute('src') && images[imageIndex].complete && images[imageIndex].naturalWidth === 0) {
            showFallback(images[imageIndex]);
        }
    }

    function selectPhoto(index) {
        currentIndex = (index + thumbnails.length) % thumbnails.length;
        var source = thumbnails[currentIndex].getAttribute('data-image-src');
        var caption = 'Ảnh ' + (currentIndex + 1) + ' / ' + thumbnails.length;
        setPicture(mainImage, source);
        mainImage.alt = thumbnails[currentIndex].getAttribute('aria-label');
        opener.href = source;
        if (dialog.open) {
            setPicture(fullImage, source);
            fullImage.alt = mainImage.alt;
        }
        counter.textContent = caption;
        dialogCounter.textContent = caption;
        for (var indexInList = 0; indexInList < thumbnails.length; indexInList++) {
            thumbnails[indexInList].setAttribute('aria-current', String(indexInList === currentIndex));
        }
    }

    thumbnails.forEach(function (thumbnail, index) {
        thumbnail.addEventListener('click', function () {
            selectPhoto(index);
        });
        thumbnail.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                event.preventDefault();
                var nextIndex = currentIndex + 1;
                if (event.key === 'ArrowLeft') {
                    nextIndex = currentIndex - 1;
                }
                selectPhoto(nextIndex);
                thumbnails[currentIndex].focus();
            }
        });
    });

    opener.addEventListener('click', function (event) {
        if (typeof dialog.showModal !== 'function') {
            return;
        }
        event.preventDefault();
        dialog.showModal();
        selectPhoto(currentIndex);
    });
    gallery.querySelector('[data-gallery-close]').addEventListener('click', function () {
        dialog.close();
    });
    gallery.querySelector('[data-gallery-previous]').addEventListener('click', function () {
        selectPhoto(currentIndex - 1);
    });
    gallery.querySelector('[data-gallery-next]').addEventListener('click', function () {
        selectPhoto(currentIndex + 1);
    });
    dialog.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            var nextIndex = currentIndex + 1;
            if (event.key === 'ArrowLeft') {
                nextIndex = currentIndex - 1;
            }
            selectPhoto(nextIndex);
        }
    });
    dialog.addEventListener('click', function (event) {
        var bounds = dialog.getBoundingClientRect();
        if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right
            || event.clientY < bounds.top || event.clientY > bounds.bottom)) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', function () {
        opener.focus();
    });
})();
