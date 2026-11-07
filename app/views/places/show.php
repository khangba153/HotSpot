<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller. */
$place = $viewData['place'];
$placeViewData = $viewData;
$tags = $viewData['tags'];
$hasLocation = $viewData['hasLocation'];
$isApproved = $viewData['isApproved'];
$isOwner = $viewData['isOwner'];
$isAdmin = $viewData['isAdmin'];

$placeTitle = (string) $place['title'];
$categoryName = (string) $place['category_name'];
$provinceName = (string) $place['province_name'];
$address = (string) $place['address'];
$description = (string) $place['description'];
$statusCode = (string) $place['status_code'];
$statusName = (string) $place['status_name'];
$reviewPartialData = [
    'reviewList' => $viewData['reviewList'] ?? [],
    'currentReview' => $viewData['currentReview'] ?? null,
    'canReview' => $viewData['canReview'] ?? false,
    'isLoggedIn' => $viewData['isLoggedIn'] ?? false,
    'placeId' => $viewData['placeId'] ?? 0,
    'csrfToken' => $viewData['csrfToken'] ?? '',
];

$wardName = '';

if ($place['ward_name'] !== null) {
    $wardName = (string) $place['ward_name'];
}

// Ghép địa chỉ đầy đủ: địa chỉ chi tiết, phường/xã (nếu có), tỉnh/thành.
$fullAddressParts = [];
$fullAddressParts[] = $address;

if ($wardName !== '') {
    $fullAddressParts[] = $wardName;
}

$fullAddressParts[] = $provinceName;
$fullAddress = implode(', ', $fullAddressParts);

$latitudeText = '';
$longitudeText = '';
$mapLinkUrl = '';

if ($hasLocation) {
    $latitudeText = (string) $place['latitude'];
    $longitudeText = (string) $place['longitude'];
    $mapLinkUrl = 'https://www.openstreetmap.org/?mlat=' . rawurlencode($latitudeText)
        . '&mlon=' . rawurlencode($longitudeText)
        . '#map=16/' . rawurlencode($latitudeText) . '/' . rawurlencode($longitudeText);
}

$createdDateText = '';
$createdTimestamp = strtotime((string) $place['created_at']);

if ($createdTimestamp !== false) {
    $createdDateText = date('d/m/Y', $createdTimestamp);
}

