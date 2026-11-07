// Xử lý tương tác phía trình duyệt cho form đăng địa điểm (TV3-02).
(function () {
    'use strict';

    var MAX_IMAGES = 3;
    var MIN_IMAGES = 1;
    var MAX_TAGS = 5;
    var MAX_IMAGE_SIZE_BYTES = 5 * 1024 * 1024;
    var ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    var formElement = document.getElementById('place-create-form');
    if (!formElement) {
        return;
    }
    var isEditing = formElement.getAttribute('data-editing') === 'true';
    var existingImageCount = Number(formElement.getAttribute('data-existing-image-count') || '0');

    var titleInput = document.getElementById('place-title');
    var descriptionInput = document.getElementById('place-description');
    var categorySelect = document.getElementById('place-category');
    var provinceSelect = document.getElementById('place-province');
    var wardSelect = document.getElementById('place-ward');
    var wardHint = document.getElementById('place-ward-hint');
    var addressInput = document.getElementById('place-address');
    var latitudeInput = document.getElementById('place-latitude');
    var longitudeInput = document.getElementById('place-longitude');
    var geocodeButton = document.getElementById('place-geocode-btn');
    var clearCoordsButton = document.getElementById('place-clear-coords-btn');
    var mapReadout = document.getElementById('place-map-coord-readout');
    var geocodeStatus = document.getElementById('place-geocode-status');
    var geocodeResults = document.getElementById('place-geocode-results');
    var geocodeRequest = null;
    var geocodeSequence = 0;

    var tagFilterInput = document.getElementById('place-tag-filter');
    var selectedTagsContainer = document.getElementById('place-selected-tags');
    var tagCountElement = formElement.querySelector('[data-place-tag-count]');
    var tagsFeedback = document.getElementById('place-tags-feedback');
    var tagCheckboxes = Array.prototype.slice.call(
        formElement.querySelectorAll('[data-place-tag-checkbox]')
    );

    var imageInput = document.getElementById('place-images');
    var dropzoneElement = document.getElementById('place-dropzone');
    var imagePreviewsContainer = document.getElementById('place-image-previews');
    var imageCountElement = formElement.querySelector('[data-place-image-count]');
    var imagesFeedback = document.getElementById('place-images-feedback');

    var titleCountElement = formElement.querySelector('[data-place-title-count]');
    var descriptionCountElement = formElement.querySelector('[data-place-description-count]');
    var statusBanner = document.getElementById('place-form-status');

    var selectedImageFiles = [];
    var activeObjectUrls = [];

    // Hiển thị hoặc ẩn thông báo lỗi của từng trường.
    function setFieldError(errorElementId, message) {
        var errorElement = document.getElementById(errorElementId);
        if (!errorElement) {
            return;
        }

        if (message && message !== '') {
            errorElement.textContent = message;
            errorElement.hidden = false;
        } else {
            errorElement.textContent = '';
            errorElement.hidden = true;
        }
    }

    // Hiển thị thông báo tổng quát ở đầu form.
    function setFormStatus(type, message) {
        if (!statusBanner) {
            return;
        }

        if (!message) {
            statusBanner.hidden = true;
            statusBanner.textContent = '';
            statusBanner.className = 'alert hs-place-form-banner';
            return;
        }

        statusBanner.className = 'alert alert-' + type + ' hs-place-form-banner';
        statusBanner.textContent = message;
        statusBanner.hidden = false;
    }

    // Cập nhật bộ đếm ký tự cho Tên địa điểm và Mô tả.
    function updateCharacterCounters() {
        if (titleInput && titleCountElement) {
            titleCountElement.textContent = String(Array.from(titleInput.value).length);
        }

        if (descriptionInput && descriptionCountElement) {
            descriptionCountElement.textContent = String(Array.from(descriptionInput.value).length);
        }
    }

    // Đếm các nhãn đang được chọn.
    function getCheckedTagCheckboxes() {
        var checkedList = [];
        for (var index = 0; index < tagCheckboxes.length; index++) {
            if (tagCheckboxes[index].checked) {
                checkedList.push(tagCheckboxes[index]);
            }
        }
        return checkedList;
    }

    // Dựng lại các thẻ nhãn đã chọn có nút xóa nhanh (×).
    function renderSelectedTags() {
        var checkedList = getCheckedTagCheckboxes();

        if (tagCountElement) {
            tagCountElement.textContent = String(checkedList.length);
        }

        for (var checkboxIndex = 0; checkboxIndex < tagCheckboxes.length; checkboxIndex++) {
            var checkbox = tagCheckboxes[checkboxIndex];
            var parentLabel = checkbox.closest('.hs-place-tag-choice');
            if (parentLabel) {
                if (checkbox.checked) {
                    parentLabel.classList.add('is-selected');
                } else {
                    parentLabel.classList.remove('is-selected');
                }

                if (!checkbox.checked && checkedList.length >= MAX_TAGS) {
                    parentLabel.classList.add('is-limit-reached');
                } else {
                    parentLabel.classList.remove('is-limit-reached');
                }
            }
        }

        if (!selectedTagsContainer) {
            return;
        }

        selectedTagsContainer.innerHTML = '';

        for (var selectedTagIndex = 0; selectedTagIndex < checkedList.length; selectedTagIndex++) {
            appendSelectedTag(checkedList[selectedTagIndex]);
        }
    }

    // Mỗi lần gọi giữ riêng checkbox cho nút bỏ nhãn, không cần hàm tự gọi trong vòng lặp.
    function appendSelectedTag(checkedBox) {
        var tagName = checkedBox.getAttribute('data-tag-name') || checkedBox.value;
        var chip = document.createElement('span');
        chip.className = 'hs-place-selected-tag';

        var textSpan = document.createElement('span');
        textSpan.textContent = tagName;

        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'hs-place-selected-tag__remove';
        removeButton.setAttribute('aria-label', 'Bỏ nhãn ' + tagName);
        removeButton.textContent = '×';
        removeButton.addEventListener('click', function () {
            checkedBox.checked = false;
            setFieldError('place-tags-feedback', '');
            renderSelectedTags();
        });

        chip.appendChild(textSpan);
        chip.appendChild(removeButton);
        selectedTagsContainer.appendChild(chip);
    }

    // Xử lý khi người dùng chọn hoặc bỏ chọn một nhãn.
    function handleTagCheckboxChange(event) {
        var targetCheckbox = event.target;
        var checkedList = getCheckedTagCheckboxes();

        if (checkedList.length > MAX_TAGS) {
            targetCheckbox.checked = false;
            setFieldError(
                'place-tags-feedback',
                'Cảnh báo: Chỉ được chọn tối đa ' + MAX_TAGS + ' nhãn cho mỗi địa điểm. Vui lòng bỏ bớt nhãn đã chọn trước khi thêm nhãn mới.'
            );
            renderSelectedTags();
            return;
        }

        setFieldError('place-tags-feedback', '');
        renderSelectedTags();
    }

    // Lọc danh sách nhãn có sẵn theo từ khóa nhập vào ô tìm nhãn.
    function filterTagOptions() {
        if (!tagFilterInput) {
            return;
        }

        var keyword = tagFilterInput.value.trim().toLowerCase();

        for (var checkboxIndex = 0; checkboxIndex < tagCheckboxes.length; checkboxIndex++) {
            var parentLabel = tagCheckboxes[checkboxIndex].closest('.hs-place-tag-choice');
            if (!parentLabel) {
                continue;
            }
            var tagName = (tagCheckboxes[checkboxIndex].getAttribute('data-tag-name') || '').toLowerCase();
            if (keyword === '' || tagName.indexOf(keyword) !== -1) {
                parentLabel.hidden = false;
            } else {
                parentLabel.hidden = true;
            }
        }
    }

    // Kiểm tra cặp tọa độ tùy chọn: hoặc cùng để trống, hoặc cùng hợp lệ (không nhận 0,0 giả).
    function validateCoordinates() {
        if (!latitudeInput || !longitudeInput) {
            return true;
        }

        var rawLatitude = latitudeInput.value.trim();
        var rawLongitude = longitudeInput.value.trim();

        if (rawLatitude === '' && rawLongitude === '') {
            setFieldError('place-coords-error', '');
            if (mapReadout) {
                mapReadout.textContent = 'Chưa nhập tọa độ (không bắt buộc)';
            }
            return true;
        }

        if (rawLatitude === '' || rawLongitude === '') {
            setFieldError(
                'place-coords-error',
                'Vĩ độ và kinh độ phải đi theo cặp (nhập cả hai hoặc để trống cả hai).'
            );
            return false;
        }

        var latitudeNumber = Number(rawLatitude);
        var longitudeNumber = Number(rawLongitude);

        if (!Number.isFinite(latitudeNumber) || !Number.isFinite(longitudeNumber)) {
            setFieldError('place-coords-error', 'Tọa độ phải là số thập phân hợp lệ.');
            return false;
        }

        if (latitudeNumber < -90 || latitudeNumber > 90 || longitudeNumber < -180 || longitudeNumber > 180) {
            setFieldError(
                'place-coords-error',
                'Tọa độ ngoài phạm vi hợp lệ (Vĩ độ: -90 đến 90, Kinh độ: -180 đến 180).'
            );
            return false;
        }

        if (latitudeNumber === 0 && longitudeNumber === 0) {
            setFieldError(
                'place-coords-error',
                'Không sử dụng tọa độ giả (0, 0). Vui lòng để trống nếu chưa có tọa độ.'
            );
            return false;
        }

        setFieldError('place-coords-error', '');
        if (mapReadout) {
            mapReadout.textContent = 'Tọa độ: ' + latitudeNumber.toFixed(5) + ', ' + longitudeNumber.toFixed(5);
        }
        return true;
    }

    // Giải phóng các URL xem trước ảnh cũ trong bộ nhớ trình duyệt.
    function revokePreviewUrls() {
        for (var previewUrlIndex = 0; previewUrlIndex < activeObjectUrls.length; previewUrlIndex++) {
            URL.revokeObjectURL(activeObjectUrls[previewUrlIndex]);
        }
        activeObjectUrls = [];
    }

    // Đồng bộ mảng file đã chọn vào input[type="file"].
    function syncFilesToInput() {
        if (!imageInput || typeof DataTransfer === 'undefined') {
            return;
        }

        try {
            var dataTransfer = new DataTransfer();
            for (var imageIndex = 0; imageIndex < selectedImageFiles.length; imageIndex++) {
                dataTransfer.items.add(selectedImageFiles[imageIndex]);
            }
            imageInput.files = dataTransfer.files;
        } catch (error) {
            // Một số trình duyệt cũ không cho gán lại input.files.
        }
    }

    // Hiển thị ảnh xem trước và nút xóa từng ảnh.
    function renderImagePreviews() {
        revokePreviewUrls();

        if (imageCountElement) {
            var displayedCount = selectedImageFiles.length;
            if (isEditing && displayedCount === 0) {
                displayedCount = existingImageCount;
            }
            imageCountElement.textContent = String(displayedCount);
        }

        if (!imagePreviewsContainer) {
            return;
        }

        imagePreviewsContainer.innerHTML = '';

        if (selectedImageFiles.length === 0) {
            var emptyPlaceholder = document.createElement('div');
            emptyPlaceholder.className = 'hs-place-form__preview-placeholder';
            emptyPlaceholder.textContent = 'Chưa có hình ảnh nào được chọn. Hãy chọn từ 1 đến 3 ảnh minh họa địa điểm.';
            if (isEditing && existingImageCount > 0) {
                emptyPlaceholder.textContent = 'Chưa chọn ảnh thay thế. Bộ ảnh hiện tại sẽ được giữ nguyên.';
            }
            imagePreviewsContainer.appendChild(emptyPlaceholder);
            return;
        }

        for (var imageIndex = 0; imageIndex < selectedImageFiles.length; imageIndex++) {
            appendImagePreview(imageIndex);
        }
    }

    // Mỗi thẻ ảnh giữ chỉ số riêng; khi xóa sẽ dựng lại danh sách và gán lại chỉ số.
    function appendImagePreview(fileIndex) {
        var file = selectedImageFiles[fileIndex];
        var objectUrl = URL.createObjectURL(file);
        activeObjectUrls.push(objectUrl);

        var itemCard = document.createElement('div');
        itemCard.className = 'hs-place-form__preview-item';

        var imageElement = document.createElement('img');
        imageElement.className = 'hs-place-form__preview-img';
        imageElement.src = objectUrl;
        imageElement.alt = 'Ảnh xem trước ' + (fileIndex + 1) + ': ' + file.name;

        var badgeElement = document.createElement('span');
        badgeElement.className = 'hs-place-form__preview-badge';
        if (fileIndex === 0) {
            badgeElement.textContent = 'Ảnh bìa';
        } else {
            badgeElement.textContent = 'Ảnh ' + (fileIndex + 1);
        }

        var removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'hs-place-form__preview-remove';
        removeButton.setAttribute('aria-label', 'Xóa ảnh ' + file.name);
        removeButton.textContent = '×';
        removeButton.addEventListener('click', function () {
            selectedImageFiles.splice(fileIndex, 1);
            syncFilesToInput();
            setFieldError('place-images-feedback', '');
            renderImagePreviews();
        });

        itemCard.appendChild(imageElement);
        itemCard.appendChild(badgeElement);
        itemCard.appendChild(removeButton);
        imagePreviewsContainer.appendChild(itemCard);
    }

    // Kiểm tra và tiếp nhận danh sách file người dùng chọn hoặc kéo thả.
    function handleIncomingFiles(fileList) {
        if (!fileList || fileList.length === 0) {
            return;
        }

        var incomingArray = Array.prototype.slice.call(fileList);
        var invalidTypeMessages = [];
        var validIncoming = [];

        for (var incomingFileIndex = 0; incomingFileIndex < incomingArray.length; incomingFileIndex++) {
            var currentFile = incomingArray[incomingFileIndex];
            if (ALLOWED_IMAGE_TYPES.indexOf(currentFile.type) === -1) {
                invalidTypeMessages.push('File "' + currentFile.name + '" không đúng định dạng JPG, PNG hoặc WebP.');
                continue;
            }
            if (currentFile.size > MAX_IMAGE_SIZE_BYTES) {
                invalidTypeMessages.push('File "' + currentFile.name + '" vượt quá dung lượng tối đa 5MB.');
                continue;
            }
            validIncoming.push(currentFile);
        }

        var combinedCount = selectedImageFiles.length + validIncoming.length;

        if (combinedCount > MAX_IMAGES) {
            var allowedSlots = Math.max(0, MAX_IMAGES - selectedImageFiles.length);
            for (var acceptedFileIndex = 0; acceptedFileIndex < allowedSlots; acceptedFileIndex++) {
                selectedImageFiles.push(validIncoming[acceptedFileIndex]);
            }
            syncFilesToInput();
            renderImagePreviews();
            setFieldError(
                'place-images-feedback',
                'Cảnh báo: Bạn đã chọn ' + combinedCount + ' ảnh. Mỗi địa điểm chỉ cho phép tối đa ' + MAX_IMAGES + ' hình ảnh (từ 1 đến 3 ảnh).'
            );
            return;
        }

        for (var validFileIndex = 0; validFileIndex < validIncoming.length; validFileIndex++) {
            selectedImageFiles.push(validIncoming[validFileIndex]);
        }

        syncFilesToInput();
        renderImagePreviews();

        if (invalidTypeMessages.length > 0) {
            setFieldError('place-images-feedback', invalidTypeMessages.join(' '));
        } else {
            setFieldError('place-images-feedback', '');
        }
    }

    // Kiểm tra toàn bộ form trước khi gửi.
    function validateFormBeforeSubmit() {
        var isValid = true;
        var firstInvalidElement = null;

        function markInvalid(element) {
            isValid = false;
            if (!firstInvalidElement && element) {
                firstInvalidElement = element;
            }
        }

        var titleValue = '';
        if (titleInput) {
            titleValue = titleInput.value.trim();
        }
        if (titleValue === '') {
            setFieldError('place-title-error', 'Vui lòng nhập tên địa điểm.');
            markInvalid(titleInput);
        } else {
            setFieldError('place-title-error', '');
        }

        var descriptionValue = '';
        if (descriptionInput) {
            descriptionValue = descriptionInput.value.trim();
        }
        if (descriptionValue === '') {
            setFieldError('place-description-error', 'Vui lòng nhập mô tả giới thiệu địa điểm.');
            markInvalid(descriptionInput);
        } else {
            setFieldError('place-description-error', '');
        }

        var categoryValue = '';
        if (categorySelect) {
            categoryValue = categorySelect.value.trim();
        }
        if (categoryValue === '') {
            setFieldError('place-category-error', 'Vui lòng chọn danh mục cho địa điểm.');
            markInvalid(categorySelect);
        } else {
            setFieldError('place-category-error', '');
        }

        var checkedTags = getCheckedTagCheckboxes();
        if (checkedTags.length > MAX_TAGS) {
            setFieldError(
                'place-tags-feedback',
                'Chỉ được chọn tối đa ' + MAX_TAGS + ' nhãn.'
            );
            markInvalid(tagFilterInput);
        }

        var provinceValue = '';
        if (provinceSelect) {
            provinceValue = provinceSelect.value.trim();
        }
        if (provinceValue === '') {
            setFieldError('place-province-error', 'Vui lòng chọn tỉnh/thành phố.');
            markInvalid(provinceSelect);
        } else {
            setFieldError('place-province-error', '');
        }

        var addressValue = '';
        if (addressInput) {
            addressValue = addressInput.value.trim();
        }
        if (addressValue === '') {
            setFieldError('place-address-error', 'Vui lòng nhập địa chỉ chi tiết.');
            markInvalid(addressInput);
        } else {
            setFieldError('place-address-error', '');
        }

        // Phường/xã và tọa độ là tùy chọn; chỉ báo lỗi nếu nhập tọa độ sai định dạng.
        if (!validateCoordinates()) {
            markInvalid(latitudeInput);
        }

        var finalImageCount = selectedImageFiles.length;
        if (isEditing && finalImageCount === 0) {
            finalImageCount = existingImageCount;
        }
        if (finalImageCount < MIN_IMAGES || finalImageCount > MAX_IMAGES) {
            setFieldError(
                'place-images-feedback',
                'Vui lòng chọn từ ' + MIN_IMAGES + ' đến ' + MAX_IMAGES + ' hình ảnh hợp lệ cho địa điểm.'
            );
            markInvalid(dropzoneElement || imageInput);
        } else {
            setFieldError('place-images-feedback', '');
        }

        if (!isValid && firstInvalidElement && typeof firstInvalidElement.focus === 'function') {
            firstInvalidElement.focus();
        }

        return isValid;
    }

    // Đăng ký sự kiện đếm ký tự.
    if (titleInput) {
        titleInput.addEventListener('input', function () {
            updateCharacterCounters();
            if (titleInput.value.trim() !== '') {
                setFieldError('place-title-error', '');
            }
        });
    }

    if (descriptionInput) {
        descriptionInput.addEventListener('input', function () {
            updateCharacterCounters();
            if (descriptionInput.value.trim() !== '') {
                setFieldError('place-description-error', '');
            }
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            if (categorySelect.value.trim() !== '') {
                setFieldError('place-category-error', '');
            }
        });
    }

    if (addressInput) {
        addressInput.addEventListener('input', function () {
            if (addressInput.value.trim() !== '') {
                setFieldError('place-address-error', '');
            }
        });
    }

    // Đăng ký sự kiện cho các checkbox nhãn.
    for (var t = 0; t < tagCheckboxes.length; t++) {
        tagCheckboxes[t].addEventListener('change', handleTagCheckboxChange);
    }

    if (tagFilterInput) {
        tagFilterInput.addEventListener('input', filterTagOptions);
        tagFilterInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                var keyword = tagFilterInput.value.trim().toLowerCase();
                if (keyword === '') {
                    return;
                }
                for (var checkboxIndex = 0; checkboxIndex < tagCheckboxes.length; checkboxIndex++) {
                    var checkbox = tagCheckboxes[checkboxIndex];
                    var tagName = (checkbox.getAttribute('data-tag-name') || '').trim().toLowerCase();
                    if (tagName === keyword) {
                        if (!checkbox.checked) {
                            checkbox.checked = true;
                            handleTagCheckboxChange({ target: checkbox });
                        }
                        return;
                    }
                }
                setFieldError(
                    'place-tags-feedback',
                    'Chỉ được chọn từ danh sách nhãn có sẵn của hệ thống (tối đa 5 nhãn).'
                );
            }
        });
    }

    if (provinceSelect) {
        provinceSelect.addEventListener('change', function () {
            if (provinceSelect.value.trim() !== '') {
                setFieldError('place-province-error', '');
            }
        });
    }

    if (provinceSelect && wardSelect && window.HotSpotLocations) {
        window.HotSpotLocations.bindWardSelect(
            provinceSelect,
            wardSelect,
            wardHint,
            formElement.getAttribute('data-wards-url') || '',
            wardSelect.getAttribute('data-initial-ward') || ''
        );
    }

    // Hủy kết quả cũ khi người dùng đổi địa chỉ, chọn ghim hoặc xóa tọa độ.
    function cancelGeocode() {
        geocodeSequence += 1;
        if (geocodeRequest) {
            geocodeRequest.abort();
            geocodeRequest = null;
        }
        if (geocodeButton) {
            geocodeButton.disabled = false;
            geocodeButton.setAttribute('aria-disabled', 'false');
            geocodeButton.textContent = 'Tìm vị trí theo địa chỉ';
        }
        if (geocodeResults) {
            geocodeResults.replaceChildren();
            geocodeResults.hidden = true;
        }
        if (geocodeStatus) {
            geocodeStatus.textContent = '';
        }
    }

    // Chỉ gọi API TV3 khi bấm tìm, không autocomplete; lỗi mạng không ảnh hưởng submit.
    async function findLocation() {
        cancelGeocode();
        var address = addressInput.value.trim();
        var provinceCode = provinceSelect.value;
        if (address.length < 3 || !provinceCode) {
            geocodeStatus.textContent = 'Vui lòng nhập địa chỉ chi tiết và chọn tỉnh/thành trước khi tìm.';
            return;
        }
        var sequence = geocodeSequence;
        var controller = new AbortController();
        geocodeRequest = controller;
        var timer = window.setTimeout(function () { controller.abort(); }, 8000);
        geocodeButton.disabled = true;
        geocodeButton.setAttribute('aria-disabled', 'true');
        geocodeButton.textContent = 'Đang tìm vị trí...';
        geocodeStatus.textContent = 'Đang tìm. Bạn vẫn có thể tiếp tục điền và gửi bài.';
        try {
            var url = new URL(formElement.getAttribute('data-geocode-url'), window.location.href);
            url.searchParams.set('address', address);
            url.searchParams.set('province_code', provinceCode);
            var response = await fetch(url, { signal: controller.signal, credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-Token': formElement.querySelector('[name="csrf_token"]').value } });
            var payload = await response.json();
            if (sequence !== geocodeSequence) {
                return;
            }
            if (!response.ok || payload.success !== true || !Array.isArray(payload.data && payload.data.results)) {
                throw new Error(payload.message || 'Chưa tìm được vị trí. Bạn có thể nhập hoặc bỏ trống tọa độ.');
            }
            if (payload.data.results.length === 0) {
                geocodeStatus.textContent = 'Không tìm thấy vị trí. Bạn có thể chọn trên bản đồ, nhập hoặc bỏ trống tọa độ.';
                return;
            }
            geocodeStatus.textContent = 'Chọn một kết quả để điền tọa độ; bạn vẫn có thể kéo ghim để điều chỉnh.';
            payload.data.results.slice(0, 3).forEach(function (result) {
                if (typeof result.latitude !== 'number' || typeof result.longitude !== 'number'
                    || !Number.isFinite(result.latitude) || !Number.isFinite(result.longitude)
                    || Math.abs(result.latitude) > 90 || Math.abs(result.longitude) > 180
                    || (result.latitude === 0 && result.longitude === 0) || typeof result.label !== 'string') {
                    return;
                }
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-sm btn-outline-secondary';
                button.textContent = result.label;
                button.addEventListener('click', function () {
                    latitudeInput.value = result.latitude.toFixed(7);
                    longitudeInput.value = result.longitude.toFixed(7);
                    latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    geocodeStatus.textContent = 'Đã chọn vị trí. Kiểm tra ghim hoặc điều chỉnh tọa độ trước khi lưu.';
                    latitudeInput.focus();
                });
                geocodeResults.appendChild(button);
            });
            geocodeResults.hidden = geocodeResults.children.length === 0;
        } catch (error) {
            if (sequence === geocodeSequence) {
                var errorMessage = error.message || 'Chưa thể tìm vị trí. Bạn vẫn có thể lưu bài không có tọa độ.';
                if (error.name === 'AbortError') {
                    errorMessage = 'Tìm vị trí mất quá lâu. Bạn vẫn có thể nhập hoặc bỏ trống tọa độ để lưu bài.';
                }
                geocodeStatus.textContent = errorMessage;
            }
        } finally {
            window.clearTimeout(timer);
            if (sequence === geocodeSequence) {
                geocodeRequest = null;
                geocodeButton.disabled = false;
                geocodeButton.setAttribute('aria-disabled', 'false');
                geocodeButton.textContent = 'Tìm vị trí theo địa chỉ';
            }
        }
    }

    // Thay thông tin đầu vào sẽ không dùng kết quả tìm cũ để ghi đè tọa độ mới.
    [addressInput, provinceSelect, wardSelect].forEach(function (input) {
        if (input) {
            input.addEventListener('change', cancelGeocode);
            if (input === addressInput) {
                input.addEventListener('input', cancelGeocode);
            }
        }
    });

    // Đăng ký sự kiện tọa độ; giữ bộ kiểm tra và submit của T06.
    if (latitudeInput) {
        latitudeInput.addEventListener('input', validateCoordinates);
        latitudeInput.addEventListener('input', cancelGeocode);
    }

    if (longitudeInput) {
        longitudeInput.addEventListener('input', validateCoordinates);
        longitudeInput.addEventListener('input', cancelGeocode);
    }

    if (clearCoordsButton) {
        clearCoordsButton.addEventListener('click', function () {
            if (latitudeInput) {
                latitudeInput.value = '';
            }
            if (longitudeInput) {
                longitudeInput.value = '';
            }
            latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
            longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    if (geocodeButton) {
        geocodeButton.disabled = false;
        geocodeButton.setAttribute('aria-disabled', 'false');
        geocodeButton.addEventListener('click', findLocation);
    }

    // Đăng ký sự kiện chọn file ảnh và kéo thả ảnh.
    if (imageInput) {
        imageInput.addEventListener('change', function () {
            selectedImageFiles = [];
            setFieldError('place-images-feedback', '');
            if (imageInput.files && imageInput.files.length > 0) {
                // Khi chọn lại qua hộp thoại file, thay thế danh sách hiện tại để dễ kiểm soát.
                handleIncomingFiles(imageInput.files);
            } else {
                syncFilesToInput();
                renderImagePreviews();
            }
        });
    }

    if (dropzoneElement && imageInput) {
        dropzoneElement.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                imageInput.click();
            }
        });

        dropzoneElement.addEventListener('dragover', function (event) {
            event.preventDefault();
            dropzoneElement.classList.add('is-dragover');
        });

        dropzoneElement.addEventListener('dragleave', function () {
            dropzoneElement.classList.remove('is-dragover');
        });

        dropzoneElement.addEventListener('drop', function (event) {
            event.preventDefault();
            dropzoneElement.classList.remove('is-dragover');
            if (event.dataTransfer && event.dataTransfer.files) {
                handleIncomingFiles(event.dataTransfer.files);
            }
        });
    }

    var checkFormButton = document.getElementById('place-check-btn');
    if (checkFormButton) {
        checkFormButton.addEventListener('click', function () {
            setFormStatus('', '');

            var isFormValid = validateFormBeforeSubmit();
            if (!isFormValid) {
                setFormStatus(
                    'danger',
                    'Vui lòng kiểm tra và hoàn thiện các trường bắt buộc được đánh dấu màu đỏ bên dưới.'
                );
                return;
            }

            setFormStatus(
                'warning',
                'Thông tin đã nhập trên biểu mẫu đều hợp lệ. Chức năng lưu địa điểm đang được hoàn thiện nên dữ liệu chưa được gửi lưu.'
            );
            if (statusBanner) {
                statusBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    }

    // Kiểm tra dữ liệu trước khi gửi form.
    formElement.addEventListener('submit', function (event) {
        setFormStatus('', '');

        var isFormValid = validateFormBeforeSubmit();
        if (!isFormValid) {
            event.preventDefault();
            setFormStatus(
                'danger',
                'Vui lòng kiểm tra và hoàn thiện các trường bắt buộc được đánh dấu màu đỏ bên dưới.'
            );
            return;
        }

        var isStoreReady = formElement.getAttribute('data-store-ready') === 'true';
        if (!isStoreReady) {
            event.preventDefault();
            setFormStatus(
                'warning',
                'Thông tin đã nhập trên biểu mẫu đều hợp lệ. Chức năng lưu địa điểm đang được hoàn thiện nên dữ liệu chưa được gửi lưu.'
            );
            if (statusBanner) {
                statusBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            return;
        }

        if (event.submitter && event.submitter.hasAttribute('formaction')) {
            formElement.action = event.submitter.getAttribute('formaction');
        }
        var submitButtons = formElement.querySelectorAll('button[type="submit"]');
        for (var buttonIndex = 0; buttonIndex < submitButtons.length; buttonIndex++) {
            submitButtons[buttonIndex].disabled = true;
        }
        if (event.submitter) {
            event.submitter.textContent = 'Đang lưu địa điểm...';
        }
    });

    updateCharacterCounters();
    renderSelectedTags();
    validateCoordinates();
    renderImagePreviews();
})();
