<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller. */

$userInformation = [];
if (is_array($viewData['userInformation'] ?? null)) {
    $userInformation = $viewData['userInformation'];
}
$myPlaces = [];
if (is_array($viewData['myPlaces'] ?? null)) {
    $myPlaces = $viewData['myPlaces'];
}
$statusCounts = [];
if (is_array($viewData['statusCounts'] ?? null)) {
    $statusCounts = $viewData['statusCounts'];
}
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
                        </a>
                    </div>

                    <nav class="hs-place-mine-menu" aria-label="Menu cá nhân">
                        <a class="hs-place-mine-menu__item" href="<?php echo escapeHtml($profilePageUrl); ?>">
                            <span>Tổng quan</span>
                        </a>

                        <a
                            class="hs-place-mine-menu__item is-active"
                            href="<?php echo escapeHtml($myPlacesUrl); ?>"
                            aria-current="page">
                            <span>Địa điểm của tôi</span>
                        </a>

                        <a class="hs-place-mine-menu__item" href="<?php echo escapeHtml($favoritesPageUrl); ?>">
                            <span>Yêu thích</span>
                        </a>

                        <a class="hs-place-mine-menu__item" href="<?php echo escapeHtml($profilePageUrl . '&tab=reviews'); ?>">
                            <span>Đánh giá của tôi</span>
                        </a>

                        <a class="hs-place-mine-menu__item" href="<?php echo escapeHtml($profilePageUrl); ?>">
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
                            <span>Đăng địa điểm mới</span>
                        </a>
                    </div>

                    <nav class="hs-place-mine-tabs" aria-label="Lọc theo trạng thái duyệt">
                        <a
                            class="hs-place-mine-tab <?php if ($activeStatusFilter === 'ALL') { echo 'is-active'; } ?>"
                            href="<?php echo escapeHtml($myPlacesUrl); ?>">
                            <span>Tất cả (<?php echo $totalCount; ?>)</span>
                        </a>

                        <a
                            class="hs-place-mine-tab <?php if ($activeStatusFilter === 'PENDING') { echo 'is-active'; } ?>"
                            href="<?php echo escapeHtml($myPlacesUrl . '&status=PENDING'); ?>">
                            <span>Chờ duyệt (<?php echo $pendingCount; ?>)</span>
                        </a>

                        <a
                            class="hs-place-mine-tab <?php if ($activeStatusFilter === 'APPROVED') { echo 'is-active'; } ?>"
                            href="<?php echo escapeHtml($myPlacesUrl . '&status=APPROVED'); ?>">
                            <span>Đã duyệt (<?php echo $approvedCount; ?>)</span>
                        </a>

                        <a
                            class="hs-place-mine-tab <?php if ($activeStatusFilter === 'REJECTED') { echo 'is-active'; } ?>"
                            href="<?php echo escapeHtml($myPlacesUrl . '&status=REJECTED'); ?>">
                            <span>Bị từ chối (<?php echo $rejectedCount; ?>)</span>
                        </a>

                        <a
                            class="hs-place-mine-tab <?php if ($activeStatusFilter === 'HIDDEN') { echo 'is-active'; } ?>"
                            href="<?php echo escapeHtml($myPlacesUrl . '&status=HIDDEN'); ?>">
                            <span>Đã ẩn (<?php echo $hiddenCount; ?>)</span>
                        </a>
                    </nav>
                </header>

                <?php if (count($myPlaces) === 0) { ?>
                    <section class="hs-place-mine-empty" aria-label="Danh sách trống">
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
                                                    <span>Xem</span>
                                                </a>

                                                <?php if ($isEditableStatus) { ?>
                                                    <?php if ($statusCode === 'REJECTED') { ?>
                                                        <a
                                                            class="btn hs-place-mine-btn-resubmit"
                                                            href="<?php echo escapeHtml(createRouteUrl('places/edit') . '&id=' . (int) $placeItem['place_id'] . '#place-resubmit-btn'); ?>">
                                                            <span>Gửi duyệt lại</span>
                                                        </a>
                                                    <?php } else { ?>
                                                        <a
                                                            class="btn hs-place-mine-btn-edit"
                                                            href="<?php echo escapeHtml(createRouteUrl('places/edit') . '&id=' . (int) $placeItem['place_id']); ?>">
                                                            <span>Chỉnh sửa</span>
                                                        </a>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <span class="hs-place-mine-lock-label">
                                                        <span>Không chỉnh sửa</span>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($statusCode === 'REJECTED') { ?>
                                        <div class="hs-place-mine-rejection" role="note">
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
