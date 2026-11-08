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
$createPlacePageUrl = createRouteUrl('places/create');

$favoritePageUrl = createRouteUrl('favorites');

$currentRoute = 'home';
if (isset($_GET['route']) && is_string($_GET['route'])) {
    $currentRoute = trim($_GET['route'], '/');
    if ($currentRoute === '') {
        $currentRoute = 'home';
    }
}
$homeLinkClass = 'hs-main-nav__link';
$createLinkClass = 'hs-main-nav__link';
$favoriteLinkClass = 'hs-main-nav__link';
if ($currentRoute === 'home' || $currentRoute === 'places/show') {
    $homeLinkClass .= ' hs-main-nav__link--active';
} elseif (in_array($currentRoute, ['places/create', 'places/edit', 'places/mine'], true)) {
    $createLinkClass .= ' hs-main-nav__link--active';
} elseif ($currentRoute === 'favorites') {
    $favoriteLinkClass .= ' hs-main-nav__link--active';
}
if (isset($_SESSION['user_id']) === false) {
    $favoritePageUrl = $loginPageUrl;
    $createPlacePageUrl = $loginPageUrl;
}

$userIsLoggedIn = isset($_SESSION['user_id']);
$userIsAdmin = isset($viewData['userInformation']['role_code'])
    && $viewData['userInformation']['role_code'] === 'ADMIN';
$currentUserName = 'Tài khoản';

if (
    isset($viewData['userInformation']['full_name'])
    && is_string($viewData['userInformation']['full_name'])
) {
    $currentUserName = $viewData['userInformation']['full_name'];
}

$isLoginPage = ($viewData['isLoginPage'] ?? false) === true;
$isProfilePage = ($viewData['isProfilePage'] ?? false) === true;
$isAdminPage = ($viewData['isAdminPage'] ?? false) === true;

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
    <link rel="stylesheet" href="assets/css/app.css?v=20261108-card-labels">
    <link rel="stylesheet" href="assets/css/profile.css?v=20261108">
    <link rel="stylesheet" href="assets/css/auth.css?v=20261108">
    <link rel="stylesheet" href="assets/css/places.css?v=20261108-account">
    <link rel="stylesheet" href="assets/css/reviews.css?v=1">
    <link rel="stylesheet" href="assets/css/admin.css?v=20261108-card-labels">
    <link rel="stylesheet" href="assets/css/favorites.css?v=20261108-account">
    <link rel="stylesheet" href="assets/css/ai.css?v=20261108-filters2">
    <link rel="stylesheet" href="assets/css/interface.css?v=20261108">
    <?php if ($isAdminPage) { ?><script src="assets/js/admin-pagination.js?v=20261108" defer></script><?php } ?>
</head>

<body class="hs-body d-flex flex-column min-vh-100<?php echo $isAdminPage ? ' admin-layout' : ''; ?>">
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

            <?php if ($isAdminPage) { ?>
                <?php require dirname(__DIR__) . '/admin/_navigation.php'; ?>
            <?php } else { ?>
            <nav class="hs-main-nav" aria-label="Điều hướng chính">
                <a
                    class="<?php echo escapeHtml($homeLinkClass); ?>"<?php if (str_contains($homeLinkClass, '--active')) { ?> aria-current="page"<?php } ?>
                    href="<?php echo escapeHtml($homePageUrl); ?>">
                    Khám phá
                </a>
                <a
                    class="<?php echo escapeHtml($createLinkClass); ?>"<?php if (str_contains($createLinkClass, '--active')) { ?> aria-current="page"<?php } ?>
                    href="<?php echo escapeHtml($createPlacePageUrl); ?>">
                    Đăng địa điểm
                </a>
                <a
                    class="<?php echo escapeHtml($favoriteLinkClass); ?>"<?php if (str_contains($favoriteLinkClass, '--active')) { ?> aria-current="page"<?php } ?>
                    href="<?php echo escapeHtml($favoritePageUrl); ?>">
                    Yêu thích
                </a>
                <?php if ($userIsAdmin) { ?>
                    <a class="hs-main-nav__link hs-main-nav__link--admin" href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">Quản trị</a>
                <?php } ?>
            </nav>
            <?php } ?>

            <div class="hs-header-actions">
                <?php if (!$isAdminPage) { ?><a class="hs-header-search" href="<?php echo escapeHtml($homePageUrl); ?>#home-search" aria-label="Tìm kiếm">Tìm kiếm</a><?php } ?>
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

    <?php if (!$isAdminPage) { ?><footer class="hs-site-footer">
        <div class="hs-container hs-footer-simple">
            <div><strong>Hot Spot</strong><p>Khám phá và chia sẻ những địa điểm bạn yêu thích.</p></div>
            <nav aria-label="Điều hướng chân trang">
                <a href="<?php echo escapeHtml($homePageUrl); ?>">Khám phá</a>
                <a href="<?php echo escapeHtml($createPlacePageUrl); ?>">Đăng địa điểm</a>
                <a href="<?php echo escapeHtml($favoritePageUrl); ?>">Yêu thích</a>
            </nav>
        </div>
        <div class="hs-container hs-footer__bottom">
            <span>© 2026 Hot Spot. Bản demo đồ án Phát triển Web.</span>
            <span>Khám phá Việt Nam, một địa điểm mỗi lần. <a href="assets/images/photo-credits.html" target="_blank" rel="noopener noreferrer">Nguồn ảnh</a></span>
        </div>
    </footer><?php } ?>
    <script src="assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="assets/js/select-control.js?v=20261108" defer></script>
</body>

</html>
