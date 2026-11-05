<?php
/** @var array<string, mixed> $viewData */
$places = $viewData['places'] ?? [];
$categories = $viewData['categories'] ?? [];
$provinces = $viewData['provinces'] ?? [];
$tags = $viewData['tags'] ?? [];
$selectedTagSlug = $viewData['selectedTagSlug'] ?? '';
$page = $viewData['page'] ?? 1;
$pageSize = $viewData['pageSize'] ?? 12;
$totalPages = $viewData['totalPages'] ?? 1;
$selectedCategoryId = $viewData['selectedCategoryId'] ?? '';
$selectedProvinceCode = $viewData['selectedProvinceCode'] ?? '';
$keyword = $viewData['keyword'] ?? '';
$totalCount = $viewData['totalCount'] ?? null;

if (!is_array($places)) {
    $places = [];
}

if (!is_array($categories)) {
    $categories = [];
}

if (!is_array($provinces)) {
    $provinces = [];
}

if (!is_array($tags)) {
    $tags = [];
}

if (is_string($selectedTagSlug) === false) {
    $selectedTagSlug = '';
}

if (!is_numeric($page) || (int) $page < 1) {
    $page = 1;
} else {
    $page = (int) $page;
}

if (!is_numeric($pageSize) || (int) $pageSize < 1) {
    $pageSize = 12;
} else {
    $pageSize = (int) $pageSize;
}

if (!is_numeric($totalPages) || (int) $totalPages < 1) {
    $totalPages = 1;
} else {
    $totalPages = (int) $totalPages;
}

if (is_string($selectedCategoryId) === false && is_int($selectedCategoryId) === false) {
    $selectedCategoryId = '';
}

$selectedCategoryId = (string) $selectedCategoryId;

if (is_string($selectedProvinceCode) === false) {
    $selectedProvinceCode = '';
}

if (!is_string($keyword)) {
    $keyword = '';
}

if (!is_numeric($totalCount) || (int) $totalCount < 0) {
    $totalCount = count($places);
} else {
    $totalCount = (int) $totalCount;
}

/*
 * Ảnh minh họa cho dữ liệu seed.
 * Ảnh riêng của địa điểm được ưu tiên trong _place_cards.php.
 */
$categoryVisuals = [
    1 => ['image' => 'assets/images/home/food.webp', 'icon' => 'food', 'subtitle' => 'Món ngon địa phương'],
    2 => ['image' => 'assets/images/home/coffee.webp', 'icon' => 'coffee', 'subtitle' => 'Quán cà phê đáng thử'],
    3 => ['image' => 'assets/images/home/travel.webp', 'icon' => 'travel', 'subtitle' => 'Điểm đến nổi bật'],
    4 => ['image' => 'assets/images/home/fun.webp', 'icon' => 'fun', 'subtitle' => 'Đi chơi cuối tuần'],
    5 => ['image' => 'assets/images/home/nature.webp', 'icon' => 'nature', 'subtitle' => 'Gần gũi thiên nhiên'],
    6 => ['image' => 'assets/images/home/culture.webp', 'icon' => 'culture', 'subtitle' => 'Văn hóa và di sản'],
];

$featuredPlaces = array_slice($places, 0, 12);
$hasActiveFilters = $keyword !== ''
    || $selectedCategoryId !== ''
    || $selectedProvinceCode !== ''
    || $selectedTagSlug !== '';
?>

