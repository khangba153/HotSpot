<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller. */
$reviews = $viewData['reviews'] ?? [];
$visibleCount = 0;
$hiddenCount = 0;
$places = [];
foreach ($reviews as $review) {
    if ($review['status_code'] === 'VISIBLE') { $visibleCount++; }
    if ($review['status_code'] === 'HIDDEN') { $hiddenCount++; }
    $places[(int) $review['place_id']] = (string) $review['title'];
}
$message = (string) ($viewData['message'] ?? '');
$messageType = ($viewData['messageType'] ?? '') === 'danger' ? 'danger' : 'success';
?>
<div class="admin-shell admin-list-shell">
    <aside class="admin-sidebar">
        <small>QUẢN LÝ HỆ THỐNG</small>
        <a href="<?php echo escapeHtml(createRouteUrl('admin')); ?>">Tổng quan</a>
        <a href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">Chờ duyệt địa điểm</a>
        <a href="<?php echo escapeHtml(createRouteUrl('admin/places') . '&view=managed'); ?>">Quản lý địa điểm</a>
        <a class="active" href="<?php echo escapeHtml(createRouteUrl('admin/reviews')); ?>">Quản lý đánh giá</a>
        <a href="<?php echo escapeHtml(createRouteUrl('admin/categories')); ?>">Quản lý danh mục</a>
        <a class="admin-back" href="<?php echo escapeHtml(createRouteUrl('home')); ?>">Quay về trang chủ</a>
    </aside>
    <main class="admin-content admin-reviews-page">
        <div class="admin-heading"><div><small>HOT SPOT / QUẢN TRỊ</small><h1>Quản lý đánh giá</h1><p>Theo dõi và kiểm duyệt các đánh giá mà cộng đồng gửi lên</p></div></div>
        <?php if ($message !== '') { ?><div class="admin-flash admin-flash--<?php echo escapeHtml($messageType); ?>" role="status"><?php echo escapeHtml($message); ?></div><?php } ?>
        <div class="admin-review-stats">
            <div><span>Tổng đánh giá</span><strong><?php echo count($reviews); ?></strong></div>
            <div><span>Đang hiển thị</span><strong><?php echo $visibleCount; ?></strong></div>
            <div><span>Đã ẩn</span><strong><?php echo $hiddenCount; ?></strong></div>
        </div>
        <div class="admin-toolbar">
            <label class="admin-search"><span aria-hidden="true">⌕</span><input id="review-search" type="search" aria-label="Tìm người đánh giá hoặc địa điểm" placeholder="Tìm người đánh giá hoặc địa điểm..."></label>
            <select id="review-status" aria-label="Lọc trạng thái"><option value="">Tất cả trạng thái</option><option value="VISIBLE">Hiển thị</option><option value="HIDDEN">Đã ẩn</option></select>
            <select id="review-place" aria-label="Lọc địa điểm"><option value="">Tất cả địa điểm</option><?php foreach ($places as $placeId => $title) { ?><option value="<?php echo $placeId; ?>"><?php echo escapeHtml($title); ?></option><?php } ?></select>
        </div>
        <div class="admin-table-card"><div class="table-responsive"><table class="admin-table admin-reviews-table"><thead><tr><th>NGƯỜI ĐÁNH GIÁ</th><th>ĐỊA ĐIỂM</th><th>SỐ SAO</th><th>NỘI DUNG</th><th>TRẠNG THÁI</th><th>NGÀY ĐĂNG</th><th>THAO TÁC</th></tr></thead><tbody>
        <?php foreach ($reviews as $review) { $visible = $review['status_code'] === 'VISIBLE'; $date = strtotime((string) $review['created_at']); ?>
            <tr class="admin-review-row" data-author="<?php echo escapeHtml((string) $review['full_name']); ?>" data-title="<?php echo escapeHtml((string) $review['title']); ?>" data-place="<?php echo (int) $review['place_id']; ?>" data-status="<?php echo escapeHtml((string) $review['status_code']); ?>">
                <td><div class="admin-review-author"><img src="assets/images/default-avatar.svg" alt=""><strong><?php echo escapeHtml((string) $review['full_name']); ?></strong></div></td>
                <td><?php echo escapeHtml((string) $review['title']); ?></td>
                <td><span class="admin-review-stars" aria-label="<?php echo (int) $review['rating']; ?> trên 5 sao"><?php for ($star = 1; $star <= 5; $star++) { echo $star <= (int) $review['rating'] ? '★' : '☆'; } ?></span></td>
                <td class="admin-review-content"><?php echo escapeHtml((string) $review['content']); ?></td>
                <td><span class="admin-status <?php echo $visible ? 'admin-status-approved' : 'admin-status-hidden'; ?>">● <?php echo $visible ? 'Hiển thị' : 'Đã ẩn'; ?></span></td>
                <td><?php echo $date === false ? '—' : escapeHtml(date('d/m/Y', $date)); ?></td>
                <td><form method="post" action="<?php echo escapeHtml(createRouteUrl('admin/reviews/status')); ?>"><input type="hidden" name="csrf_token" value="<?php echo escapeHtml((string) ($viewData['csrfToken'] ?? '')); ?>"><input type="hidden" name="review_id" value="<?php echo (int) $review['review_id']; ?>"><input type="hidden" name="target_status" value="<?php echo $visible ? 'HIDDEN' : 'VISIBLE'; ?>"><button type="submit" class="admin-manage-action <?php echo $visible ? 'admin-hide-action' : 'admin-restore-action'; ?>"><?php echo $visible ? 'Ẩn' : 'Khôi phục'; ?></button></form></td>
            </tr>
        <?php } ?>
            <tr id="review-empty"<?php if (count($reviews) > 0) { ?> hidden<?php } ?>><td colspan="7" class="admin-empty">Không có đánh giá phù hợp.</td></tr>
        </tbody></table></div></div>
        <div class="admin-pagination" id="review-pagination" aria-label="Phân trang"></div>
        <p class="admin-table-summary" id="review-summary"></p>
        <p class="admin-review-note">Đánh giá bị ẩn sẽ không hiển thị công khai. Nếu thành viên sửa nội dung, trạng thái ẩn vẫn được giữ nguyên.</p>
    </main>
</div>
<script src="assets/js/admin-reviews.js"></script>
