<?php
/** @var array<int, array<string, mixed>> $places */
if ($places === []) :
?>
    <div class="border rounded-3 p-4 p-md-5 text-center" role="status">
        <div class="fs-1 mb-2" aria-hidden="true">⌖</div>
        <h3 class="h5">Chưa có địa điểm để hiển thị</h3>
        <p class="text-secondary mb-0">Các địa điểm đã được duyệt sẽ xuất hiện tại đây.</p>
    </div>
<?php else : ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 g-lg-4">
        <?php foreach ($places as $place) :
            $placeId = (string) ($place['place_id'] ?? '');
            $title = trim((string) ($place['title'] ?? ''));
            if ($title === '') {
                $title = 'Địa điểm chưa có tên';
            }
            $categoryName = trim((string) ($place['category_name'] ?? ''));
            $provinceName = trim((string) ($place['province_name'] ?? ''));
            $address = trim((string) ($place['address'] ?? ''));
            $coverImage = trim((string) ($place['cover_image'] ?? ''));
            $fallbackImage = 'assets/images/demo-placeholder.svg';

            // Chỉ cho phép ảnh từ thư mục asset/upload của website hoặc URL HTTP(S).
            $isLocalImage = preg_match('#^(assets|uploads)/[A-Za-z0-9._/-]+$#', $coverImage) === 1
                && strpos($coverImage, '..') === false;
            $isRemoteImage = preg_match('#^https?://#i', $coverImage) === 1
                && filter_var($coverImage, FILTER_VALIDATE_URL) !== false;
            $imageUrl = ($isLocalImage || $isRemoteImage) ? $coverImage : $fallbackImage;
            $detailUrl = createRouteUrl('places/show') . '&id=' . rawurlencode($placeId);
        ?>
            <div class="col">
                <article class="card h-100 border-0 shadow-sm place-card">
                    <a class="place-card__image-link" href="<?php echo escapeHtml($detailUrl); ?>" aria-label="Xem <?php echo escapeHtml($title); ?>">
                        <img
                            class="card-img-top place-card__image"
                            src="<?php echo escapeHtml($imageUrl); ?>"
                            alt="Ảnh <?php echo escapeHtml($title); ?>"
                            loading="lazy"
                            decoding="async"
                            onerror="this.onerror=null;this.src='<?php echo escapeHtml($fallbackImage); ?>';"
                        >
                    </a>
                    <div class="card-body d-flex flex-column p-3 p-lg-4">
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <?php if ($categoryName !== '') : ?>
                                <span class="badge rounded-pill text-bg-light border fw-medium">
                                    <?php echo escapeHtml($categoryName); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($provinceName !== '') : ?>
                                <span class="small text-secondary align-self-center">
                                    <?php echo escapeHtml($provinceName); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3 class="h5 fw-bold card-title mb-2">
                            <a class="text-decoration-none link-dark" href="<?php echo escapeHtml($detailUrl); ?>">
                                <?php echo escapeHtml($title); ?>
                            </a>
                        </h3>
                        <p class="small text-secondary mb-3">
                            <span aria-hidden="true">⌖</span>
                            <?php echo escapeHtml($address !== '' ? $address : 'Chưa có địa chỉ chi tiết'); ?>
                        </p>
                        <div class="mt-auto pt-1">
                            <a class="btn btn-outline-dark btn-sm" href="<?php echo escapeHtml($detailUrl); ?>">
                                Xem chi tiết <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
