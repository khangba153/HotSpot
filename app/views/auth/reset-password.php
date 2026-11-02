<?php
/** @var array<string, mixed> $viewData Dữ liệu từ AuthController. */

$errorMessage = $viewData['errorMessage'];
$resetToken = $viewData['resetToken'];
$csrfToken = $viewData['csrfToken'];
$resetPasswordUrl = createRouteUrl('reset-password');
$forgotPasswordUrl = createRouteUrl('forgot-password');
$loginUrl = createRouteUrl('login');
$canResetPassword = false;

if (isset($viewData['canResetPassword']) && $viewData['canResetPassword'] === true) {
    $canResetPassword = true;
}
?>

<div class="container">
    <div class="auth-card">
        <div class="row g-0">
            <div class="col-12 col-lg-6">
                <section class="auth-introduction">
                    <p class="auth-slogan">Việt Nam ơi!</p>
                    <h2>Sẵn sàng cho<br><span>hành trình</span> tiếp theo</h2>
                    <p class="auth-introduction-text">
                        Đặt mật khẩu mới để quay lại những địa điểm yêu thích
                        và tiếp tục chia sẻ trải nghiệm cùng Hot Spot.
                    </p>
                </section>
            </div>

            <div class="col-12 col-lg-6">
                <section class="auth-form-panel auth-recovery-panel" aria-labelledby="reset-password-title">
                    <div class="auth-welcome">
                        <img src="assets/images/logo.svg" alt="Hot Spot"
                            class="auth-logo" width="300" height="72">
                        <h1 id="reset-password-title">Đặt mật khẩu mới</h1>
                        <p>Nhập mật khẩu mới hai lần để hoàn tất khôi phục tài khoản.</p>
                    </div>

                    <?php if ($errorMessage !== '') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo escapeHtml($errorMessage); ?>
                        </div>
                    <?php } ?>

                    <form method="post" action="<?php echo escapeHtml($resetPasswordUrl); ?>">
                        <input type="hidden" name="csrf_token"
                            value="<?php echo escapeHtml($csrfToken); ?>">
                        <input type="hidden" name="token"
                            value="<?php echo escapeHtml($resetToken); ?>">

                        <div class="mb-4">
                            <label for="reset-password" class="form-label">Mật khẩu mới</label>
                            <input type="password" id="reset-password" name="password"
                                class="form-control" placeholder="Nhập mật khẩu mới"
                                autocomplete="new-password" minlength="8" maxlength="72"
                                aria-describedby="reset-password-help" required>
                            <p id="reset-password-help" class="auth-register-help">
                                Ít nhất 8 ký tự, có chữ hoa, chữ thường và chữ số.
                            </p>
                        </div>

                        <div class="mb-4">
                            <label for="reset-password-confirmation" class="form-label">
                                Xác nhận mật khẩu mới
                            </label>
                            <input type="password" id="reset-password-confirmation"
                                name="password_confirmation" class="form-control"
                                placeholder="Nhập lại mật khẩu mới"
                                autocomplete="new-password" minlength="8" maxlength="72" required>
                        </div>

                        <button type="submit" class="btn button-brand auth-submit w-100"
                            <?php if ($canResetPassword === false) { ?>disabled<?php } ?>>
                            Lưu mật khẩu mới
                        </button>
                    </form>

                    <p class="auth-register-help mt-3 text-center">
                        Sau khi đổi mật khẩu, hãy đăng nhập lại vào tài khoản của bạn.
                    </p>
                    <div class="auth-registration auth-recovery-back">
                        <a href="<?php echo escapeHtml($loginUrl); ?>">Đăng nhập</a>
                        <span class="mx-2">·</span>
                        <a href="<?php echo escapeHtml($forgotPasswordUrl); ?>">Yêu cầu liên kết mới</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
