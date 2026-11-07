<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller trang Detail. */
$galleryImageUrls = array_slice($viewData['imageUrls'], 0, 3);
$galleryFallback = (string) $viewData['fallbackImageUrl'];
$galleryTitle = (string) $viewData['place']['title'];
if (count($galleryImageUrls) === 0) {
    $galleryImageUrls[] = $galleryFallback;
}
$galleryCount = count($galleryImageUrls);
?>
<section class="hs-place-gallery" data-place-gallery aria-label="Ảnh địa điểm">
    <a class="hs-place-gallery__main" data-gallery-open data-gallery-picture
        href="<?php echo escapeHtml($galleryImageUrls[0]); ?>" target="_blank" rel="noopener noreferrer"
        aria-label="Xem ảnh địa điểm ở kích thước lớn">
        <img data-gallery-main data-gallery-image data-fallback-src="<?php echo escapeHtml($galleryFallback); ?>"
            src="<?php echo escapeHtml($galleryImageUrls[0]); ?>"
            alt="Ảnh 1 của <?php echo escapeHtml($galleryTitle); ?>" decoding="async">
        <span data-gallery-unavailable class="hs-place-gallery__unavailable" hidden>Chưa tải được ảnh địa điểm.</span>
        <span class="hs-place-gallery__open-label">Xem ảnh lớn</span>
    </a>
    <p class="hs-place-gallery__counter" data-gallery-counter aria-live="polite">
        Ảnh 1 / <?php echo $galleryCount; ?>
    </p>
    <div class="hs-place-gallery__thumbnails" aria-label="Chọn ảnh địa điểm">
        <?php foreach ($galleryImageUrls as $galleryIndex => $galleryUrl) { ?>
            <button type="button" class="hs-place-gallery__thumbnail" data-gallery-thumbnail
                data-image-src="<?php echo escapeHtml($galleryUrl); ?>"
                aria-label="Xem ảnh <?php echo $galleryIndex + 1; ?>"
                aria-current="<?php if ($galleryIndex === 0) { echo 'true'; } else { echo 'false'; } ?>">
                <span data-gallery-picture>
                    <img data-gallery-image data-fallback-src="<?php echo escapeHtml($galleryFallback); ?>"
                        src="<?php echo escapeHtml($galleryUrl); ?>" alt="" loading="lazy" decoding="async">
                    <span data-gallery-unavailable hidden>Chưa tải được ảnh</span>
                </span>
                <span>Ảnh <?php echo $galleryIndex + 1; ?></span>
            </button>
        <?php } ?>
    </div>
    <dialog class="hs-place-gallery__dialog" data-gallery-dialog aria-labelledby="place-gallery-heading">
        <header class="hs-place-gallery__dialog-header">
            <h2 id="place-gallery-heading">Ảnh <?php echo escapeHtml($galleryTitle); ?></h2>
            <button type="button" class="btn btn-light btn-sm" data-gallery-close>Đóng</button>
        </header>
        <div class="hs-place-gallery__viewer" data-gallery-picture>
            <img data-gallery-full data-gallery-image data-fallback-src="<?php echo escapeHtml($galleryFallback); ?>"
                alt="Ảnh địa điểm ở kích thước lớn">
            <span data-gallery-unavailable class="hs-place-gallery__unavailable" hidden>Chưa tải được ảnh địa điểm.</span>
        </div>
        <footer class="hs-place-gallery__dialog-controls">
            <button type="button" class="btn btn-light btn-sm" data-gallery-previous
                <?php if ($galleryCount === 1) { echo 'disabled'; } ?>>Ảnh trước</button>
            <span data-gallery-dialog-counter aria-live="polite">Ảnh 1 / <?php echo $galleryCount; ?></span>
            <button type="button" class="btn btn-light btn-sm" data-gallery-next
                <?php if ($galleryCount === 1) { echo 'disabled'; } ?>>Ảnh sau</button>
        </footer>
    </dialog>
</section>
