<?php
/** @var array<string, mixed> $reviewPartialData */
$aiSummaryReviewList = $reviewPartialData['reviewList'];
$aiSummaryIsLoggedIn = $reviewPartialData['isLoggedIn'];
?>
<section class="hs-ai-summary" aria-labelledby="ai-summary-heading" data-ai-summary>
    <div class="row g-3 align-items-center">
        <div class="col-12 col-sm">
            <h3 id="ai-summary-heading" class="hs-ai-summary__heading">Tóm tắt đánh giá bằng AI</h3>
            <p class="hs-ai-summary__description">Đọc nhanh tối đa 10 đánh giá công khai gần nhất.</p>
        </div>
        <div class="col-12 col-sm-auto">
            <?php if ($aiSummaryIsLoggedIn === false) { ?>
                <a class="hs-ai-summary__button" href="<?php echo escapeHtml(createRouteUrl('login')); ?>">Đăng nhập để dùng AI</a>
            <?php } elseif ($aiSummaryReviewList !== []) { ?>
                <form action="<?php echo escapeHtml(createRouteUrl('api/ai/review-summary')); ?>" method="post" data-ai-summary-form>
                    <input type="hidden" name="place_id" value="<?php echo escapeHtml((string) $reviewPartialData['placeId']); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo escapeHtml((string) $reviewPartialData['csrfToken']); ?>">
                    <button class="hs-ai-summary__button" type="submit">Tóm tắt đánh giá</button>
                </form>
            <?php } ?>
        </div>
    </div>

    <?php if ($aiSummaryReviewList === []) { ?>
        <p class="hs-ai-summary__description mt-2">Chưa có đánh giá công khai để tóm tắt.</p>
    <?php } ?>

    <p class="hs-ai-summary__feedback" data-ai-summary-feedback role="status" hidden></p>
    <div class="hs-ai-summary__result" data-ai-summary-result hidden>
        <p class="hs-ai-summary__text" data-ai-summary-text></p>
        <p class="hs-ai-summary__note">Nội dung do AI tóm tắt có thể chưa đầy đủ. Hãy tham khảo các đánh giá gốc bên dưới.</p>
    </div>
    <noscript><p class="hs-ai-summary__description mt-2">Vui lòng bật JavaScript để sử dụng tính năng tóm tắt.</p></noscript>
</section>
