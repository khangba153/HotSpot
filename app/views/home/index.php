<?php
/** @var array<string, mixed> $viewData */
$places = $viewData['places'] ?? [];
if (!is_array($places)) {
    $places = [];
}
?>
<section class="mb-4 mb-lg-5">
    <div class="p-4 p-md-5 rounded-4 bg-body-tertiary border">
        <p class="text-uppercase text-secondary small fw-semibold mb-2">HOT SPOT · KHÁM PHÁ VIỆT NAM</p>
        <h1 class="display-6 fw-bold mb-3">Tìm một nơi đáng để ghé thăm.</h1>
        <p class="lead text-secondary mb-0">
            Khám phá những địa điểm được cộng đồng chia sẻ và đã được duyệt trên Hot Spot.
        </p>
    </div>
</section>

<section aria-labelledby="approved-places-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <div>
            <p class="text-uppercase text-secondary small fw-semibold mb-1">Gợi ý cho bạn</p>
            <h2 class="h3 fw-bold mb-0" id="approved-places-heading">Địa điểm nổi bật</h2>
        </div>
        <span class="text-secondary small">
            <?php echo count($places); ?> địa điểm
        </span>
    </div>

    <?php require __DIR__ . '/_place_cards.php'; ?>
</section>
