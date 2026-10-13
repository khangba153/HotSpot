<?php

/** @var array<string, mixed> $viewData */
/** @var string $pageContent */
$pageTitle = 'Hot Spot';

if (isset($viewData['title']) && is_string($viewData['title'])) {
    $pageTitle = $viewData['title'];
}

$homePageUrl = createRouteUrl('home');
$loginPageUrl = createRouteUrl('login');
$profilePageUrl = createRouteUrl('profile');

$userIsLoggedIn = isset($_SESSION['user_id']);
$currentUserName = 'Tài khoản';

if (
    isset($viewData['userInformation']['full_name'])
    && is_string($viewData['userInformation']['full_name'])
) {
    $currentUserName = $viewData['userInformation']['full_name'];
}

$isLoginPage = ($viewData['isLoginPage'] ?? false) === true;
$isProfilePage = ($viewData['isProfilePage'] ?? false) === true;

$mainClass = 'hs-site-main flex-grow-1';

if ($isLoginPage) {
    $mainClass .= ' login-page';
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#fffaf7">
    <title><?php echo escapeHtml($pageTitle); ?> | Hot Spot</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/app.css?v=4">
    <link rel="stylesheet" href="assets/css/profile.css?v=1">
    <link rel="stylesheet" href="assets/css/auth.css?v=1">
    <link rel="stylesheet" href="assets/css/places.css?v=1">
</head>

<body class="hs-body d-flex flex-column min-vh-100">
    <header class="hs-site-header">
        <div class="hs-header__inner">
            <a class="hs-brand" href="<?php echo escapeHtml($homePageUrl); ?>" aria-label="Hot Spot — Trang chủ">
                <span class="hs-brand__mark" aria-hidden="true">
                    <svg viewBox="0 0 28 34">
                        <path d="M14 1.5C6.9 1.5 1.2 7.2 1.2 14.3c0 9.4 12.8 18.2 12.8 18.2s12.8-8.8 12.8-18.2C26.8 7.2 21.1 1.5 14 1.5Z"></path>
                        <circle cx="14" cy="14" r="4.5"></circle>
                    </svg>
                </span>
                <span class="hs-brand__copy">
                    <strong>Hot Spot</strong>
                    <small>Khám phá Việt Nam theo cách của bạn</small>
                </span>
            </a>

            <nav class="hs-main-nav" aria-label="Điều hướng chính">
                <a class="hs-main-nav__link hs-main-nav__link--active" href="<?php echo escapeHtml($homePageUrl); ?>">Khám phá</a>
                <span class="hs-main-nav__link" aria-disabled="true">Đăng địa điểm</span>
                <span class="hs-main-nav__link hs-main-nav__link--heart" aria-disabled="true"><span aria-hidden="true">♡</span> Yêu thích</span>
            </nav>

            <div class="hs-header-actions">
                <a class="hs-header-icon" href="#home-search" aria-label="Tìm kiếm">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.8"></circle>
                        <path d="m16 16 4.4 4.4"></path>
                    </svg>
                </a>
                <span class="hs-header-icon hs-header-icon--bell" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                        <path d="M10 21h4"></path>
                    </svg>
                    <i></i>
                </span>
                <?php if ($userIsLoggedIn) { ?>
                    <a
                        class="hs-profile-link"
                        href="<?php echo escapeHtml($profilePageUrl); ?>">
                        <img
                            class="hs-profile-avatar"
                            src="assets/images/default-avatar.svg"
                            alt=""
                            width="28"
                            height="28">
                        <span class="hs-profile-name">
                            <?php echo escapeHtml($currentUserName); ?>
                        </span>
                    </a>
                <?php } else { ?>
                    <a
                        class="hs-login-button"
                        href="<?php echo escapeHtml($loginPageUrl); ?>">
                        Đăng nhập
                    </a>
                <?php } ?>
            </div>
        </div>
    </header>

    <main class="<?php echo escapeHtml($mainClass); ?>">
        <?php echo $pageContent; ?>
    </main>

    <footer class="hs-site-footer">
        <div class="hs-footer-hills" aria-hidden="true">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path d="M0 88 72 50l68 38 76-60 81 60 90-34 85 34 86-53 69 53 82-23 66 23 93-54 74 54 81-31 61 31 91-51 81 51 74-27 66 27v32H0Z"></path>
            </svg>
        </div>
        <div class="hs-container hs-footer__grid">
            <div class="hs-footer-brand">
                <a class="hs-brand hs-brand--footer" href="<?php echo escapeHtml($homePageUrl); ?>">
                    <span class="hs-brand__mark" aria-hidden="true">
                        <svg viewBox="0 0 28 34">
                            <path d="M14 1.5C6.9 1.5 1.2 7.2 1.2 14.3c0 9.4 12.8 18.2 12.8 18.2s12.8-8.8 12.8-18.2C26.8 7.2 21.1 1.5 14 1.5Z"></path>
                            <circle cx="14" cy="14" r="4.5"></circle>
                        </svg>
                    </span>
                    <span class="hs-brand__copy"><strong>Hot Spot</strong><small>Khám phá Việt Nam theo cách của bạn</small></span>
                </a>
                <p>Cộng đồng chia sẻ và khám phá những địa điểm thú vị tại Việt Nam.</p>
                <div class="hs-socials" aria-label="Mạng xã hội">
                    <span>f</span><span>◎</span><span>▶</span><span>♪</span>
                </div>
            </div>

            <div class="hs-footer-column">
                <strong>Khám phá</strong>
                <span>Địa điểm</span>
                <span>Danh mục</span>
                <span>Tỉnh thành</span>
                <span>Bài viết du lịch</span>
            </div>
            <div class="hs-footer-column">
                <strong>Hỗ trợ</strong>
                <span>Hướng dẫn sử dụng</span>
                <span>Liên hệ</span>
                <span>Báo cáo vi phạm</span>
                <span>Câu hỏi thường gặp</span>
            </div>
            <div class="hs-footer-column">
                <strong>Về Hot Spot</strong>
                <span>Giới thiệu</span>
                <span>Điều khoản sử dụng</span>
                <span>Chính sách bảo mật</span>
                <span>Tuyển dụng</span>
            </div>
            <div class="hs-footer-newsletter">
                <strong>Nhận tin tức mới nhất</strong>
                <p>Cập nhật địa điểm hay, kinh nghiệm du lịch và ưu đãi hấp dẫn.</p>
                <div class="hs-newsletter-shell"><span>Nhập email của bạn...</span><b>Đăng ký</b></div>
            </div>
        </div>
        <div class="hs-container hs-footer__bottom">
            <span>© 2026 Hot Spot. Bản demo đồ án Phát triển Web.</span>
            <span>Khám phá Việt Nam, một địa điểm mỗi lần.</span>
        </div>
    </footer>
    <script src="assets/vendor/bootstrap.bundle.min.js"></script>
</body>

</html>
