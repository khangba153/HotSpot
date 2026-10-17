// Gắn sự kiện cho các form yêu thích trên trang.
function initializeFavoriteForms() {
    const favoriteForms = document.querySelectorAll('.favorite-form');

    favoriteForms.forEach(registerFavoriteForm);
}

// Mỗi form dùng chung một hàm xử lý submit.
function registerFavoriteForm(form) {
    form.addEventListener('submit', submitFavorite);
}

// Chỉ đổi trạng thái nút khi server xác nhận cập nhật thành công.
async function submitFavorite(event) {
    event.preventDefault();

    const form = event.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    const desiredStateInput = form.querySelector(
        'input[name="desired_state"]'
    );
    const messageElement = form.querySelector('.favorite-message');

    if (
        button === null
        || desiredStateInput === null
        || messageElement === null
    ) {
        return;
    }

    if (button.disabled) {
        return;
    }

    let buttonText = button.textContent;

    button.disabled = true;
    button.textContent = 'Đang lưu...';

    messageElement.textContent = '';
    messageElement.classList.remove('text-success', 'text-danger');

    try {
        const requestData = new FormData(form);

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                Accept: 'application/json'
            },
            body: requestData
        });

        const result = await response.json();

        if (result === null || typeof result !== 'object') {
            throw new Error('Server trả về dữ liệu không hợp lệ.');
        }

        if (response.ok === false || result.success !== true) {
            let errorMessage = 'Chưa thể cập nhật yêu thích.';

            if (typeof result.message === 'string') {
                errorMessage = result.message;
            }

            throw new Error(errorMessage);
        }

        if (
            result.data === null
            || typeof result.data !== 'object'
            || typeof result.data.is_favorited !== 'boolean'
        ) {
            throw new Error('Server trả về trạng thái không hợp lệ.');
        }

        const isFavorited = result.data.is_favorited;

        button.classList.remove('btn-danger', 'btn-outline-danger');

        if (isFavorited) {
            buttonText = '♥ Đã lưu địa điểm';
            button.classList.add('btn-danger');
            button.setAttribute('aria-pressed', 'true');
            desiredStateInput.value = '0';

            messageElement.textContent = 'Đã lưu địa điểm.';
        } else {
            buttonText = '♡ Lưu địa điểm';
            button.classList.add('btn-outline-danger');
            button.setAttribute('aria-pressed', 'false');
            desiredStateInput.value = '1';

            messageElement.textContent = 'Đã bỏ yêu thích.';
        }

        messageElement.classList.add('text-success');
    } catch (error) {
        messageElement.textContent =
            'Không thể cập nhật. Vui lòng kiểm tra kết nối và thử lại.';

        if (error instanceof Error && error.name === 'Error') {
            messageElement.textContent = error.message;
        }

        messageElement.classList.add('text-danger');
    } finally {
        button.disabled = false;
        button.textContent = buttonText;
    }
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeFavoriteForms
    );
} else {
    initializeFavoriteForms();
}