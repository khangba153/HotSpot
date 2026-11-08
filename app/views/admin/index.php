<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller. */
$counts = $viewData['counts'];
$pendingPlaces = $viewData['pendingPlaces'];
$totalPlaces = (int) $counts['total_places'];
$pendingCount = (int) $counts['pending_places'];
$approvedCount = (int) $counts['approved_places'];
$reviewCount = (int) $counts['total_reviews'];
$hiddenReviewCount = (int) $counts['hidden_reviews'];
$categoryCount = (int) $counts['total_categories'];
?>
<div class="admin-shell admin-list-shell admin-dashboard">
    <section class="admin-content">
        <div class="admin-heading"><div><small>HOT SPOT / QUẢN TRỊ</small><h1>Tổng quan quản trị</h1><p>Theo dõi hoạt động cộng đồng và những việc cần xử lý</p></div></div>
        <div class="dashboard-stats">
            <div><span>Tổng địa điểm</span><strong><?php echo $totalPlaces; ?></strong></div>
            <div><span>Chờ duyệt</span><strong><?php echo $pendingCount; ?></strong></div>
            <div><span>Đã duyệt</span><strong><?php echo $approvedCount; ?></strong></div>
            <div><span>Tổng đánh giá</span><strong><?php echo $reviewCount; ?></strong></div>
        </div>
        <h2 class="dashboard-section-title">Lối tắt quản trị</h2>
        <nav class="dashboard-links" aria-label="Lối tắt quản trị">
            <a href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>"><strong>Duyệt địa điểm</strong><span><?php echo $pendingCount; ?> bài đang chờ</span></a>
            <a href="<?php echo escapeHtml(createRouteUrl('admin/places') . '&view=managed'); ?>"><strong>Quản lý địa điểm</strong><span>Ẩn hoặc khôi phục</span></a>
            <a href="<?php echo escapeHtml(createRouteUrl('admin/reviews')); ?>"><strong>Quản lý đánh giá</strong><span><?php echo $reviewCount; ?> đánh giá</span></a>
            <a href="<?php echo escapeHtml(createRouteUrl('admin/categories')); ?>"><strong>Quản lý danh mục</strong><span><?php echo $categoryCount; ?> danh mục</span></a>
        </nav>
        <div class="dashboard-details">
            <section><div class="dashboard-section-heading"><h2 class="dashboard-section-title">Địa điểm chờ duyệt gần đây</h2><a href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">Xem tất cả</a></div>
                <div class="admin-table-card"><div class="table-responsive"><table class="admin-table dashboard-table"><thead><tr><th>ĐỊA ĐIỂM</th><th>NGƯỜI ĐĂNG</th><th>DANH MỤC</th><th>TỈNH/THÀNH</th><th>THAO TÁC</th></tr></thead><tbody>
                <?php if ($pendingPlaces === []) { ?><tr><td colspan="5" class="admin-empty">Hiện chưa có địa điểm chờ duyệt.</td></tr><?php } ?>
                <?php foreach ($pendingPlaces as $place) { ?><tr><td><?php echo escapeHtml((string) $place['title']); ?></td><td><?php echo escapeHtml((string) $place['owner_name']); ?></td><td><?php echo escapeHtml((string) $place['category_name']); ?></td><td><?php echo escapeHtml((string) $place['province_name']); ?></td><td><a class="admin-review-link" href="<?php echo escapeHtml(createRouteUrl('admin/places/review') . '&id=' . (int) $place['place_id']); ?>">Xem xét</a></td></tr><?php } ?>
                </tbody></table></div></div>
            </section>
            <section><h2 class="dashboard-section-title">Hoạt động tổng quan</h2><div class="dashboard-overview"><h3>Cần xử lý</h3><p><strong>Địa điểm</strong><span><?php echo $pendingCount; ?> bài đang chờ duyệt</span></p><p><strong>Đánh giá</strong><span><?php echo $hiddenReviewCount; ?> đánh giá đã ẩn</span></p><p><strong>Danh mục</strong><span><?php echo $categoryCount; ?> danh mục đang quản lý</span></p></div></section>
        </div>
    </section>
</div>
