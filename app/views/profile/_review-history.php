<?php
/** @var array<string, mixed> $viewData */

$reviewHistory = $viewData['reviewHistory'];
$homePageUrl = createRouteUrl('home');
?>

<section class="profile-review-history" aria-labelledby="review-history-title">
    <div class="row mb-4">
        <div class="col-12">
            <h1 id="review-history-title" class="profile-section-title">Đã đánh giá</h1>
            <p class="text-secondary mb-2">
                <?php echo count($reviewHistory); ?> đánh giá · Lịch sử trải nghiệm của bạn.
            </p>
            <p class="profile-review-privacy mb-0">
                Chỉ bạn xem được lịch sử này. Đánh giá bị ẩn không xuất hiện trong danh sách đánh giá công khai.
            </p>
        </div>
    </div>

    <?php if (count($reviewHistory) === 0) { ?>
        <div class="row">
            <div class="col-12">
                <div class="profile-review-empty">
                    <h2 class="h5">Bạn chưa viết đánh giá nào</h2>
                    <p class="text-secondary">Khám phá địa điểm và chia sẻ trải nghiệm ngay trên trang chi tiết.</p>
                    <a class="btn profile-save-button" href="<?php echo escapeHtml($homePageUrl); ?>">
                        Khám phá địa điểm
                    </a>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="row g-3">
            <?php foreach ($reviewHistory as $reviewInformation) {
                $rating = (int) $reviewInformation['rating'];
                $isHiddenReview = $reviewInformation['review_status'] === 'HIDDEN';
                $isPublicPlace = $reviewInformation['place_status'] === 'APPROVED';
                $detailPageUrl = createRouteUrl('places/show');
                $detailPageUrl = $detailPageUrl . '&id=' . (int) $reviewInformation['place_id'];
            ?>
                <div class="col-12">
                    <article class="profile-review-card">
                        <div class="profile-review-heading">
                            <h2><?php echo escapeHtml($reviewInformation['place_title']); ?></h2>
                            <?php if ($isHiddenReview) { ?>
                                <span class="profile-review-status profile-review-status-hidden">Đánh giá đã bị ẩn</span>
                            <?php } elseif ($isPublicPlace === false) { ?>
                                <span class="profile-review-status profile-review-status-unavailable">Địa điểm chưa công khai</span>
                            <?php } else { ?>
                                <span class="profile-review-status">Đánh giá công khai</span>
                            <?php } ?>
                        </div>

                        <div class="profile-review-meta">
                            <span class="profile-review-stars" role="img" aria-label="<?php echo $rating; ?> trên 5 sao">
                                <?php for ($star = 1; $star <= 5; $star++) { ?>
                                    <?php if ($star <= $rating) { ?>
                                        <span aria-hidden="true">★</span>
                                    <?php } else { ?>
                                        <span class="profile-review-star-empty" aria-hidden="true">☆</span>
                                    <?php } ?>
                                <?php } ?>
                            </span>
                            <time><?php echo escapeHtml($reviewInformation['review_created_at']); ?></time>
                        </div>

                        <p class="profile-review-content"><?php echo nl2br(escapeHtml($reviewInformation['content'])); ?></p>

                        <?php if ($isHiddenReview) { ?>
                            <p class="profile-review-hidden-note">
                                Bạn vẫn xem được nội dung trong lịch sử riêng. Chỉnh sửa không tự đưa đánh giá trở lại công khai.
                            </p>
                        <?php } ?>

                        <div class="profile-review-footer">
                            <?php if ($isPublicPlace) { ?>
                                <a class="btn profile-save-button" href="<?php echo escapeHtml($detailPageUrl); ?>">
                                    Xem địa điểm
                                </a>
                            <?php } else { ?>
                                <span class="profile-review-unavailable">Địa điểm hiện chưa công khai</span>
                            <?php } ?>
                        </div>
                    </article>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</section>
