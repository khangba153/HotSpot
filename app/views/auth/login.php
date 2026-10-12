<?php
/** @var array<string, mixed> $viewData Dữ liệu từ AuthController. */

// Nhận dữ liệu form và thông báo lỗi.
$errorMessage = $viewData['errorMessage'];
$email = $viewData['email'];
$csrfToken = $viewData['csrfToken'];
$loginUrl = createRouteUrl('login');
?>

<div class="container">
    <div class="auth-card">
        <div class="row g-0">

            <div class="col-12 col-lg-6">
                <section class="auth-introduction">
                    <p class="auth-slogan">Việt Nam ơi!</p>

                    <h2>
                        Còn bao điều<br>
                        <span>đẹp</span> để khám phá
                    </h2>

                    <p class="auth-introduction-text">
                        Tham gia cộng đồng Hot Spot để lưu lại hành trình,
                        chia sẻ trải nghiệm và khám phá những địa điểm
                        tuyệt vời trên khắp Việt Nam.
                    </p>

                    <ul class="auth-benefits">
                        <li>
                            <span aria-hidden="true">♥</span>
                            Kết nối cộng đồng yêu du lịch
                        </li>
                        <li>
                            <span aria-hidden="true">★</span>
                            Lưu lại địa điểm yêu thích
                        </li>
                        <li>
                            <span aria-hidden="true">▣</span>
                            Chia sẻ trải nghiệm và hình ảnh đẹp
                        </li>
                    </ul>
                </section>
            </div>

            <div class="col-12 col-lg-6">
                <section class="auth-form-panel">
                    <div class="auth-welcome">
                        <img
                            src="assets/images/logo.svg"
                            alt="Hot Spot"
                            class="auth-logo"
                            width="300"
                            height="72"
                        >

                        <h1>Chào mừng bạn trở lại!</h1>

                        <p>
                            Đăng nhập để tiếp tục khám phá những địa điểm
                            tuyệt vời và kết nối cộng đồng yêu du lịch Việt Nam.
                        </p>
                    </div>

                    <?php if ($errorMessage !== '') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo escapeHtml($errorMessage); ?>
                        </div>
                    <?php } ?>

                    <form
                        method="post"
                        action="<?php echo escapeHtml($loginUrl); ?>"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo escapeHtml($csrfToken); ?>"
                        >

                        <div class="mb-4">
                            <label for="email" class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?php echo escapeHtml($email); ?>"
                                placeholder="Nhập email của bạn"
                                autocomplete="username"
                                maxlength="254"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Mật khẩu
                            </label>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Nhập mật khẩu"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <!-- Hai chức năng này hiện chỉ hiển thị giao diện. -->
                        <div class="auth-options">
                            <label class="auth-remember">
                                <input type="checkbox" disabled>
                                Ghi nhớ đăng nhập
                            </label>

                            <span title="Chưa hỗ trợ khôi phục mật khẩu">
                                Quên mật khẩu?
                            </span>
                        </div>

                        <button
                            type="submit"
                            class="btn button-brand auth-submit w-100"
                        >
                            Đăng nhập →
                        </button>
                    </form>

                    <div class="auth-divider">
                        <span>hoặc</span>
                    </div>

                    <div class="auth-registration">
                        Chưa có tài khoản?
                        <span>Đăng ký</span>
                        <small>Chức năng đăng ký chưa được triển khai.</small>
                    </div>
                </section>
            </div>

        </div>
    </div>
</div>