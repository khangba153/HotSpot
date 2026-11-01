<?php
/** @var array<string, mixed> $viewData Dữ liệu từ AuthController. */

$errorMessage = $viewData['errorMessage'];
$fullName = $viewData['fullName'];
$email = $viewData['email'];
$csrfToken = $viewData['csrfToken'];
$registerUrl = createRouteUrl('register');
$loginUrl = createRouteUrl('login');
?>

<div class="container">
    <div class="auth-card auth-register-card">
        <div class="row g-0">
            <div class="col-12 col-lg-6">
                <section class="auth-introduction">
                    <p class="auth-slogan">Việt Nam ơi!</p>
                    <h2>Còn bao điều<br><span>đẹp</span> để khám phá</h2>
                    <p class="auth-introduction-text">
                        Tham gia cộng đồng Hot Spot để lưu lại hành trình,
                        chia sẻ trải nghiệm và khám phá những địa điểm
                        tuyệt vời trên khắp Việt Nam.
                    </p>
                    <ul class="auth-benefits">
                        <li>Lưu lại những địa điểm yêu thích</li>
                        <li>Chia sẻ đánh giá và trải nghiệm của bạn</li>
                        <li>Kết nối cộng đồng yêu du lịch Việt Nam</li>
                    </ul>
                </section>
            </div>

            <div class="col-12 col-lg-6">
                <section class="auth-form-panel auth-register-panel" aria-labelledby="register-title">
                    <div class="auth-register-heading">
                        <h1 id="register-title">Tạo tài khoản <span>Hot Spot</span></h1>
                        <p>Cùng khám phá những điểm đến mới và lưu giữ trải nghiệm theo cách của bạn.</p>
                    </div>

                    <?php if ($errorMessage !== '') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo escapeHtml($errorMessage); ?>
                        </div>
                    <?php } ?>

                    <form method="post" action="<?php echo escapeHtml($registerUrl); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo escapeHtml($csrfToken); ?>">

                        <div class="mb-3">
                            <label for="register-full-name" class="form-label">Họ và tên</label>
                            <input
                                type="text"
                                id="register-full-name"
                                name="full_name"
                                class="form-control"
                                value="<?php echo escapeHtml($fullName); ?>"
                                placeholder="Nhập họ và tên của bạn"
                                autocomplete="name"
                                maxlength="120"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="register-email" class="form-label">Email</label>
                            <input
                                type="email"
                                id="register-email"
                                name="email"
                                class="form-control"
                                value="<?php echo escapeHtml($email); ?>"
                                placeholder="Nhập địa chỉ email"
                                autocomplete="username"
                                maxlength="254"
                                required>
                        </div>

                        <div class="mb-3">
                            <label for="register-password" class="form-label">Mật khẩu</label>
                            <input
                                type="password"
                                id="register-password"
                                name="password"
                                class="form-control"
                                placeholder="Tạo mật khẩu của bạn"
                                autocomplete="new-password"
                                minlength="8"
                                maxlength="72"
                                aria-describedby="register-password-help"
                                required>
                            <p id="register-password-help" class="auth-register-help">
                                Ít nhất 8 ký tự, gồm chữ hoa, chữ thường và số.
                            </p>
                        </div>

                        <div class="mb-4">
                            <label for="register-password-confirmation" class="form-label">Xác nhận mật khẩu</label>
                            <input
                                type="password"
                                id="register-password-confirmation"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Nhập lại mật khẩu"
                                autocomplete="new-password"
                                minlength="8"
                                maxlength="72"
                                required>
                        </div>

                        <button type="submit" class="btn auth-submit w-100">
                            Tạo tài khoản
                        </button>
                    </form>

                    <div class="auth-registration mt-4">
                        Đã có tài khoản?
                        <a href="<?php echo escapeHtml($loginUrl); ?>">Đăng nhập</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
