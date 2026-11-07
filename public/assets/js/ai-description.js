'use strict';

const placeDescriptionState = {
    section: null,
    form: null,
    titleInput: null,
    descriptionInput: null,
    generateButton: null,
    applyButton: null,
    feedback: null,
    result: null,
    draftText: null
};

// Hiển thị thông báo bằng văn bản thuần.
function showPlaceDescriptionFeedback(message, isError) {
    placeDescriptionState.feedback.textContent = message;
    placeDescriptionState.feedback.hidden = false;
    placeDescriptionState.feedback.classList.toggle('is-error', isError);
}

// Bỏ bản nháp đang hiển thị khi dữ liệu nguồn thay đổi.
function resetPlaceDescriptionDraft() {
    placeDescriptionState.result.hidden = true;
    placeDescriptionState.draftText.textContent = '';
    placeDescriptionState.applyButton.disabled = true;
    placeDescriptionState.feedback.hidden = true;
}

// Chỉ gửi tên địa điểm, các ý thực tế và CSRF tới API.
async function generatePlaceDescriptionDraft() {
    if (placeDescriptionState.generateButton.disabled) {
        return;
    }

    const title = placeDescriptionState.titleInput.value.trim();
    const facts = placeDescriptionState.descriptionInput.value.trim();

    if (title === '' || facts === '') {
        showPlaceDescriptionFeedback('Vui lòng nhập tên địa điểm và vài ý trong ô Mô tả.', true);
        return;
    }

    if (Array.from(title).length > 100 || Array.from(facts).length > 1000) {
        showPlaceDescriptionFeedback('Tên tối đa 100 ký tự, thông tin tối đa 1.000 ký tự.', true);
        return;
    }

    const tokenInput = placeDescriptionState.form.querySelector('input[name="csrf_token"]');

    if (tokenInput === null || tokenInput.value === '') {
        showPlaceDescriptionFeedback('Phiên làm việc không hợp lệ. Vui lòng tải lại trang.', true);
        return;
    }

    const requestData = new FormData();
    requestData.append('csrf_token', tokenInput.value);
    requestData.append('title', title);
    requestData.append('facts', facts);

    resetPlaceDescriptionDraft();
    placeDescriptionState.generateButton.disabled = true;
    placeDescriptionState.generateButton.textContent = 'Đang tạo bản nháp...';
    placeDescriptionState.section.setAttribute('aria-busy', 'true');
    showPlaceDescriptionFeedback('Đang chuẩn bị bản nháp, vui lòng chờ.', false);

    try {
        const response = await fetch(placeDescriptionState.section.dataset.requestUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
            body: requestData
        });
        const responseData = await response.json();

        // Không dùng kết quả của dữ liệu đã thay đổi trong lúc chờ.
        if (
            title !== placeDescriptionState.titleInput.value.trim()
            || facts !== placeDescriptionState.descriptionInput.value.trim()
        ) {
            showPlaceDescriptionFeedback('Thông tin đã thay đổi. Hãy tạo bản nháp theo nội dung mới.', false);
            return;
        }

        if (!response.ok || responseData.success !== true) {
            let message = 'Chưa thể tạo bản nháp. Vui lòng thử lại sau.';

            if (typeof responseData.message === 'string' && responseData.message !== '') {
                message = responseData.message;
            }

            throw new Error(message);
        }

        if (
            !responseData.data
            || typeof responseData.data.description !== 'string'
            || responseData.data.description.trim() === ''
        ) {
            throw new Error('Chưa nhận được bản nháp hợp lệ.');
        }

        const description = responseData.data.description.trim();

        if (Array.from(description).length > 1000) {
            throw new Error('Bản nháp vượt quá giới hạn của ô Mô tả. Vui lòng thử với ít thông tin hơn.');
        }

        placeDescriptionState.draftText.textContent = description;
        placeDescriptionState.result.hidden = false;
        placeDescriptionState.applyButton.disabled = false;
        showPlaceDescriptionFeedback('Đã tạo bản nháp. Bạn hãy kiểm tra trước khi sử dụng.', false);
    } catch (error) {
        let message = 'Không thể kết nối. Vui lòng thử lại sau.';

        if (error instanceof Error && error.name !== 'SyntaxError') {
            message = error.message;
        }

        showPlaceDescriptionFeedback(message, true);
    } finally {
        placeDescriptionState.generateButton.disabled = false;
        placeDescriptionState.generateButton.textContent = 'Tạo bản nháp';
        placeDescriptionState.section.removeAttribute('aria-busy');
    }
}

// Người đăng chủ động chọn đưa bản nháp vào ô Mô tả.
function applyPlaceDescriptionDraft() {
    if (placeDescriptionState.result.hidden || placeDescriptionState.applyButton.disabled) {
        return;
    }

    placeDescriptionState.descriptionInput.value = placeDescriptionState.draftText.textContent;
    placeDescriptionState.descriptionInput.dispatchEvent(new Event('input', { bubbles: true }));
    placeDescriptionState.descriptionInput.focus();
    showPlaceDescriptionFeedback('Đã đưa bản nháp vào ô Mô tả. Bạn có thể chỉnh sửa tiếp.', false);
}

// Gắn thao tác AI vào form hiện có, không tạo form lồng nhau.
function initializePlaceDescriptionDraft() {
    placeDescriptionState.section = document.querySelector('[data-ai-description]');

    if (placeDescriptionState.section === null) {
        return;
    }

    placeDescriptionState.form = placeDescriptionState.section.closest('form');
    placeDescriptionState.titleInput = document.getElementById('place-title');
    placeDescriptionState.descriptionInput = document.getElementById('place-description');
    placeDescriptionState.generateButton = placeDescriptionState.section.querySelector('[data-ai-description-generate]');
    placeDescriptionState.applyButton = placeDescriptionState.section.querySelector('[data-ai-description-apply]');
    placeDescriptionState.feedback = placeDescriptionState.section.querySelector('[data-ai-description-feedback]');
    placeDescriptionState.result = placeDescriptionState.section.querySelector('[data-ai-description-result]');
    placeDescriptionState.draftText = placeDescriptionState.section.querySelector('[data-ai-description-text]');

    placeDescriptionState.generateButton.addEventListener('click', generatePlaceDescriptionDraft);
    placeDescriptionState.applyButton.addEventListener('click', applyPlaceDescriptionDraft);
    placeDescriptionState.titleInput.addEventListener('input', resetPlaceDescriptionDraft);
    placeDescriptionState.descriptionInput.addEventListener('input', resetPlaceDescriptionDraft);
}

initializePlaceDescriptionDraft();
