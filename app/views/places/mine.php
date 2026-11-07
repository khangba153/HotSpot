<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller. */

$userInformation = is_array($viewData['userInformation'] ?? null) ? $viewData['userInformation'] : [];
$myPlaces = is_array($viewData['myPlaces'] ?? null) ? $viewData['myPlaces'] : [];
$statusCounts = is_array($viewData['statusCounts'] ?? null) ? $viewData['statusCounts'] : [];
$activeStatusFilter = (string) ($viewData['activeStatusFilter'] ?? 'ALL');
$csrfToken = (string) ($viewData['csrfToken'] ?? ($_SESSION['csrf_token'] ?? ''));

$totalCount = (int) ($statusCounts['total'] ?? count($myPlaces));
$pendingCount = (int) ($statusCounts['PENDING'] ?? 0);
$approvedCount = (int) ($statusCounts['APPROVED'] ?? 0);
$rejectedCount = (int) ($statusCounts['REJECTED'] ?? 0);
$hiddenCount = (int) ($statusCounts['HIDDEN'] ?? 0);

$profilePageUrl = createRouteUrl('profile');
$favoritesPageUrl = createRouteUrl('favorites');
$createPlaceUrl = createRouteUrl('places/create');
$myPlacesUrl = createRouteUrl('places/mine');
$logoutUrl = createRouteUrl('logout');

$userFullName = (string) ($userInformation['full_name'] ?? 'Thành viên');
?>

<section class="hs-place-mine-hero" aria-label="Ảnh bìa Địa điểm của tôi">
    <div class="hs-place-mine-hero__overlay"></div>
    <div class="container hs-place-mine-hero__inner">
        <div class="hs-place-mine-hero__slogan">
            <span class="hs-place-mine-hero__script">Việt Nam ơi !</span>
            <p class="hs-place-mine-hero__sub mb-0">
                Khám phá <em>nhiều hơn</em> cùng Hot Spot
            </p>
        </div>
    </div>
</section>