<section class="hs-hero" aria-labelledby="home-hero-title">
    <div class="hs-hero__shade" aria-hidden="true"></div>

    <div class="hs-container hs-hero__inner">
        <div class="hs-hero__copy">
            <p class="hs-hero__eyebrow">Việt Nam ơi!</p>

            <h1 id="home-hero-title">
                Còn bao điều<br>
                <span>đẹp</span> để khám phá
            </h1>

            <p class="hs-hero__description">
                Khám phá những địa điểm thú vị, quán ngon, điểm check-in đẹp<br class="hs-desktop-break">
                và trải nghiệm đáng nhớ trên khắp Việt Nam.
            </p>

            <form
                class="hs-search-form"
                id="home-search"
                data-search-url="<?php echo escapeHtml(createRouteUrl('api/places/search')); ?>"
                action="index.php"
                method="get"
                role="search"
                aria-label="Tìm kiếm và lọc địa điểm">

                <input type="hidden" name="route" value="home">

                <div class="hs-search-shell">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="10.8" cy="10.8" r="6.8"></circle>
                        <path d="m16 16 4.4 4.4"></path>
                    </svg>

                    <input
                        class="hs-search-shell__input"
                        type="search"
                        name="keyword"
                        value="<?php echo escapeHtml($keyword); ?>"
                        placeholder="Tìm địa điểm, món ăn, quán cà phê, trải nghiệm..."
                        maxlength="100"
                        aria-label="Từ khóa tìm kiếm">

                    <button class="hs-search-shell__button" type="submit">
                        Tìm kiếm
                    </button>
                </div>

                <div class="hs-hero__filters" aria-label="Bộ lọc tìm kiếm">
                    <label class="hs-filter-pill hs-filter-control">
                        <span>Danh mục</span>
                        <select class="hs-filter-select" name="category_id" aria-label="Lọc theo danh mục">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $categoryInformation) : ?>
                                <option
                                    value="<?php echo (int) $categoryInformation['category_id']; ?>"
                                    <?php if ($selectedCategoryId === (string) $categoryInformation['category_id']) { ?>selected<?php } ?>>
                                    <?php echo escapeHtml((string) $categoryInformation['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="hs-filter-pill hs-filter-control">
                        <span>Tỉnh/thành</span>
                        <select class="hs-filter-select" name="province_code" aria-label="Lọc theo tỉnh/thành">
                            <option value="">Tất cả tỉnh/thành</option>
                            <?php foreach ($provinces as $provinceInformation) : ?>
                                <option
                                    value="<?php echo escapeHtml((string) $provinceInformation['province_code']); ?>"
                                    <?php if ($selectedProvinceCode === (string) $provinceInformation['province_code']) { ?>selected<?php } ?>>
                                    <?php echo escapeHtml((string) $provinceInformation['province_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>

                    <label class="hs-filter-pill hs-filter-control">
                        <span>Nhãn</span>
                        <select class="hs-filter-select" name="tag_slug" aria-label="Lọc theo nhãn">
                            <option value="">Tất cả nhãn</option>
                            <?php foreach ($tags as $tagInformation) : ?>
                                <option
                                    value="<?php echo escapeHtml((string) $tagInformation['slug']); ?>"
                                    <?php if ($selectedTagSlug === (string) $tagInformation['slug']) { ?>selected<?php } ?>>
                                    <?php echo escapeHtml((string) $tagInformation['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            </form>
        </div>

        <aside class="hs-hero-place" aria-label="Điểm đến minh họa">
            <img src="assets/images/home/travel.webp" alt="" loading="lazy">

            <div class="hs-hero-place__copy">
                <strong>Vịnh Hạ Long</strong>
                <span>Quảng Ninh</span>
            </div>

        </aside>
    </div>
</section>

<div class="hs-container hs-home-content">
    <section class="hs-section hs-categories" aria-labelledby="popular-categories-heading">
        <div class="hs-section-heading">
            <div class="hs-section-heading__title">
                <span class="hs-section-heading__marker" aria-hidden="true"></span>
                <h2 id="popular-categories-heading">Danh mục phổ biến</h2>
            </div>

            <span class="hs-section-link">
                Xem tất cả danh mục
            </span>
        </div>

        <?php if ($categories === []) : ?>
            <p class="hs-empty-inline">Danh mục đang được cập nhật.</p>
        <?php else : ?>
            <div class="hs-category-grid">
                <?php foreach ($categories as $category) :
                    $categoryId = (int) ($category['category_id'] ?? 0);
                    $categoryName = trim((string) ($category['name'] ?? 'Danh mục'));
                    $visual = $categoryVisuals[$categoryId] ?? $categoryVisuals[3];
                ?>
                    <article class="hs-category-card">
                        <div class="hs-category-card__image">
                            <img
                                src="<?php echo escapeHtml($visual['image']); ?>"
                                alt=""
                                loading="lazy"
                                decoding="async">
                        </div>

                        <div class="hs-category-card__body">
                            <span
                                class="hs-category-card__icon hs-category-card__icon--<?php echo escapeHtml($visual['icon']); ?>"
                                aria-hidden="true">
                                <?php
                                $categorySymbol = [
                                    1 => '♨',
                                    2 => '☕',
                                    3 => '▲',
                                    4 => '★',
                                    5 => '♧',
                                    6 => '⌂',
                                ][$categoryId] ?? '•';

                                echo escapeHtml($categorySymbol);
                                ?>
                            </span>

                            <div class="hs-category-card__text">
                                <strong><?php echo escapeHtml($categoryName); ?></strong>
                                <span><?php echo escapeHtml($visual['subtitle']); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section
        class="hs-section hs-featured"
        id="featured-places"
        aria-labelledby="featured-places-heading">

        <div class="hs-section-heading hs-section-heading--featured">
            <div class="hs-section-heading__title">
                <span class="hs-section-heading__marker" aria-hidden="true"></span>

                <h2 id="featured-places-heading"><?php
                    echo $hasActiveFilters ? 'Kết quả tìm kiếm' : 'Địa điểm nổi bật';
                ?></h2>
            </div>

            <div
                class="hs-featured-tools"
                id="home-featured-tools"
                aria-label="Các cách xem địa điểm"
                <?php echo $hasActiveFilters ? 'hidden' : ''; ?>>

                <span class="hs-sort-pill hs-sort-pill--active">Được yêu thích</span>
                <span class="hs-sort-pill">Mới cập nhật</span>
                <span class="hs-sort-pill">Đánh giá cao</span>
            </div>

            <span
                class="hs-section-link"
                id="home-featured-all-link"
                <?php echo $hasActiveFilters ? 'hidden' : ''; ?>>

                Xem tất cả địa điểm
            </span>
        </div>

        <p
            class="hs-search-feedback"
            id="home-search-feedback"
            role="status"
            <?php echo $hasActiveFilters ? '' : 'hidden'; ?>>

            <span id="home-search-feedback-text">
                <?php if ($hasActiveFilters) : ?>
                    Tìm thấy <strong><?php echo escapeHtml(number_format($totalCount)); ?></strong>
                    địa điểm phù hợp với bộ lọc hiện tại.
                <?php endif; ?>
            </span>

            <a
                href="<?php echo escapeHtml(createRouteUrl('home')); ?>"
                id="home-search-clear"
                <?php echo $hasActiveFilters ? '' : 'hidden'; ?>>
                Xóa tìm kiếm
            </a>
        </p>

        <div id="home-search-results">
            <?php if ($hasActiveFilters && $featuredPlaces === []) : ?>
                <div class="hs-empty-state" role="status">
                    <h3>Không tìm thấy địa điểm phù hợp</h3>
                    <p>Thử một từ khóa khác hoặc rút gọn từ khóa hiện tại.</p>
                </div>
            <?php else : ?>
                <?php
                $places = $featuredPlaces;
                require __DIR__ . '/_place_cards.php';
                ?>
            <?php endif; ?>
        </div>

        <nav
            class="hs-pagination"
            id="home-pagination"
            aria-label="Phân trang địa điểm"
            <?php echo $totalPages <= 1 ? 'hidden' : ''; ?>>
            <?php require __DIR__ . '/_pagination.php'; ?>
        </nav>
    </section>
</div>

<script src="assets/js/search.js?v=4" defer></script>
