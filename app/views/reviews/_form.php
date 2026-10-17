<?php
/** @var array<string, mixed>|null $currentReview Existing member review for prefill. */
$currentReview = is_array($viewData['currentReview'] ?? null) ? $viewData['currentReview'] : null;
$reviewPlaceId = (int) ($viewData['placeId'] ?? 0);
$reviewCsrfToken = (string) ($viewData['csrfToken'] ?? '');
$reviewError = (string) ($viewData['reviewError'] ?? '');
$selectedRating = (int) ($currentReview['rating'] ?? 0);
?>
<?php if (($viewData['canReview'] ?? false) === true) { ?>
    <form class="hs-review-form" method="post" action="<?php echo escapeHtml((string) ($viewData['formAction'] ?? '')); ?>">
        <div class="hs-review-form__title">
            <img src="assets/images/default-avatar.svg" alt="" width="40" height="40">
            <div><strong><?php echo $currentReview === null ? 'Chia sẻ trải nghiệm của bạn' : 'Cập nhật đánh giá của bạn'; ?></strong><span>Đánh giá chân thực giúp cộng đồng khám phá tốt hơn.</span></div>
        </div>
        <?php if ($reviewError !== '') { ?><div class="alert alert-danger py-2" role="alert"><?php echo escapeHtml($reviewError); ?></div><?php } ?>
        <input type="hidden" name="csrf_token" value="<?php echo escapeHtml($reviewCsrfToken); ?>">
        <input type="hidden" name="place_id" value="<?php echo escapeHtml((string) $reviewPlaceId); ?>">
        <fieldset class="hs-review-form__rating">
            <legend>Trải nghiệm của bạn</legend>
            <div class="hs-rating-choice" role="radiogroup" aria-label="Chọn số sao">
                <?php for ($star = 5; $star >= 1; $star--) { ?>
                    <input id="review-rating-<?php echo $star; ?>" type="radio" name="rating" value="<?php echo $star; ?>" required<?php echo $selectedRating === $star ? ' checked' : ''; ?>>
                    <label for="review-rating-<?php echo $star; ?>" title="<?php echo $star; ?> sao">★</label>
                <?php } ?>
            </div>
        </fieldset>
        <label class="visually-hidden" for="review-text">Nội dung đánh giá</label>
        <textarea id="review-text" name="review_text" rows="4" maxlength="2000" required placeholder="Chia sẻ điều bạn thích về địa điểm này..." aria-describedby="review-help"><?php echo escapeHtml((string) ($currentReview['review_text'] ?? '')); ?></textarea>
        <div class="hs-review-form__footer"><small id="review-help">Tối đa 2.000 ký tự</small><button class="hs-button-primary" type="submit">Gửi đánh giá <span aria-hidden="true">↗</span></button></div>
    </form>
<?php } elseif (($viewData['isLoggedIn'] ?? false) !== true) { ?>
    <div class="hs-review-login"><span>Muốn chia sẻ trải nghiệm?</span><a href="<?php echo escapeHtml((string) ($viewData['loginUrl'] ?? 'index.php?route=login')); ?>">Đăng nhập để viết đánh giá <span aria-hidden="true">→</span></a></div>
<?php } ?>
