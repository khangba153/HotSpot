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
    var wardRequestSequence = 0;
    var activeWardAbortController = null;

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

        for (var i = 0; i < tagCheckboxes.length; i++) {
            var checkbox = tagCheckboxes[i];
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

        for (var j = 0; j < checkedList.length; j++) {
            (function (checkedBox) {
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
            })(checkedList[j]);
        }
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

        for (var i = 0; i < tagCheckboxes.length; i++) {
            var parentLabel = tagCheckboxes[i].closest('.hs-place-tag-choice');
            if (!parentLabel) {
                continue;
            }
            var tagName = (tagCheckboxes[i].getAttribute('data-tag-name') || '').toLowerCase();
            if (keyword === '' || tagName.indexOf(keyword) !== -1) {
                parentLabel.hidden = false;
            } else {
                parentLabel.hidden = true;
            }
        }
    }

    // Làm mới ô chọn Phường/Xã để không giữ lại phường của tỉnh cũ.
    function resetWardOptions(placeholderText) {
        if (!wardSelect) {
            return;
        }

        wardSelect.innerHTML = '';
        var defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = placeholderText || 'Chọn phường/xã';
        wardSelect.appendChild(defaultOption);
        wardSelect.value = '';
        wardSelect.disabled = true;
        wardSelect.setAttribute('aria-busy', 'false');
    }

    // Gọi API phường/xã của TV2 theo mã tỉnh (hợp đồng C03).
    function loadWardsForProvince(provinceCode, preferredWardCode) {
        if (!wardSelect) {
            return;
        }

        if (activeWardAbortController) {
            activeWardAbortController.abort();
            activeWardAbortController = null;
        }

        wardRequestSequence += 1;
        var currentSequence = wardRequestSequence;

        // Luôn xóa lựa chọn phường cũ ngay khi đổi tỉnh.
        resetWardOptions('Chọn tỉnh/thành trước (không bắt buộc)');

        if (wardHint) {
            wardHint.textContent = 'Phường/xã là tùy chọn. Bạn có thể để trống nếu chưa tải được danh sách.';
        }

        if (!provinceCode || provinceCode.trim() === '') {
            return;
        }

        var wardsBaseUrl = formElement.getAttribute('data-wards-url') || '';
        if (wardsBaseUrl === '') {
            resetWardOptions('Chưa tải được phường/xã (không bắt buộc)');
            return;
        }

        resetWardOptions('Đang tải phường/xã...');
        wardSelect.setAttribute('aria-busy', 'true');

        var separator = '&';
        if (wardsBaseUrl.indexOf('?') === -1) {
            separator = '?';
        }
        var requestUrl = wardsBaseUrl + separator + 'province_code=' + encodeURIComponent(provinceCode);

        if (typeof AbortController !== 'undefined') {
            activeWardAbortController = new AbortController();
        }

        var fetchOptions = {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        };

        if (activeWardAbortController) {
            fetchOptions.signal = activeWardAbortController.signal;
        }

        fetch(requestUrl, fetchOptions)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Wards API status ' + response.status);
                }
                return response.json();
            })
            .then(function (payload) {
                // Bỏ qua nếu người dùng đã chuyển sang tỉnh khác trong lúc chờ phản hồi.
                if (currentSequence !== wardRequestSequence) {
                    return;
                }

                resetWardOptions('Chọn phường/xã');

                var wardItems = [];
                if (payload && payload.success === false) {
                    throw new Error('Không tải được danh sách phường/xã.');
                }

                if (payload && payload.data && Array.isArray(payload.data.wards)) {
                    wardItems = payload.data.wards;
                } else if (payload && Array.isArray(payload.data)) {
                    wardItems = payload.data;
                } else if (Array.isArray(payload)) {
                    wardItems = payload;
                } else {
                    throw new Error('Dữ liệu phường/xã không hợp lệ.');
                }

                for (var index = 0; index < wardItems.length; index++) {
                    var wardItem = wardItems[index];
                    if (!wardItem || typeof wardItem !== 'object') {
                        continue;
                    }
                    // Mã địa giới phải là chuỗi để giữ số 0 ở đầu.
                    if (typeof wardItem.ward_code !== 'string') {
                        continue;
                    }
                    if (wardItem.province_code !== undefined && wardItem.province_code !== provinceCode) {
                        continue;
                    }
                    var wardCode = wardItem.ward_code.trim();
                    var wardName = String(wardItem.ward_name || wardItem.name || '').trim();
                    if (wardCode === '' || wardName === '') {
                        continue;
                    }

                    var option = document.createElement('option');
                    option.value = wardCode;
                    option.textContent = wardName;
                    if (preferredWardCode && preferredWardCode === wardCode) {
                        option.selected = true;
                    }
                    wardSelect.appendChild(option);
                }

                wardSelect.disabled = wardSelect.options.length <= 1;
                if (wardSelect.disabled) {
                    resetWardOptions('Không có phường/xã để chọn (không bắt buộc)');
                }
                if (wardHint && !wardSelect.disabled) {
                    wardHint.textContent = 'Bạn có thể chọn phường/xã hoặc để trống. Danh sách sẽ làm mới khi đổi tỉnh/thành.';
                }
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                // Lỗi API hoặc chưa nối C03 không chặn form vì phường/xã là tùy chọn.
                if (currentSequence === wardRequestSequence) {
                    resetWardOptions('Chưa tải được phường/xã (không bắt buộc)');
                    if (wardHint) {
                        wardHint.textContent = 'Chưa tải được danh sách phường/xã. Bạn vẫn có thể tiếp tục và để trống trường này.';
                    }
                }
            });
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
        for (var i = 0; i < activeObjectUrls.length; i++) {
            URL.revokeObjectURL(activeObjectUrls[i]);
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
            for (var i = 0; i < selectedImageFiles.length; i++) {
                dataTransfer.items.add(selectedImageFiles[i]);
            }
            imageInput.files = dataTransfer.files;
        } catch (err) {
            // Một số trình duyệt cũ không cho gán lại input.files.
        }
    }

    // Hiển thị ảnh xem trước và nút xóa từng ảnh.
    function renderImagePreviews() {
        revokePreviewUrls();

        if (imageCountElement) {
            imageCountElement.textContent = String(selectedImageFiles.length);
        }

        if (!imagePreviewsContainer) {
            return;
        }

        imagePreviewsContainer.innerHTML = '';

        if (selectedImageFiles.length === 0) {
            var emptyPlaceholder = document.createElement('div');
            emptyPlaceholder.className = 'hs-place-form__preview-placeholder';
            emptyPlaceholder.textContent = 'Chưa có hình ảnh nào được chọn. Hãy chọn từ 1 đến 3 ảnh minh họa địa điểm.';
            imagePreviewsContainer.appendChild(emptyPlaceholder);
            return;
        }

        for (var index = 0; index < selectedImageFiles.length; index++) {
            (function (fileIndex) {
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
            })(index);
        }
    }

    // Kiểm tra và tiếp nhận danh sách file người dùng chọn hoặc kéo thả.
    function handleIncomingFiles(fileList) {
        if (!fileList || fileList.length === 0) {
            return;
        }

        var incomingArray = Array.prototype.slice.call(fileList);
        var invalidTypeMessages = [];
        var validIncoming = [];

        for (var i = 0; i < incomingArray.length; i++) {
            var currentFile = incomingArray[i];
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
            for (var j = 0; j < allowedSlots; j++) {
                selectedImageFiles.push(validIncoming[j]);
            }
            syncFilesToInput();
            renderImagePreviews();
            setFieldError(
                'place-images-feedback',
                'Cảnh báo: Bạn đã chọn ' + combinedCount + ' ảnh. Mỗi địa điểm chỉ cho phép tối đa ' + MAX_IMAGES + ' hình ảnh (từ 1 đến 3 ảnh).'
            );
            return;
        }

        for (var k = 0; k < validIncoming.length; k++) {
            selectedImageFiles.push(validIncoming[k]);
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

        if (selectedImageFiles.length < MIN_IMAGES || selectedImageFiles.length > MAX_IMAGES) {
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
                for (var idx = 0; idx < tagCheckboxes.length; idx++) {
                    var box = tagCheckboxes[idx];
                    var name = (box.getAttribute('data-tag-name') || '').trim().toLowerCase();
                    if (name === keyword) {
                        if (!box.checked) {
                            box.checked = true;
                            handleTagCheckboxChange({ target: box });
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

    // Đăng ký sự kiện đổi Tỉnh/Thành để nạp lại Phường/Xã.
    if (provinceSelect) {
        provinceSelect.addEventListener('change', function () {
            if (provinceSelect.value.trim() !== '') {
                setFieldError('place-province-error', '');
            }
            loadWardsForProvince(provinceSelect.value, '');
        });

        // Nếu mở form đã có sẵn mã tỉnh và danh sách phường đang trống thì nạp phường tương ứng.
        if (provinceSelect.value.trim() !== '' && wardSelect && wardSelect.options.length <= 1) {
            var initialWardCode = wardSelect.getAttribute('data-initial-ward') || '';
            loadWardsForProvince(provinceSelect.value, initialWardCode);
        }
    }

    // Đăng ký sự kiện cho tọa độ và nút tìm vị trí (không chặn submit khi Nominatim lỗi).
    if (latitudeInput) {
        latitudeInput.addEventListener('input', validateCoordinates);
    }

    if (longitudeInput) {
        longitudeInput.addEventListener('input', validateCoordinates);
    }

    if (clearCoordsButton) {
        clearCoordsButton.addEventListener('click', function () {
            if (latitudeInput) {
                latitudeInput.value = '';
            }
            if (longitudeInput) {
                longitudeInput.value = '';
            }
            validateCoordinates();
        });
    }

    if (geocodeButton) {
        geocodeButton.addEventListener('click', function () {
            setFieldError(
                'place-coords-error',
                'Chức năng tìm vị trí tự động hiện chưa hỗ trợ. Bạn có thể để trống tọa độ hoặc nhập cặp vĩ độ và kinh độ hợp lệ.'
            );
        });
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
        }
    });

    updateCharacterCounters();
    renderSelectedTags();
    validateCoordinates();
    renderImagePreviews();
})();
