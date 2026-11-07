<div
    class="hs-ai-description"
    data-ai-description
    data-request-url="<?php echo escapeHtml(createRouteUrl('api/ai/place-description')); ?>">
    <div class="hs-ai-description__toolbar">
        <label for="place-description" class="form-label hs-place-form__label">
            Mô tả <span class="hs-place-form__required" aria-hidden="true">*</span>
        </label>
        <button
            type="button"
            class="hs-ai-summary__button"
            data-ai-description-generate
            aria-controls="ai-description-result">Tạo bản nháp</button>
    </div>
    <textarea
        id="place-description"
        name="description"
        class="form-control hs-place-form__control hs-place-form__textarea"
        rows="3"
        maxlength="1000"
        placeholder="Ghi vài ý thực tế về địa điểm hoặc tự viết phần giới thiệu..."
        required
        aria-describedby="place-description-counter place-description-error ai-description-help"><?php echo escapeHtml($formValues['description']); ?></textarea>
    <div class="hs-place-form__meta-row">
        <p id="place-description-error" class="hs-place-form__field-error" role="alert" hidden></p>
        <small id="place-description-counter" class="hs-place-form__counter">
            <span data-place-description-count><?php echo $initialDescriptionLength; ?></span>/1000
        </small>
    </div>
    <p id="ai-description-help" class="hs-ai-summary__description">AI dùng tên địa điểm và nội dung trong ô Mô tả để hỗ trợ diễn đạt.</p>
    <p class="hs-ai-summary__feedback" data-ai-description-feedback role="status" hidden></p>
    <div id="ai-description-result" class="hs-ai-summary__result" data-ai-description-result hidden>
        <p class="hs-ai-summary__note mb-2">Bản nháp AI</p>
        <p class="hs-ai-summary__text" data-ai-description-text></p>
        <p class="hs-ai-summary__note">Kiểm tra thông tin rồi chọn dùng để thay nội dung trong ô Mô tả.</p>
        <button type="button" class="hs-ai-summary__button mt-3" data-ai-description-apply disabled>Dùng bản nháp</button>
    </div>
    <noscript><p class="hs-ai-summary__description mt-2">Vui lòng bật JavaScript để sử dụng tính năng AI.</p></noscript>
</div>
