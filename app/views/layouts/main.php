<?php

/** @var array<string, mixed> $viewData Dữ liệu được truyền từ View::render(). */
/** @var string $pageContent Nội dung HTML được View::render() tạo từ View con. */
$pageTitle = 'Hot Spot';

if (isset($viewData['title'])) {
    $pageTitle = $viewData['title'];
}

$homePageUrl = createRouteUrl('home');

$loginPageUrl = createRouteUrl('login');
$profilePageUrl = createRouteUrl('profile');

$userIsLoggedIn = isset($_SESSION['user_id']);
$currentUserName = 'Tài khoản';

// Profile đã nhận thông tin thật từ Controller.
if (isset($viewData['userInformation']['full_name'])) {
    $currentUserName = $viewData['userInformation']['full_name'];
}

$mainClass = 'container py-4 flex-grow-1';

// Profile tự bố trí container để banner có thể rộng toàn trang.
if (isset($viewData['isProfilePage'])) {
    if ($viewData['isProfilePage'] === true) {
        $mainClass = 'flex-grow-1';
    }
}

// Trang đăng nhập dùng nền riêng và tự bố trí container.
if (isset($viewData['isLoginPage'])) {
    if ($viewData['isLoginPage'] === true) {
        $mainClass = 'login-page flex-grow-1';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo escapeHtml($pageTitle); ?> | Hot Spot</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/app.css?v=3">
    <link rel="stylesheet" href="assets/css/profile.css?v=1">
    <link rel="stylesheet" href="assets/css/auth.css?v=1">
</head>

<body class="d-flex flex-column min-vh-100">
    <header class="site-header">
        <div class="container">
            <div class="row align-items-center gy-3">

                <div class="col-6 col-lg-3">
                    <a href="<?php echo escapeHtml($homePageUrl); ?>">
                        <img
                            src="assets/images/logo.svg"
                            alt="Hot Spot"
                            class="site-logo"
                            width="300"
                            height="72">
                    </a>
                </div>

                <div class="col-12 col-lg-5 order-3 order-lg-2">
                    <nav class="site-navigation" aria-label="Điều hướng chính">
                        <a href="<?php echo escapeHtml($homePageUrl); ?>">
                            Khám phá
                        </a>

                        <span
                            class="navigation-unavailable"
                            title="Chức năng đang được xây dựng">
                            Đăng địa điểm
                        </span>

                        <span
                            class="navigation-unavailable"
                            title="Chức năng đang được xây dựng">
                            ♡ Yêu thích
                        </span>
                    </nav>
                </div>

                <div class="col-6 col-lg-4 order-2 order-lg-3">
                    <div class="header-account">

                        <?php if ($userIsLoggedIn) { ?>
                            <a
                                href="<?php echo escapeHtml($profilePageUrl); ?>"
                                class="header-profile-link">
                                <img
                                    src="assets/images/default-avatar.svg"
                                    alt="Ảnh đại diện mặc định"
                                    class="header-avatar"
                                    width="36"
                                    height="36">

                                <span class="header-user-name">
                                    <?php echo escapeHtml($currentUserName); ?>
                                </span>
                            </a>
                        <?php } else { ?>
                            <a
                                href="<?php echo escapeHtml($loginPageUrl); ?>"
                                class="btn button-brand">
                                Đăng nhập
                            </a>
                        <?php } ?>

                    </div>
                </div>

            </div>
        </div>
    </header>
    <main class="<?php echo escapeHtml($mainClass); ?>">
        <?php echo $pageContent; ?>
    </main>
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">

                <div class="col-12 col-md-6 col-lg-3">
                    <a href="<?php echo escapeHtml($homePageUrl); ?>">
                        <img
                            src="assets/images/logo.svg"
                            alt="Hot Spot"
                            class="footer-logo"
                            width="300"
                            height="72">
                    </a>

                    <p class="footer-description">
                        Cộng đồng chia sẻ và khám phá những địa điểm thú vị
                        trên khắp Việt Nam.
                    </p>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <h2 class="footer-heading">Khám phá</h2>

                    <ul class="footer-list">
                        <li>
                            <a href="<?php echo escapeHtml($homePageUrl); ?>">
                                Địa điểm
                            </a>
                        </li>
                        <li>Danh mục</li>
                        <li>Tỉnh thành</li>
                        <li>Bài viết du lịch</li>
                    </ul>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <h2 class="footer-heading">Hỗ trợ</h2>

                    <ul class="footer-list">
                        <li>Hướng dẫn sử dụng</li>
                        <li>Liên hệ</li>
                        <li>Báo cáo vi phạm</li>
                        <li>Câu hỏi thường gặp</li>
                    </ul>
                </div>

                <div class="col-6 col-md-6 col-lg-2">
                    <h2 class="footer-heading">Về Hot Spot</h2>

                    <ul class="footer-list">
                        <li>Giới thiệu</li>
                        <li>Điều khoản sử dụng</li>
                        <li>Chính sách bảo mật</li>
                    </ul>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <h2 class="footer-heading">Nhận tin tức mới nhất</h2>

                    <p class="footer-description">
                        Cập nhật địa điểm mới và những gợi ý khám phá Việt Nam.
                    </p>

                    <!-- Chỉ dựng giao diện, chưa có chức năng nhận bản tin. -->
                    <div class="input-group">
                        <input
                            type="email"
                            class="form-control"
                            placeholder="Nhập email của bạn"
                            aria-label="Email nhận bản tin"
                            disabled>

                        <button
                            type="button"
                            class="btn button-brand"
                            disabled>
                            Đăng ký
                        </button>
                    </div>

                    <small class="footer-note">
                        Chức năng đang được xây dựng.
                    </small>
                </div>

            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <p class="footer-copyright">
                        © <?php echo date('Y'); ?> Hot Spot —
                        Đồ án môn Phát triển Web.
                    </p>
                </div>
            </div>
        </div>
    </footer>
    <script src="assets/vendor/bootstrap.bundle.min.js"></script>
</body>

</html>