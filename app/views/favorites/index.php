<?php

/** @var array<string, mixed> $viewData */

$favoritePlaces = $viewData['favoritePlaces'];
$homePageUrl = createRouteUrl('home');
$profilePageUrl = createRouteUrl('profile');
$favoritePageUrl = createRouteUrl('favorites');
$myPlacesUrl = createRouteUrl('places/mine');
$userInformation = $viewData['userInformation'];
?>

<div class="profile-cover" role="img" aria-label="Phong cảnh vịnh Hạ Long"></div>
<div class="container favorites-page py-4">
    <div class="row g-4">
        <div class="col-12 col-lg-3">
            <?php $activeAccountSection = 'favorites'; require __DIR__ . '/../profile/_sidebar.php'; ?>
        </div>
        <div class="col-12 col-lg-9">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="favorites-heading">
                        <div>
                            <h1>Địa điểm yêu thích</h1>
                            <p class="text-muted mb-0">Những địa điểm đang công khai mà bạn đã lưu.</p>
                        </div>
                        <span class="favorites-sort-label">Mới lưu nhất</span>
                    </div>
                </div>
            </div>

            <?php if (count($favoritePlaces) === 0) { ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <h2 class="h5">Chưa có địa điểm yêu thích</h2>

                                <p class="text-muted">
                                    Khám phá và lưu những địa điểm bạn muốn ghé thăm.
                                </p>

                                <a
                                    class="btn btn-outline-danger"
                                    href="<?php echo escapeHtml($homePageUrl); ?>">
                                    Khám phá địa điểm
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } else { ?>
                <div class="row g-4">
                    <?php foreach ($favoritePlaces as $placeInformation) {
                        $placeId = (int) $placeInformation['place_id'];

                        $detailPageUrl = createRouteUrl('places/show');
                        $detailPageUrl = $detailPageUrl . '&id=' . $placeId;
                    ?>
                        <div class="col-12 col-md-6 col-xxl-4">
                            <article class="card favorite-card h-100">
                                <a
                                    class="favorite-card-image-link"
                                    href="<?php echo escapeHtml($detailPageUrl); ?>">
                                    <img
                                        class="card-img-top favorite-card-image"
                                        src="<?php echo escapeHtml($placeInformation['cover_image']); ?>"
                                        alt="<?php echo escapeHtml($placeInformation['title']); ?>"
                                        loading="lazy"
                                        onerror="this.onerror=null;this.src='assets/images/home/travel.webp';">
                                </a>
                                <div class="card-body">
                                    <h2 class="h5">
                                        <?php echo escapeHtml(
                                            $placeInformation['title']
                                        ); ?>
                                    </h2>

                                    <p class="text-muted mb-2">
                                        <?php echo escapeHtml(
                                            $placeInformation['category_name']
                                        ); ?>
                                        ·
                                        <?php echo escapeHtml(
                                            $placeInformation['province_name']
                                        ); ?>
                                    </p>

                                    <p>
                                        <?php echo escapeHtml(
                                            $placeInformation['address']
                                        ); ?>
                                    </p>

                                    <p class="favorite-card-description">
                                        <?php echo escapeHtml($placeInformation['description']); ?>
                                    </p>

                                    <a
                                        class="btn btn-outline-danger favorite-detail-link"
                                        href="<?php echo escapeHtml($detailPageUrl); ?>">
                                        Xem địa điểm
                                    </a>

                                    <form
                                        class="favorite-form mt-3"
                                        method="post"
                                        action="<?php echo escapeHtml(createRouteUrl('favorites/set')); ?>"
                                        data-favorite-list="true">

                                        <input
                                            type="hidden"
                                            name="place_id"
                                            value="<?php echo $placeId; ?>">

                                        <input
                                            type="hidden"
                                            name="desired_state"
                                            value="0">

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo escapeHtml($viewData['csrfToken']); ?>">

                                        <button
                                            type="submit"
                                            class="btn btn-outline-danger">
                                            Bỏ yêu thích
                                        </button>

                                        <p
                                            class="favorite-message small mt-2 mb-0"
                                            role="status"
                                            aria-live="polite"></p>
                                    </form>
                                </div>
                            </article>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script src="assets/js/favorite.js?v=20261108" defer></script>