$homePageUrl = createRouteUrl('home');
?>
<link rel="stylesheet" href="assets/css/place-detail.css?v=20261107">
<?php if ($hasLocation) { ?>
    <link rel="stylesheet" href="assets/vendor/leaflet-1.9.4/leaflet.css">
<?php } ?>
<section class="hs-place-detail">
    <div class="container">
        <nav class="hs-place-breadcrumb" aria-label="Đường dẫn">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?php echo escapeHtml($homePageUrl); ?>">Trang chủ</a>
                </li>
                <li class="breadcrumb-item">
                    <span><?php echo escapeHtml($categoryName); ?></span>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo escapeHtml($placeTitle); ?>
                </li>
            </ol>
        </nav>

        <?php if ($isApproved === false) { ?>
            <div class="alert alert-warning hs-place-status-alert" role="status">
                <strong>Trạng thái: <?php echo escapeHtml($statusName); ?>.</strong>
                <?php if ($isOwner) { ?>
                    Bài của bạn chưa được công khai; chỉ bạn và quản trị viên xem được trang này.
                <?php } else { ?>
                    Bạn đang xem với quyền quản trị viên; khách không xem được trang này.
                <?php } ?>
                <?php if ($statusCode === 'REJECTED' && $place['rejection_reason'] !== null) { ?>
                    <div class="mt-2">
                        <span class="fw-semibold">Lý do từ chối:</span>
                        <?php echo escapeHtml((string) $place['rejection_reason']); ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <?php require __DIR__ . '/_gallery.php'; ?>

                <header class="hs-place-heading">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <h1 class="hs-place-heading__title"><?php echo escapeHtml($placeTitle); ?></h1>

                        <?php if ($isApproved) { ?>
                            <div id="place-favorite-slot" class="hs-place-favorite-slot">
                                <?php require dirname(__DIR__) . '/favorites/_button.php'; ?>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="hs-place-heading__meta">
                        <span class="hs-place-chip"><?php echo escapeHtml($categoryName); ?></span>
                        <span class="hs-place-heading__location">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M19 10.2c0 5-7 10.3-7 10.3S5 15.2 5 10.2a7 7 0 1 1 14 0Z"></path>
                                <circle cx="12" cy="10" r="2.2"></circle>
                            </svg>
                            <?php echo escapeHtml($provinceName); ?>
                        </span>
                    </div>

                    <?php if (count($tags) > 0) { ?>
                        <ul class="hs-place-tags" aria-label="Nhãn">
                            <?php foreach ($tags as $tag) { ?>
                                <li class="hs-place-tags__item"><?php echo escapeHtml((string) $tag['name']); ?></li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </header>

                <section class="hs-place-card-block" aria-labelledby="place-description-heading">
                    <h2 id="place-description-heading" class="hs-place-card-block__title">Giới thiệu</h2>
                    <p class="hs-place-description"><?php echo nl2br(escapeHtml($description)); ?></p>
                </section>

                <?php if ($isApproved) { ?>
                    <section class="hs-place-card-block" aria-labelledby="place-reviews-heading">
                        <h2 id="place-reviews-heading" class="hs-place-card-block__title">Đánh giá</h2>
                        <div id="place-reviews-slot" class="hs-place-reviews-slot">
                            <?php
                            $viewData = $reviewPartialData;
                            require dirname(__DIR__) . '/reviews/_list.php';
                            require dirname(__DIR__) . '/reviews/_form.php';
                            $viewData = $placeViewData;
                            ?>
                        </div>
                    </section>
                <?php } ?>
            </div>

            <div class="col-12 col-lg-4">
                <aside class="hs-place-card-block hs-place-info" aria-labelledby="place-info-heading">
                    <h2 id="place-info-heading" class="hs-place-card-block__title">Thông tin địa điểm</h2>

                    <dl class="hs-place-info__list">
                        <div class="hs-place-info__row">
                            <dt>Địa chỉ</dt>
                            <dd><?php echo escapeHtml($fullAddress); ?></dd>
                        </div>
                        <div class="hs-place-info__row">
                            <dt>Tỉnh/Thành</dt>
                            <dd><?php echo escapeHtml($provinceName); ?></dd>
                        </div>
                        <?php if ($wardName !== '') { ?>
                            <div class="hs-place-info__row">
                                <dt>Phường/Xã</dt>
                                <dd><?php echo escapeHtml($wardName); ?></dd>
                            </div>
                        <?php } ?>
                        <div class="hs-place-info__row">
                            <dt>Danh mục</dt>
                            <dd><?php echo escapeHtml($categoryName); ?></dd>
                        </div>
                        <?php if ($createdDateText !== '') { ?>
                            <div class="hs-place-info__row">
                                <dt>Ngày đăng</dt>
                                <dd><?php echo escapeHtml($createdDateText); ?></dd>
                            </div>
                        <?php } ?>
                    </dl>
                </aside>

                <aside class="hs-place-card-block hs-place-location" aria-labelledby="place-location-heading">
                    <h2 id="place-location-heading" class="hs-place-card-block__title">Vị trí</h2>

                    <?php if ($hasLocation) { ?>
                        <div id="place-map" class="hs-place-map" data-place-map
                            data-latitude="<?php echo escapeHtml($latitudeText); ?>"
                            data-longitude="<?php echo escapeHtml($longitudeText); ?>"
                            data-place-title="<?php echo escapeHtml($placeTitle); ?>"
                            data-tile-url="https://tile.openstreetmap.org/{z}/{x}/{y}.png"
                            role="region" aria-label="Bản đồ vị trí địa điểm"></div>
                        <p id="place-map-status" class="small text-muted mt-2" role="status" aria-live="polite">
                            Nếu bản đồ chưa tải được, bạn có thể xem vị trí bằng đường dẫn OpenStreetMap bên dưới.
                        </p>
                        <p class="hs-place-location__coords">
                            <?php echo escapeHtml($latitudeText); ?>, <?php echo escapeHtml($longitudeText); ?>
                        </p>
                        <a
                            id="place-map-link"
                            class="btn btn-outline-secondary btn-sm"
                            href="<?php echo escapeHtml($mapLinkUrl); ?>"
                            target="_blank"
                            rel="noopener noreferrer">
                            Xem trên OpenStreetMap
                        </a>
                    <?php } else { ?>
                        <p class="text-muted mb-0">Địa điểm chưa cập nhật tọa độ.</p>
                    <?php } ?>
                </aside>
            </div>
        </div>
    </div>
</section>
<script src="assets/js/favorite.js" defer></script>
<script src="assets/js/gallery.js?v=20261107" defer></script>
<?php if ($hasLocation) { ?>
    <script src="assets/vendor/leaflet-1.9.4/leaflet.js" defer></script>
    <script src="assets/js/map.js?v=20261107" defer></script>
<?php } ?>
