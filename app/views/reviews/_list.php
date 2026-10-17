<?php
/** @var array<int, array<string, mixed>> $reviewList Visible reviews, each with full_name, rating, review_text, created_at. */
$reviewList = is_array($viewData['reviewList'] ?? null) ? $viewData['reviewList'] : [];
$reviewCount = count($reviewList);
?>
<section class="hs-reviews" aria-label="Đánh giá từ cộng đồng">
    <div class="hs-reviews__heading">
        <div>
            <span class="hs-reviews__eyebrow">CẢM NHẬN THỰC TẾ</span>
            <h3>Đánh giá từ cộng đồng</h3>
        </div>
        <span class="hs-reviews__count"><?php echo escapeHtml((string) $reviewCount); ?> đánh giá</span>
    </div>

    <?php if ($reviewCount === 0) { ?>
        <div class="hs-reviews__empty">
            <span aria-hidden="true">✦</span>
            <strong>Hãy là người đầu tiên chia sẻ trải nghiệm</strong>
            <p>Đánh giá của bạn sẽ giúp mọi người chọn được điểm đến phù hợp.</p>
        </div>
    <?php } else { ?>
        <div class="hs-review-list">
            <?php foreach ($reviewList as $review) {
                $rating = max(1, min(5, (int) ($review['rating'] ?? 0)));
                $reviewName = (string) ($review['full_name'] ?? 'Thành viên Hot Spot');
                $reviewDate = (string) ($review['created_at'] ?? '');
            ?>
                <article class="hs-review">
                    <div class="hs-review__avatar" aria-hidden="true"><?php echo escapeHtml(mb_strtoupper(mb_substr($reviewName, 0, 1, 'UTF-8'), 'UTF-8')); ?></div>
                    <div class="hs-review__body">
                        <div class="hs-review__topline">
                            <strong><?php echo escapeHtml($reviewName); ?></strong>
                            <?php if ($reviewDate !== '') { ?><time><?php echo escapeHtml($reviewDate); ?></time><?php } ?>
                        </div>
                        <div class="hs-review__stars" aria-label="<?php echo escapeHtml((string) $rating); ?> trên 5 sao">
                            <?php for ($star = 1; $star <= 5; $star++) { ?><span class="<?php echo $star <= $rating ? 'is-active' : ''; ?>" aria-hidden="true">★</span><?php } ?>
                        </div>
                        <p><?php echo nl2br(escapeHtml((string) ($review['review_text'] ?? ''))); ?></p>
                    </div>
                </article>
            <?php } ?>
        </div>
    <?php } ?>
</section>
