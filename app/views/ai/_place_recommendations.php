<?php
/** @var array<string, mixed> $viewData */
$recommendationUser = $viewData['userInformation'] ?? null;
$recommendationCatalog = [
    'categories' => $viewData['categories'] ?? [],
    'provinces' => $viewData['provinces'] ?? [],
    'tags' => $viewData['tags'] ?? [],
];
?>
<dialog id="ai-place-dialog" class="hs-ai-recommendations" aria-labelledby="ai-recommendations-heading"
    aria-describedby="ai-recommendations-description"
    data-ai-recommendations
    data-search-catalog="<?php echo escapeHtml((string) json_encode($recommendationCatalog, JSON_UNESCAPED_UNICODE)); ?>">
    <div class="container-fluid px-0">
        <div class="row g-3">
            <div class="col-12">
                <div class="hs-ai-recommendations__header">
                    <h2 id="ai-recommendations-heading">Gợi ý địa điểm</h2>
                    <button type="button" class="hs-ai-recommendations__close" data-ai-dialog-close>Đóng</button>
                </div>
                <p id="ai-recommendations-description" class="hs-ai-summary__description">Tìm địa điểm bằng mô tả nhu cầu của bạn.</p>
            </div>
            <div class="col-12">
                <?php if ($recommendationUser === null) { ?>
                    <a class="hs-ai-summary__button" href="<?php echo escapeHtml(createRouteUrl('login')); ?>">Đăng nhập để nhận gợi ý</a>
                <?php } else { ?>
                    <form action="<?php echo escapeHtml(createRouteUrl('api/ai/place-recommendations')); ?>"
                        method="post" data-ai-recommendations-form>
                        <input type="hidden" name="csrf_token" value="<?php echo escapeHtml((string) ($_SESSION['csrf_token'] ?? '')); ?>">
                        <label for="ai-place-request" class="hs-ai-recommendations__label">Bạn muốn khám phá điều gì?</label>
                        <div class="row g-2 align-items-start">
                            <div class="col-12 col-sm">
                                <textarea id="ai-place-request" name="request_text" rows="2" maxlength="500" required
                                    class="form-control" placeholder="Ví dụ: Top 3 quán ăn điểm cao nhất Đà Nẵng, từ 4 sao"
                                    aria-describedby="ai-place-request-help ai-place-request-count"></textarea>
                            </div>
                            <div class="col-12 col-sm-auto">
                                <button class="hs-ai-summary__button hs-ai-recommendations__submit" type="submit">Tìm gợi ý</button>
                            </div>
                        </div>
                        <small id="ai-place-request-count" class="hs-ai-summary__note hs-ai-recommendations__counter" data-ai-request-count>0/500 ký tự</small>
                        <p id="ai-place-request-help" class="hs-ai-summary__note">Hỗ trợ danh mục, tỉnh/thành, nhiều nhãn, điểm và số lượt đánh giá, lượt lưu, ngày đăng, tọa độ và top 1–12. Tiêu chí chưa có dữ liệu sẽ được báo rõ.</p>
                    </form>
                <?php } ?>
            </div>
        </div>
        <p class="hs-ai-summary__feedback" data-ai-recommendations-feedback role="status" hidden></p>
        <div data-ai-recommendations-result hidden>
            <p class="hs-ai-recommendations__filters" data-ai-applied-filters></p>
            <p class="hs-ai-recommendations__warning" data-ai-unsupported hidden></p>
            <div class="row g-3" data-ai-recommendations-cards></div>
        </div>
        <p class="hs-ai-summary__note hs-ai-recommendations__footer">Kết quả và điểm đánh giá lấy từ dữ liệu đã duyệt trên Hot Spot, không phải xếp hạng Google Maps.</p>
        <noscript><p class="hs-ai-summary__note mt-2">Vui lòng bật JavaScript để nhận gợi ý bằng AI.</p></noscript>
    </div>
</dialog>
