<?php

/** @var array<int, array<string, mixed>> $places */
$demoImageByCategory = [
    1 => 'assets/images/home/food.webp',
    2 => 'assets/images/home/coffee.webp',
    3 => 'assets/images/home/travel.webp',
    4 => 'assets/images/home/fun.webp',
    5 => 'assets/images/home/nature.webp',
    6 => 'assets/images/home/culture.webp',
];

if ($places === []) :
?>
    <div class="hs-empty-state" role="status">
        <h3>Chưa có địa điểm để hiển thị</h3>
        <p>Các địa điểm đã được duyệt sẽ xuất hiện tại đây.</p>
    </div>
<?php else : ?>
    <div class="hs-place-grid">
        <?php foreach ($places as $place) :
            $placeId = (string) ($place['place_id'] ?? '');
            $title = trim((string) ($place['title'] ?? ''));
            if ($title === '') {
                $title = 'Địa điểm chưa có tên';
            }

            $categoryId = (int) ($place['category_id'] ?? 0);
            $categoryName = trim((string) ($place['category_name'] ?? ''));
            $categoryLabelImage = $categoryCardLabels[$categoryId] ?? '';
            $provinceName = trim((string) ($place['province_name'] ?? ''));
            $address = trim((string) ($place['address'] ?? ''));
            $description = trim((string) ($place['description'] ?? ''));
            $coverImage = trim((string) ($place['cover_image'] ?? ''));

            $demoImage = $demoImageByCategory[$categoryId] ?? 'assets/images/home/travel.webp';
            $isSeedPlaceholder = $coverImage === '' || $coverImage === 'assets/images/demo-placeholder.svg';
            $isLocalImage = preg_match('#^(assets|uploads)/[A-Za-z0-9._/-]+$#', $coverImage) === 1
                && strpos($coverImage, '..') === false;
            $isRemoteImage = preg_match('#^https?://#i', $coverImage) === 1
                && filter_var($coverImage, FILTER_VALIDATE_URL) !== false;
            $imageUrl = $isSeedPlaceholder
                ? $demoImage
                : (($isLocalImage || $isRemoteImage) ? $coverImage : $demoImage);

            $rating = $place['average_rating'] ?? null;
            $reviewCount = (int) ($place['review_count'] ?? 0);
            $detailUrl = createRouteUrl('places/show') . '&id=' . rawurlencode($placeId);

            if ($description !== '') {
                if (function_exists('mb_strimwidth')) {
                    $description = mb_strimwidth($description, 0, 88, '…', 'UTF-8');
                } elseif (strlen($description) > 88) {
                    $description = substr($description, 0, 85) . '...';
                }
            } else {
                $description = 'Khám phá địa điểm thú vị được cộng đồng chia sẻ trên Hot Spot.';
            }
        ?>
            <article class="hs-place-card">
                <a class="hs-place-card__image-link" href="<?php echo escapeHtml($detailUrl); ?>" aria-label="Xem <?php echo escapeHtml($title); ?>">
                    <img
                        class="hs-place-card__image"
                        src="<?php echo escapeHtml($imageUrl); ?>"
                        alt="Ảnh minh họa <?php echo escapeHtml($title); ?>"
                        loading="lazy"
                        decoding="async"
                        onerror="this.onerror=null;this.src='<?php echo escapeHtml($demoImage); ?>';">
</a>
                <?php if ($categoryName !== '') : ?>
                    <div class="hs-place-card__label">
                        <?php if ($categoryLabelImage !== '') : ?>
                            <img class="hs-place-card__label-image" src="<?php echo escapeHtml($categoryLabelImage); ?>" alt="" loading="lazy" decoding="async" onerror="this.hidden=true;">
                        <?php endif; ?>
                        <span><?php echo escapeHtml($categoryName); ?></span>
                    </div>
                <?php endif; ?>
                <div class="hs-place-card__body">
                    <h3 class="hs-place-card__title">
                        <a href="<?php echo escapeHtml($detailUrl); ?>"><?php echo escapeHtml($title); ?></a>
                    </h3>
                    <div class="hs-place-card__meta">
                        <span class="hs-rating">
                            <span aria-hidden="true">★</span>
                            <?php if ($rating !== null && $rating !== '') : ?>
                                <?php echo escapeHtml(number_format((float) $rating, 1)); ?>
                            <?php else : ?>
                                Chưa có điểm
                            <?php endif; ?>
                        </span>
                        <?php if ($reviewCount > 0) : ?>
                            <span class="hs-review-count">(<?php echo escapeHtml(number_format($reviewCount)); ?>)</span>
                        <?php endif; ?>

                    </div>
                    <p class="hs-place-card__location">
                        <?php
                        $locationParts = [];

                        if ($provinceName !== '') {
                            $locationParts[] = $provinceName;
                        }

                        if ($address !== '') {
                            $locationParts[] = $address;
                        }

                        echo escapeHtml(
                            $locationParts !== []
                                ? implode(' · ', $locationParts)
                                : 'Việt Nam'
                        );
                        ?>
                    </p>
                    <p class="hs-place-card__description"><?php echo escapeHtml($description); ?></p>
                    <div class="hs-place-card__bottom">

                        <a class="hs-place-card__more" href="<?php echo escapeHtml($detailUrl); ?>">Chi tiết </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
