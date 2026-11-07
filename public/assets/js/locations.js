(function () {
    'use strict';

    var WARD_REQUEST_TIMEOUT_MS = 10000;

    function bindWardSelect(provinceSelect, wardSelect, wardHint, apiUrl, initialWardCode) {
        if (!provinceSelect || !wardSelect) {
            return;
        }

        var wardState = {
            provinceSelect: provinceSelect,
            wardSelect: wardSelect,
            wardHint: wardHint,
            apiUrl: apiUrl,
            requestController: null,
            requestSequence: 0
        };

        provinceSelect.addEventListener('change', function () {
            loadWards(wardState, provinceSelect.value, '');
        });

        loadWards(wardState, provinceSelect.value, initialWardCode || '');
    }

    function loadWards(wardState, provinceCode, selectedWardCode) {
        if (wardState.requestController) {
            wardState.requestController.abort();
            wardState.requestController = null;
        }

        wardState.requestSequence += 1;
        var currentSequence = wardState.requestSequence;

        resetWardSelect(wardState, 'Chọn tỉnh/thành trước (không bắt buộc)');

        if (wardState.wardHint) {
            wardState.wardHint.textContent = 'Phường/xã là tùy chọn. Bạn có thể để trống nếu chưa tải được danh sách.';
        }

        if (!provinceCode || provinceCode.trim() === '') {
            return;
        }

        if (!wardState.apiUrl) {
            resetWardSelect(wardState, 'Chưa tải được phường/xã (không bắt buộc)');
            return;
        }

        resetWardSelect(wardState, 'Đang tải phường/xã...');
        wardState.wardSelect.setAttribute('aria-busy', 'true');

        if (wardState.wardHint) {
            wardState.wardHint.textContent = 'Đang tải danh sách phường/xã...';
        }

        var requestUrl;

        try {
            requestUrl = new URL(wardState.apiUrl, window.location.href);
            requestUrl.searchParams.set('province_code', provinceCode);
        } catch (error) {
            showWardLoadError(wardState);
            return;
        }

        var fetchOptions = {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        };

        var requestController = null;

        if (typeof AbortController !== 'undefined') {
            requestController = new AbortController();
            wardState.requestController = requestController;
            fetchOptions.signal = requestController.signal;
        }

        var requestTimedOut = false;
        var timeoutId = null;
        var timeoutPromise = new Promise(function (resolve, reject) {
            timeoutId = window.setTimeout(function () {
                requestTimedOut = true;

                var timeoutError = new Error('Yêu cầu tải phường/xã đã hết thời gian chờ.');
                timeoutError.name = 'TimeoutError';
                reject(timeoutError);

                if (requestController) {
                    requestController.abort();
                }
            }, WARD_REQUEST_TIMEOUT_MS);
        });

        var requestPromise = Promise.resolve()
            .then(function () {
                return fetch(requestUrl.toString(), fetchOptions);
            })
            .then(function (response) {
                return response.json().then(function (payload) {
                    if (response.ok === false || !payload || payload.success !== true) {
                        var errorMessage = 'Chưa thể tải danh sách phường/xã.';
                        if (payload && typeof payload.message === 'string') {
                            errorMessage = payload.message;
                        }
                        throw new Error(errorMessage);
                    }

                    return payload;
                });
            });

        Promise.race([requestPromise, timeoutPromise])
            .then(function (payload) {
                window.clearTimeout(timeoutId);

                if (currentSequence !== wardState.requestSequence) {
                    return;
                }

                renderWards(wardState, payload.data.wards, provinceCode, selectedWardCode);
                wardState.requestController = null;
            })
            .catch(function (error) {
                window.clearTimeout(timeoutId);

                if (currentSequence !== wardState.requestSequence) {
                    return;
                }

                if (error && error.name === 'AbortError' && requestTimedOut === false) {
                    return;
                }

                showWardLoadError(wardState);
            });
    }

    function showWardLoadError(wardState) {
        wardState.requestController = null;
        resetWardSelect(wardState, 'Chưa tải được phường/xã (không bắt buộc)');

        if (wardState.wardHint) {
            wardState.wardHint.textContent = 'Chưa tải được danh sách phường/xã. Bạn vẫn có thể tiếp tục và để trống trường này.';
        }
    }

    function resetWardSelect(wardState, placeholderText) {
        wardState.wardSelect.innerHTML = '';

        var defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = placeholderText;
        wardState.wardSelect.appendChild(defaultOption);
        wardState.wardSelect.value = '';
        wardState.wardSelect.disabled = true;
        wardState.wardSelect.setAttribute('aria-busy', 'false');
    }

    function renderWards(wardState, wardList, provinceCode, selectedWardCode) {
        if (!Array.isArray(wardList)) {
            throw new Error('Dữ liệu phường/xã không hợp lệ.');
        }

        resetWardSelect(wardState, 'Chọn phường/xã');

        for (var wardIndex = 0; wardIndex < wardList.length; wardIndex++) {
            var wardItem = wardList[wardIndex];

            if (!wardItem || typeof wardItem !== 'object') {
                continue;
            }

            if (typeof wardItem.ward_code !== 'string') {
                continue;
            }

            if (wardItem.province_code !== provinceCode) {
                continue;
            }

            var wardCode = wardItem.ward_code.trim();
            var wardName = String(wardItem.ward_name || '').trim();

            if (wardCode === '' || wardName === '') {
                continue;
            }

            var wardOption = document.createElement('option');
            wardOption.value = wardCode;
            wardOption.textContent = wardName;
            wardOption.selected = selectedWardCode !== '' && selectedWardCode === wardCode;
            wardState.wardSelect.appendChild(wardOption);
        }

        wardState.wardSelect.disabled = wardState.wardSelect.options.length <= 1;

        if (wardState.wardSelect.disabled) {
            resetWardSelect(wardState, 'Không có phường/xã để chọn (không bắt buộc)');
            if (wardState.wardHint) {
                wardState.wardHint.textContent = 'Tỉnh/thành này chưa có danh sách phường/xã đang hoạt động.';
            }
            return;
        }

        if (wardState.wardHint) {
            wardState.wardHint.textContent = 'Bạn có thể chọn phường/xã hoặc để trống. Danh sách sẽ làm mới khi đổi tỉnh/thành.';
        }
    }

    window.HotSpotLocations = {
        bindWardSelect: bindWardSelect
    };
})();
