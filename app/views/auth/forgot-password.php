<?php
/** @var array<string, mixed> $viewData Dữ liệu từ AuthController. */

$errorMessage = $viewData['errorMessage'];
$successMessage = $viewData['successMessage'];
$email = $viewData['email'];
$csrfToken = $viewData['csrfToken'];
$forgotPasswordUrl = createRouteUrl('forgot-password');
$loginUrl = createRouteUrl('login');
$canRequestReset = false;

if (isset($viewData['canRequestReset']) && $viewData['canRequestReset'] === true) {
    $canRequestReset = true;
}
?>

<div class="container">
    <div class="auth-card">
        <div class="row g-0">
            <div class="col-12 col-lg-6">
                <section class="auth-introduction">
                    <p class="auth-slogan">Việt Nam ơi!</p>
                    <h2>Hành trình mới<br><span>đang chờ</span> bạn</h2>
                    <p class="auth-introduction-text">
                        Lấy lại quyền truy cập tài khoản để tiếp tục lưu
                        những địa điểm yêu thích và chia sẻ trải nghiệm của bạn.
                    </p>
                </section>
            </div>

            <div class="col-12 col-lg-6">
                <section class="auth-form-panel auth-recovery-panel" aria-labelledby="forgot-password-title">
                    <div class="auth-welcome">
                        <img src="assets/images/logo.svg" alt="Hot Spot"
                            class="auth-logo" width="300" height="72">
                        <h1 id="forgot-password-title">Quên mật khẩu?</h1>
                        <p>
                            Nhập email đã đăng ký Hot Spot.
                            Chúng tôi sẽ gửi liên kết để bạn đặt mật khẩu mới.
                        </p>
                    </div>

                    <?php if ($errorMessage !== '') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo escapeHtml($errorMessage); ?>
                        </div>
                    <?php } ?>

                    <?php if ($successMessage !== '') { ?>
                        <div class="alert alert-success" role="status">
                            <?php echo escapeHtml($successMessage); ?>
                        </div>
                    <?php } ?>

                    <form method="post" action="<?php echo escapeHtml($forgotPasswordUrl); ?>">
                        <input type="hidden" name="csrf_token"
                            value="<?php echo escapeHtml($csrfToken); ?>">

                        <div class="mb-4">
                            <label for="recovery-email" class="form-label">Email tài khoản</label>
                            <input type="email" id="recovery-email" name="email"
                                class="form-control"
                                value="<?php echo escapeHtml($email); ?>"
                                placeholder="Nhập email đã đăng ký"
                                autocomplete="email" maxlength="254"
                                aria-describedby="recovery-email-help" required>
                            <p id="recovery-email-help" class="auth-register-help">
                                Liên kết có hiệu lực trong 15 phút và chỉ sử dụng một lần.
                            </p>
                        </div>

                        <button type="submit" class="btn button-brand auth-submit w-100"
                            <?php if ($canRequestReset === false) { ?>disabled<?php } ?>>
                            Gửi liên kết đặt lại mật khẩu
                        </button>
                    </form>

                    <div class="auth-registration auth-recovery-back">
                        Đã nhớ mật khẩu?
                        <a href="<?php echo escapeHtml($loginUrl); ?>">Đăng nhập</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
