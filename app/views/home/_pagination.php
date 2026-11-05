<?php
/** @var array<string, mixed> $viewData */
$paginationPage = isset($page) && is_int($page) ? max(1, $page) : 1;
$paginationTotalPages = isset($totalPages) && is_int($totalPages)
    ? max(1, $totalPages)
    : 1;
$paginationFilters = [];

if (isset($keyword) && is_string($keyword) && $keyword !== '') {
    $paginationFilters['keyword'] = $keyword;
}

if (isset($selectedCategoryId) && is_string($selectedCategoryId) && $selectedCategoryId !== '') {
    $paginationFilters['category_id'] = $selectedCategoryId;
}

if (isset($selectedProvinceCode) && is_string($selectedProvinceCode) && $selectedProvinceCode !== '') {
    $paginationFilters['province_code'] = $selectedProvinceCode;
}

if (isset($selectedTagSlug) && is_string($selectedTagSlug) && $selectedTagSlug !== '') {
    $paginationFilters['tag_slug'] = $selectedTagSlug;
}

$firstVisiblePage = max(1, $paginationPage - 2);

if ($firstVisiblePage + 4 > $paginationTotalPages) {
    $firstVisiblePage = max(1, $paginationTotalPages - 4);
}

$lastVisiblePage = min($paginationTotalPages, $firstVisiblePage + 4);
?>

<?php if ($paginationPage > 1) : ?>
    <?php
    $previousPageQuery = $paginationFilters;
    $previousPageQuery['page'] = $paginationPage - 1;
    $previousPageUrl = createRouteUrl('home') . '&' . http_build_query($previousPageQuery);
    ?>
    <a
        class="hs-pagination__direction"
        href="<?php echo escapeHtml($previousPageUrl); ?>"
        data-page="<?php echo $paginationPage - 1; ?>">
        Trước
    </a>
<?php else : ?>
    <span class="hs-pagination__direction" aria-disabled="true">Trước</span>
<?php endif; ?>

<?php for ($visiblePage = $firstVisiblePage; $visiblePage <= $lastVisiblePage; $visiblePage++) : ?>
    <?php
    $pageQuery = $paginationFilters;
    $pageQuery['page'] = $visiblePage;
    $pageUrl = createRouteUrl('home') . '&' . http_build_query($pageQuery);
    ?>
    <?php if ($visiblePage === $paginationPage) : ?>
        <span
            class="hs-pagination__number hs-pagination__number--active"
            aria-current="page">
            <?php echo $visiblePage; ?>
        </span>
    <?php else : ?>
        <a
            class="hs-pagination__number"
            href="<?php echo escapeHtml($pageUrl); ?>"
            data-page="<?php echo $visiblePage; ?>">
            <?php echo $visiblePage; ?>
        </a>
    <?php endif; ?>
<?php endfor; ?>

<?php if ($paginationPage < $paginationTotalPages) : ?>
    <?php
    $nextPageQuery = $paginationFilters;
    $nextPageQuery['page'] = $paginationPage + 1;
    $nextPageUrl = createRouteUrl('home') . '&' . http_build_query($nextPageQuery);
    ?>
    <a
        class="hs-pagination__direction"
        href="<?php echo escapeHtml($nextPageUrl); ?>"
        data-page="<?php echo $paginationPage + 1; ?>">
        Sau
    </a>
<?php else : ?>
    <span class="hs-pagination__direction" aria-disabled="true">Sau</span>
<?php endif; ?>
