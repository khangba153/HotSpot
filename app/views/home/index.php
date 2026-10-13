<?php
/** @var array<string, mixed> $viewData */
$places = $viewData['places'] ?? [];
$categories = $viewData['categories'] ?? [];
$keyword = $viewData['keyword'] ?? '';
$totalCount = $viewData['totalCount'] ?? null;

if (!is_array($places)) {
    $places = [];
}
if (!is_array($categories)) {
    $categories = [];
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
 * Ảnh chỉ dùng để trình bày dữ liệu seed còn trỏ tới demo-placeholder.svg.
 * Khi một địa điểm có ảnh riêng, ảnh đó luôn được ưu tiên trong _place_cards.php.
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

            <form class="hs-search-shell" id="home-search" action="index.php" method="get" role="search" aria-label="Tìm kiếm địa điểm">
                <input type="hidden" name="route" value="home">
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
                    aria-label="Từ khóa tìm kiếm"
                >
                <button class="hs-search-shell__button" type="submit">Tìm kiếm</button>
            </form>

            <div class="hs-hero__filters" aria-label="Bộ lọc tìm kiếm">
                <span class="hs-filter-pill">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="6" height="6" rx="1"></rect><rect x="14.5" y="3.5" width="6" height="6" rx="1"></rect><rect x="3.5" y="14.5" width="6" height="6" rx="1"></rect><rect x="14.5" y="14.5" width="6" height="6" rx="1"></rect></svg>
                    Tất cả danh mục
                    <span class="hs-filter-pill__chevron">⌄</span>
                </span>
                <span class="hs-filter-pill">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 10.2c0 5-7 10.3-7 10.3S5 15.2 5 10.2a7 7 0 1 1 14 0Z"></path><circle cx="12" cy="10" r="2.2"></circle></svg>
                    Tỉnh/Thành
                    <span class="hs-filter-pill__chevron">⌄</span>
                </span>
                <span class="hs-filter-pill">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8.5 8.5L12 20l-8.5-8.5Z"></path><circle cx="9" cy="9" r="1"></circle></svg>
                    Nhãn phổ biến
                    <span class="hs-filter-pill__chevron">⌄</span>
                </span>
            </div>
        </div>

        <aside class="hs-hero-place" aria-label="Điểm đến minh họa">
            <img src="assets/images/home/travel.webp" alt="" loading="lazy">
            <div class="hs-hero-place__copy">
                <strong>Vịnh Hạ Long</strong>
                <span><i aria-hidden="true">●</i> Quảng Ninh</span>
            </div>
            <span class="hs-hero-place__arrow" aria-hidden="true">›</span>
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
            <span class="hs-section-link">Xem tất cả danh mục <span aria-hidden="true">→</span></span>
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
                                decoding="async"
                            >
                        </div>
                        <div class="hs-category-card__body">
                            <span class="hs-category-card__icon hs-category-card__icon--<?php echo escapeHtml($visual['icon']); ?>" aria-hidden="true">
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

    <section class="hs-section hs-featured" id="featured-places" aria-labelledby="featured-places-heading">
        <div class="hs-section-heading hs-section-heading--featured">
            <div class="hs-section-heading__title">
                <span class="hs-section-heading__marker" aria-hidden="true"></span>
                <h2 id="featured-places-heading"><?php echo $keyword !== '' ? 'Kết quả tìm kiếm' : 'Địa điểm nổi bật'; ?></h2>
            </div>
            <?php if ($keyword === '') : ?>
                <div class="hs-featured-tools" aria-label="Các cách xem địa điểm">
                    <span class="hs-sort-pill hs-sort-pill--active">Được yêu thích</span>
                    <span class="hs-sort-pill">Mới cập nhật</span>
                    <span class="hs-sort-pill">Đánh giá cao</span>
                </div>
                <span class="hs-section-link">Xem tất cả địa điểm <span aria-hidden="true">→</span></span>
            <?php endif; ?>
        </div>

        <?php if ($keyword !== '') : ?>
            <p class="hs-search-feedback" role="status">
                Tìm thấy <strong><?php echo escapeHtml(number_format($totalCount)); ?></strong>
                địa điểm phù hợp với từ khóa
                <strong>“<?php echo escapeHtml($keyword); ?>”</strong>.
                <?php if (count($featuredPlaces) < $totalCount) : ?>
                    Đang hiển thị <?php echo escapeHtml((string) count($featuredPlaces)); ?> kết quả đầu tiên.
                <?php endif; ?>
                <a href="<?php echo escapeHtml(createRouteUrl('home')); ?>">Xóa tìm kiếm</a>
            </p>
        <?php endif; ?>

        <?php if ($keyword !== '' && $featuredPlaces === []) : ?>
            <div class="hs-empty-state" role="status">
                <span class="hs-empty-state__icon" aria-hidden="true">⌖</span>
                <h3>Không tìm thấy địa điểm phù hợp</h3>
                <p>Thử một từ khóa khác hoặc rút gọn từ khóa hiện tại.</p>
            </div>
        <?php else : ?>
            <?php
            $places = $featuredPlaces;
            require __DIR__ . '/_place_cards.php';
            ?>
        <?php endif; ?>

        <?php if ($keyword === '') : ?>
            <nav class="hs-pagination" aria-label="Phân trang địa điểm">
                <span class="hs-pagination__arrow" aria-hidden="true">‹</span>
                <span class="hs-pagination__number hs-pagination__number--active" aria-current="page">1</span>
                <span class="hs-pagination__number">2</span>
                <span class="hs-pagination__number">3</span>
                <span class="hs-pagination__number">4</span>
                <span class="hs-pagination__number">5</span>
                <span class="hs-pagination__arrow" aria-hidden="true">›</span>
            </nav>
        <?php endif; ?>
    </section>
</div>