<div class="hs-place-mine-page">
    <div class="container">
        <?php if (isset($viewData['successMessage']) && $viewData['successMessage'] !== '') { ?>
            <div class="alert alert-success" role="status">
                <?php echo escapeHtml((string) $viewData['successMessage']); ?>
            </div>
        <?php } ?>
        <div class="row g-3 g-xl-4 hs-place-mine-layout">
            <div class="col-12 col-lg-3 hs-place-mine-layout__sidebar">
                <aside class="hs-place-mine-sidebar" aria-label="Điều hướng tài khoản">
                    <div class="hs-place-mine-account">
                        <img
                            class="hs-place-mine-account__avatar"
                            src="assets/images/default-avatar.svg"
                            alt="Ảnh đại diện của <?php echo escapeHtml($userFullName); ?>"
                            width="64"
                            height="64">
                        <strong class="hs-place-mine-account__name">
                            <?php echo escapeHtml($userFullName); ?>
                        </strong>
                        <a class="hs-place-mine-account__link" href="<?php echo escapeHtml($profilePageUrl); ?>">
                            <span>Xem hồ sơ cá nhân</span>
                            <svg class="hs-place-mine-svg hs-place-mine-svg--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>

                    <nav class="hs-place-mine-menu" aria-label="Menu cá nhân">
                        <a class="hs-place-mine-menu__item" href="<?php echo escapeHtml($profilePageUrl); ?>">
                            <span class="hs-place-mine-menu__icon" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <path d="M3 10.5L12 3l9 7.5V20a1 1 0 0 1-1 1h-5.5v-6h-5v6H4a1 1 0 0 1-1-1v-9.5z"></path>
                                </svg>
                            </span>
                            <span>Tổng quan</span>
                        </a>

                        <a
                            class="hs-place-mine-menu__item is-active"
                            href="<?php echo escapeHtml($myPlacesUrl); ?>"
                            aria-current="page">
                            <span class="hs-place-mine-menu__icon" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <path d="M12 21s-7-5.686-7-11a7 7 0 1 1 14 0c0 5.314-7 11-7 11z"></path>
                                    <circle cx="12" cy="10" r="2.5"></circle>
                                </svg>
                            </span>
                            <span>Địa điểm của tôi</span>
                        </a>

                        <a class="hs-place-mine-menu__item" href="<?php echo escapeHtml($favoritesPageUrl); ?>">
                            <span class="hs-place-mine-menu__icon" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <path d="M12 20.5s-7.5-4.35-9.5-8.5C.8 8.5 3.2 4.5 7 4.5c2.1 0 3.8 1.1 5 2.7 1.2-1.6 2.9-2.7 5-2.7 3.8 0 6.2 4 4.5 7.5-2 4.15-9.5 8.5-9.5 8.5z"></path>
                                </svg>
                            </span>
                            <span>Yêu thích</span>
                        </a>

                        <a class="hs-place-mine-menu__item" href="<?php echo escapeHtml($profilePageUrl . '&tab=reviews'); ?>">
                            <span class="hs-place-mine-menu__icon" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <path d="M21 15a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10z"></path>
                                </svg>
                            </span>
                            <span>Đánh giá của tôi</span>
                        </a>

                        <a class="hs-place-mine-menu__item" href="<?php echo escapeHtml($profilePageUrl); ?>">
                            <span class="hs-place-mine-menu__icon" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.09a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                </svg>
                            </span>
                            <span>Cài đặt tài khoản</span>
                        </a>
                    </nav>

                    <form
                        method="post"
                        action="<?php echo escapeHtml($logoutUrl); ?>"
                        class="hs-place-mine-logout-form">
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php echo escapeHtml($csrfToken); ?>">
                        <button type="submit" class="hs-place-mine-logout-btn">
                            <span class="hs-place-mine-menu__icon" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                            </span>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </aside>
            </div>

            <div class="col-12 col-lg-9 hs-place-mine-layout__main">
                <header class="hs-place-mine-header">
                    <div class="hs-place-mine-header__top">
                        <div class="hs-place-mine-heading">
                            <h1 class="hs-place-mine-title">Địa điểm của tôi</h1>
                            <p class="hs-place-mine-subtitle mb-0">
                                Quản lý các địa điểm bạn đã đăng lên Hot Spot. Theo dõi trạng thái duyệt và chỉnh sửa thông tin khi cần thiết.
                            </p>
                        </div>

                        <a class="btn hs-place-mine-create-btn" href="<?php echo escapeHtml($createPlaceUrl); ?>">
                            <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Đăng địa điểm mới</span>
                        </a>
                    </div>

                    <nav class="hs-place-mine-tabs" aria-label="Lọc theo trạng thái duyệt">
                        <a
                            class="hs-place-mine-tab <?php echo $activeStatusFilter === 'ALL' ? 'is-active' : ''; ?>"
                            href="<?php echo escapeHtml($myPlacesUrl); ?>">
                            <span class="hs-place-mine-tab__icon hs-place-mine-tab__icon--all" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <line x1="8" y1="6" x2="20" y2="6"></line>
                                    <line x1="8" y1="12" x2="20" y2="12"></line>
                                    <line x1="8" y1="18" x2="20" y2="18"></line>
                                    <circle cx="4" cy="6" r="1"></circle>
                                    <circle cx="4" cy="12" r="1"></circle>
                                    <circle cx="4" cy="18" r="1"></circle>
                                </svg>
                            </span>
                            <span>Tất cả (<?php echo $totalCount; ?>)</span>
                        </a>

                        <a
                            class="hs-place-mine-tab <?php echo $activeStatusFilter === 'PENDING' ? 'is-active' : ''; ?>"
                            href="<?php echo escapeHtml($myPlacesUrl . '&status=PENDING'); ?>">
                            <span class="hs-place-mine-tab__icon hs-place-mine-tab__icon--pending" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <polyline points="12 7 12 12 15 15"></polyline>
                                </svg>
                            </span>
                            <span>Chờ duyệt (<?php echo $pendingCount; ?>)</span>
                        </a>

                        <a
                            class="hs-place-mine-tab <?php echo $activeStatusFilter === 'APPROVED' ? 'is-active' : ''; ?>"
                            href="<?php echo escapeHtml($myPlacesUrl . '&status=APPROVED'); ?>">
                            <span class="hs-place-mine-tab__icon hs-place-mine-tab__icon--approved" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <polyline points="8.5 12.5 11 15 15.5 9.5"></polyline>
                                </svg>
                            </span>
                            <span>Đã duyệt (<?php echo $approvedCount; ?>)</span>
                        </a>

                        <a
                            class="hs-place-mine-tab <?php echo $activeStatusFilter === 'REJECTED' ? 'is-active' : ''; ?>"
                            href="<?php echo escapeHtml($myPlacesUrl . '&status=REJECTED'); ?>">
                            <span class="hs-place-mine-tab__icon hs-place-mine-tab__icon--rejected" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                            </span>
                            <span>Bị từ chối (<?php echo $rejectedCount; ?>)</span>
                        </a>

                        <a
                            class="hs-place-mine-tab <?php echo $activeStatusFilter === 'HIDDEN' ? 'is-active' : ''; ?>"
                            href="<?php echo escapeHtml($myPlacesUrl . '&status=HIDDEN'); ?>">
                            <span class="hs-place-mine-tab__icon hs-place-mine-tab__icon--hidden" aria-hidden="true">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-10-8-10-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                                    <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 8 10 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                                    <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="2" y1="2" x2="22" y2="22"></line>
                                </svg>
                            </span>
                            <span>Đã ẩn (<?php echo $hiddenCount; ?>)</span>
                        </a>
                    </nav>
                </header>

                <?php if (count($myPlaces) === 0) { ?>
                    <section class="hs-place-mine-empty" aria-label="Danh sách trống">
                        <div class="hs-place-mine-empty__icon" aria-hidden="true">
                            <svg class="hs-place-mine-svg hs-place-mine-svg--lg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <h2 class="hs-place-mine-empty__title">
                            <?php if ($activeStatusFilter === 'ALL') { ?>
                                Bạn chưa đăng địa điểm nào
                            <?php } else { ?>
                                Không có địa điểm nào ở trạng thái này
                            <?php } ?>
                        </h2>
                        <p class="hs-place-mine-empty__copy">
                            <?php if ($activeStatusFilter === 'ALL') { ?>
                                Chia sẻ những quán ngon, góc cà phê đẹp hoặc điểm du lịch thú vị để cộng đồng cùng khám phá.
                            <?php } else { ?>
                                Bạn có thể chuyển sang mục "Tất cả" để xem toàn bộ địa điểm đã đăng hoặc đăng địa điểm mới.
                            <?php } ?>
                        </p>
                        <div class="hs-place-mine-empty__actions">
                            <?php if ($activeStatusFilter !== 'ALL') { ?>
                                <a class="btn hs-place-mine-btn-view" href="<?php echo escapeHtml($myPlacesUrl); ?>">
                                    <span>Xem tất cả địa điểm</span>
                                </a>
                            <?php } ?>
                            <a class="btn hs-place-mine-create-btn" href="<?php echo escapeHtml($createPlaceUrl); ?>">
                                <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Đăng địa điểm mới</span>
                            </a>
                        </div>
                    </section>
                <?php } else { ?>
                    <section class="hs-place-mine-list-box" aria-label="Danh sách địa điểm đã đăng">
                        <div class="hs-place-mine-thead d-none d-lg-grid" aria-hidden="true">
                            <div>Địa điểm</div>
                            <div>Danh mục</div>
                            <div>Ngày đăng</div>
                            <div>Trạng thái</div>
                            <div>Thao tác</div>
                        </div>

                        <div class="hs-place-mine-rows">
                            <?php foreach ($myPlaces as $placeItem) {
                                $placeId = (int) ($placeItem['place_id'] ?? 0);
                                $categoryId = (int) ($placeItem['category_id'] ?? 0);
                                $statusCode = (string) ($placeItem['status_code'] ?? '');
                                $statusName = trim((string) ($placeItem['status_name'] ?? ''));
                                $rejectionReason = trim((string) ($placeItem['rejection_reason'] ?? ''));

                                $statusBadgeModifier = 'hs-place-mine-badge--pending';
                                $defaultStatusLabel = 'Chờ duyệt';
                                $statusSubnote = 'Đang được xem xét, vui lòng chờ...';
                                $statusSubnoteModifier = '';

                                if ($statusCode === 'APPROVED') {
                                    $statusBadgeModifier = 'hs-place-mine-badge--approved';
                                    $defaultStatusLabel = 'Đã duyệt';
                                    $statusSubnote = 'Xuất hiện trên trang công khai';
                                } elseif ($statusCode === 'REJECTED') {
                                    $statusBadgeModifier = 'hs-place-mine-badge--rejected';
                                    $defaultStatusLabel = 'Bị từ chối';
                                    $statusSubnote = 'Cần chỉnh sửa và gửi duyệt lại';
                                    $statusSubnoteModifier = 'is-rejected';
                                } elseif ($statusCode === 'HIDDEN') {
                                    $statusBadgeModifier = 'hs-place-mine-badge--hidden';
                                    $defaultStatusLabel = 'Đã ẩn';
                                    $statusSubnote = 'Địa điểm này hiện đang bị ẩn';
                                }

                                if ($statusName === '') {
                                    $statusName = $defaultStatusLabel;
                                }

                                $categoryModifier = 'hs-place-mine-cat--default';
                                if ($categoryId >= 1 && $categoryId <= 6) {
                                    $categoryModifier = 'hs-place-mine-cat--' . $categoryId;
                                }

                                $detailPageUrl = createRouteUrl('places/show') . '&id=' . $placeId;
                                $coverImageUrl = (string) ($placeItem['cover_image_url'] ?? 'assets/images/home/travel.webp');
                                $fallbackImageUrl = (string) ($placeItem['fallback_image_url'] ?? 'assets/images/home/travel.webp');

                                $wardName = trim((string) ($placeItem['ward_name'] ?? ''));
                                $provinceName = trim((string) ($placeItem['province_name'] ?? ''));
                                $addressText = trim((string) ($placeItem['address'] ?? ''));

                                $locationDisplay = $provinceName;
                                if ($wardName !== '' && $provinceName !== '') {
                                    $locationDisplay = $wardName . ', ' . $provinceName;
                                } elseif ($wardName !== '') {
                                    $locationDisplay = $wardName;
                                } elseif ($addressText !== '') {
                                    $locationDisplay = $addressText;
                                }

                                $createdAtRaw = (string) ($placeItem['created_at'] ?? '');
                                $createdDateLine = '—';
                                $createdTimeLine = '';
                                if ($createdAtRaw !== '') {
                                    $createdTimestamp = strtotime($createdAtRaw);
                                    if ($createdTimestamp !== false) {
                                        $createdDateLine = date('d/m/Y', $createdTimestamp);
                                        $createdTimeLine = date('H:i', $createdTimestamp);
                                    }
                                }

                                $isEditableStatus = ($statusCode === 'PENDING' || $statusCode === 'REJECTED');
                            ?>
                                <article class="hs-place-mine-card">
                                    <div class="hs-place-mine-row">
                                        <div class="hs-place-mine-col hs-place-mine-col--place">
                                            <a
                                                class="hs-place-mine-card__media"
                                                href="<?php echo escapeHtml($detailPageUrl); ?>">
                                                <img
                                                    class="hs-place-mine-card__img"
                                                    src="<?php echo escapeHtml($coverImageUrl); ?>"
                                                    alt="<?php echo escapeHtml((string) ($placeItem['title'] ?? '')); ?>"
                                                    width="116"
                                                    height="72"
                                                    loading="lazy"
                                                    onerror="this.onerror=null;this.src='<?php echo escapeHtml($fallbackImageUrl); ?>';">
                                            </a>

                                            <div class="hs-place-mine-card__info">
                                                <h2 class="hs-place-mine-card__title">
                                                    <a href="<?php echo escapeHtml($detailPageUrl); ?>">
                                                        <?php echo escapeHtml((string) ($placeItem['title'] ?? '')); ?>
                                                    </a>
                                                </h2>

                                                <?php if ($locationDisplay !== '') { ?>
                                                    <p class="hs-place-mine-card__location">
                                                        <span class="hs-place-mine-card__pin" aria-hidden="true">
                                                            <svg class="hs-place-mine-svg hs-place-mine-svg--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                                <path d="M12 21s-7-5.686-7-11a7 7 0 1 1 14 0c0 5.314-7 11-7 11z"></path>
                                                                <circle cx="12" cy="10" r="2.5"></circle>
                                                            </svg>
                                                        </span>
                                                        <span><?php echo escapeHtml($locationDisplay); ?></span>
                                                    </p>
                                                <?php } ?>

                                                <p class="hs-place-mine-card__desc">
                                                    <?php echo escapeHtml((string) ($placeItem['description'] ?? '')); ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="hs-place-mine-col hs-place-mine-col--category">
                                            <span class="hs-place-mine-col__mobile-label d-lg-none">Danh mục:</span>
                                            <span class="hs-place-mine-cat <?php echo escapeHtml($categoryModifier); ?>">
                                                <span class="hs-place-mine-cat__icon" aria-hidden="true">
                                                    <?php if ($categoryId === 2) { ?>
                                                        <svg class="hs-place-mine-svg hs-place-mine-svg--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                            <path d="M17 8h1a4 4 0 1 1 0 8h-1"></path>
                                                            <path d="M3 8h14v7a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"></path>
                                                        </svg>
                                                    <?php } elseif ($categoryId === 3) { ?>
                                                        <svg class="hs-place-mine-svg hs-place-mine-svg--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                            <polygon points="3 20 9 8 15 16 18 12 21 20 3 20"></polygon>
                                                        </svg>
                                                    <?php } elseif ($categoryId === 5) { ?>
                                                        <svg class="hs-place-mine-svg hs-place-mine-svg--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                            <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.4 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10z"></path>
                                                            <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"></path>
                                                        </svg>
                                                    <?php } else { ?>
                                                        <svg class="hs-place-mine-svg hs-place-mine-svg--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                                        </svg>
                                                    <?php } ?>
                                                </span>
                                                <span><?php echo escapeHtml((string) ($placeItem['category_name'] ?? 'Địa điểm')); ?></span>
                                            </span>
                                        </div>

                                        <div class="hs-place-mine-col hs-place-mine-col--date">
                                            <span class="hs-place-mine-col__mobile-label d-lg-none">Ngày đăng:</span>
                                            <div class="hs-place-mine-date">
                                                <strong class="hs-place-mine-date__day">
                                                    <?php echo escapeHtml($createdDateLine); ?>
                                                </strong>
                                                <?php if ($createdTimeLine !== '') { ?>
                                                    <small class="hs-place-mine-date__time">
                                                        <?php echo escapeHtml($createdTimeLine); ?>
                                                    </small>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <div class="hs-place-mine-col hs-place-mine-col--status">
                                            <span class="hs-place-mine-col__mobile-label d-lg-none">Trạng thái:</span>
                                            <div class="hs-place-mine-status-wrap">
                                                <span class="hs-place-mine-badge <?php echo escapeHtml($statusBadgeModifier); ?>">
                                                    <span class="hs-place-mine-badge__icon" aria-hidden="true">
                                                        <?php if ($statusCode === 'APPROVED') { ?>
                                                            <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                                <circle cx="12" cy="12" r="9"></circle>
                                                                <polyline points="8.5 12.5 11 15 15.5 9.5"></polyline>
                                                            </svg>
                                                        <?php } elseif ($statusCode === 'REJECTED') { ?>
                                                            <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                                <circle cx="12" cy="12" r="9"></circle>
                                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                                            </svg>
                                                        <?php } elseif ($statusCode === 'HIDDEN') { ?>
                                                            <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-10-8-10-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                                                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 10 8 10 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                                                                <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>
                                                                <line x1="2" y1="2" x2="22" y2="22"></line>
                                                            </svg>
                                                        <?php } else { ?>
                                                            <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                                <circle cx="12" cy="12" r="9"></circle>
                                                                <polyline points="12 7 12 12 15 15"></polyline>
                                                            </svg>
                                                        <?php } ?>
                                                    </span>
                                                    <span><?php echo escapeHtml($statusName); ?></span>
                                                </span>
                                                <small class="hs-place-mine-status-note <?php echo escapeHtml($statusSubnoteModifier); ?>">
                                                    <?php echo escapeHtml($statusSubnote); ?>
                                                </small>
                                            </div>
                                        </div>

                                        <div class="hs-place-mine-col hs-place-mine-col--actions">
                                            <div class="hs-place-mine-actions">
                                                <a
                                                    class="btn hs-place-mine-btn-view"
                                                    href="<?php echo escapeHtml($detailPageUrl); ?>">
                                                    <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    <span>Xem</span>
                                                </a>

                                                <?php if ($isEditableStatus) { ?>
                                                    <?php if ($statusCode === 'REJECTED') { ?>
                                                        <button
                                                            type="button"
                                                            class="btn hs-place-mine-btn-resubmit"
                                                            disabled
                                                            aria-disabled="true"
                                                            title="Chức năng chỉnh sửa và gửi duyệt lại đang được hoàn thiện.">
                                                            <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                                <polyline points="23 4 23 10 17 10"></polyline>
                                                                <polyline points="1 20 1 14 7 14"></polyline>
                                                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                                            </svg>
                                                            <span>Gửi duyệt lại</span>
                                                        </button>
                                                    <?php } else { ?>
                                                        <button
                                                            type="button"
                                                            class="btn hs-place-mine-btn-edit"
                                                            disabled
                                                            aria-disabled="true"
                                                            title="Chức năng chỉnh sửa đang được hoàn thiện.">
                                                            <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                                <path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                                            </svg>
                                                            <span>Chỉnh sửa</span>
                                                        </button>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="hs-place-mine-lock-label">
                                                        <svg class="hs-place-mine-svg hs-place-mine-svg--xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                            <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                                                            <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                                                        </svg>
                                                        <span>Không chỉnh sửa</span>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($statusCode === 'REJECTED') { ?>
                                        <div class="hs-place-mine-rejection" role="note">
                                            <span class="hs-place-mine-rejection__icon" aria-hidden="true">
                                                <svg class="hs-place-mine-svg hs-place-mine-svg--sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                                                    <circle cx="12" cy="12" r="9"></circle>
                                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                                </svg>
                                            </span>
                                            <div class="hs-place-mine-rejection__content">
                                                <strong class="hs-place-mine-rejection__title">
                                                    Lý do từ chối:
                                                </strong>
                                                <p class="hs-place-mine-rejection__reason mb-0">
                                                    <?php if ($rejectionReason !== '') { ?>
                                                        <?php echo escapeHtml($rejectionReason); ?>
                                                    <?php } else { ?>
                                                        Chưa có ghi chú lý do cụ thể từ quản trị viên.
                                                    <?php } ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </article>
                            <?php } ?>
                        </div>
                    </section>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
