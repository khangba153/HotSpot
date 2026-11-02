'use strict';

(function setupGoogleLogin() {
    const loginForm = document.getElementById('google-login-form');
    const buttonContainer = document.getElementById('google-login-button');
    const statusMessage = document.getElementById('google-login-status');
    const credentialInput = document.getElementById('google-credential');
    let isSubmitting = false;

    if (!loginForm || !buttonContainer || !statusMessage || !credentialInput) {
        return;
    }

    // Gửi token về PHP; chỉ Controller mới quyết định tài khoản đăng nhập.
    function handleGoogleCredential(response) {
        if (isSubmitting) {
            return;
        }

        if (!response || typeof response.credential !== 'string' || response.credential === '') {
            statusMessage.textContent = 'Không nhận được token Google. Vui lòng thử lại.';
            return;
        }

        isSubmitting = true;
        credentialInput.value = response.credential;
        statusMessage.textContent = 'Đang xác minh tài khoản Google…';
        loginForm.submit();
    }

    // Khởi tạo nút chính thức sau khi thư viện Google tải xong.
    function initializeGoogleLogin() {
        if (!window.google || !window.google.accounts || !window.google.accounts.id) {
            showGoogleLoadError();
            return;
        }

        let buttonWidth = Math.floor(buttonContainer.clientWidth);

        if (buttonWidth > 400) {
            buttonWidth = 400;
        }

        window.google.accounts.id.initialize({
            client_id: loginForm.dataset.clientId,
            nonce: loginForm.dataset.nonce,
            callback: handleGoogleCredential,
            ux_mode: 'popup',
            auto_select: false
        });

        window.google.accounts.id.renderButton(buttonContainer, {
            type: 'standard',
            theme: 'outline',
            size: 'large',
            text: 'signin_with',
            shape: 'rectangular',
            locale: 'vi',
            width: buttonWidth
        });

        statusMessage.textContent = '';
    }

    // Giữ đăng nhập bằng mật khẩu hoạt động khi không tải được Google.
    function showGoogleLoadError() {
        statusMessage.textContent = 'Không tải được Google. Bạn có thể đăng nhập bằng email và mật khẩu.';
    }

    if (!loginForm.dataset.clientId || !loginForm.dataset.nonce) {
        statusMessage.textContent = 'Đăng nhập Google chưa được cấu hình đầy đủ.';
        return;
    }

    const googleScript = document.createElement('script');
    googleScript.src = 'https://accounts.google.com/gsi/client';
    googleScript.async = true;
    googleScript.addEventListener('load', initializeGoogleLogin);
    googleScript.addEventListener('error', showGoogleLoadError);
    document.head.appendChild(googleScript);
})();
