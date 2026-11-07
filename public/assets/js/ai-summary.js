'use strict';

// Gửi yêu cầu khi người dùng bấm nút, hiển thị kết quả bằng văn bản thuần.
async function submitAiReviewSummary(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const section = form.closest('[data-ai-summary]');
    const button = form.querySelector('button[type="submit"]');
    const feedback = section.querySelector('[data-ai-summary-feedback]');
    const result = section.querySelector('[data-ai-summary-result]');
    const summaryText = section.querySelector('[data-ai-summary-text]');

    if (button.disabled) {
        return;
    }

    const originalLabel = button.textContent;
    button.disabled = true;
    button.textContent = 'Đang tóm tắt...';
    form.setAttribute('aria-busy', 'true');
    feedback.hidden = false;
    feedback.textContent = 'Đang chuẩn bị bản tóm tắt, vui lòng chờ.';
    feedback.classList.remove('is-error');
    result.hidden = true;
    summaryText.textContent = '';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: { Accept: 'application/json' }
        });
        const responseData = await response.json();

        if (!response.ok || responseData.success !== true) {
            let errorMessage = 'Chưa thể tóm tắt. Vui lòng thử lại sau.';

            if (typeof responseData.message === 'string' && responseData.message !== '') {
                errorMessage = responseData.message;
            }

            throw new Error(errorMessage);
        }

        if (
            !responseData.data
            || typeof responseData.data.summary !== 'string'
            || responseData.data.summary.trim() === ''
        ) {
            throw new Error('Chưa nhận được bản tóm tắt hợp lệ.');
        }

        summaryText.textContent = responseData.data.summary;
        result.hidden = false;
        feedback.textContent = 'Đã tóm tắt các đánh giá công khai.';
    } catch (error) {
        feedback.textContent = 'Không thể kết nối. Vui lòng thử lại sau.';

        if (error instanceof Error && error.name !== 'SyntaxError') {
            feedback.textContent = error.message;
        }

        feedback.classList.add('is-error');
    } finally {
        button.disabled = false;
        button.textContent = originalLabel;
        form.removeAttribute('aria-busy');
    }
}

// Chỉ gắn xử lý khi trang có form tóm tắt.
function initializeAiReviewSummary() {
    const form = document.querySelector('[data-ai-summary-form]');

    if (form === null) {
        return;
    }

    form.addEventListener('submit', submitAiReviewSummary);
}

initializeAiReviewSummary();
