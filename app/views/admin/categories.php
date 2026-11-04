<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller. */
$categories = $viewData['categories'] ?? [];
$activeCount = 0;
foreach ($categories as $category) { if ((int) $category['is_active'] === 1) { $activeCount++; } }
$token = (string) ($viewData['csrfToken'] ?? '');
$message = (string) ($viewData['message'] ?? '');
$messageType = ($viewData['messageType'] ?? '') === 'danger' ? 'danger' : 'success';
?>
<div class="admin-shell admin-list-shell">
    <aside class="admin-sidebar">
        <small>QUẢN LÝ HỆ THỐNG</small>
        <a href="<?php echo escapeHtml(createRouteUrl('admin')); ?>">Tổng quan</a>
        <a href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">Chờ duyệt địa điểm</a>
        <a href="<?php echo escapeHtml(createRouteUrl('admin/places') . '&view=managed'); ?>">Quản lý địa điểm</a>
        <a href="<?php echo escapeHtml(createRouteUrl('admin/reviews')); ?>">Quản lý đánh giá</a>
        <a class="active" href="<?php echo escapeHtml(createRouteUrl('admin/categories')); ?>">Quản lý danh mục</a>
        <a class="admin-back" href="<?php echo escapeHtml(createRouteUrl('home')); ?>">Quay về trang chủ</a>
    </aside>
    <main class="admin-content admin-categories-page">
        <div class="admin-heading"><div><small>HOT SPOT / QUẢN TRỊ</small><h1>Quản lý danh mục</h1><p>Thêm, sửa và ngừng sử dụng danh mục địa điểm trong hệ thống</p></div><button class="category-primary" type="button" data-bs-toggle="modal" data-bs-target="#category-modal" data-mode="create">Thêm danh mục</button></div>
        <?php if ($message !== '') { ?><div class="admin-flash admin-flash--<?php echo escapeHtml($messageType); ?>" role="status"><?php echo escapeHtml($message); ?></div><?php } ?>
        <div class="admin-review-stats"><div><span>Tổng danh mục</span><strong><?php echo count($categories); ?></strong></div><div><span>Đang hoạt động</span><strong><?php echo $activeCount; ?></strong></div><div><span>Ngừng sử dụng</span><strong><?php echo count($categories) - $activeCount; ?></strong></div></div>
        <div class="admin-toolbar"><label class="admin-search"><input id="category-search" type="search" placeholder="Tìm kiếm tên danh mục..." aria-label="Tìm tên danh mục"></label><select id="category-filter" aria-label="Lọc trạng thái"><option value="">Tất cả trạng thái</option><option value="1">Đang hoạt động</option><option value="0">Ngừng sử dụng</option></select></div>
        <div class="admin-table-card"><div class="table-responsive"><table class="admin-table category-table"><thead><tr><th>#</th><th>TÊN DANH MỤC</th><th>MÔ TẢ</th><th>TRẠNG THÁI</th><th>SỐ ĐỊA ĐIỂM</th><th>THAO TÁC</th></tr></thead><tbody>
        <?php foreach ($categories as $index => $category) { $active = (int) $category['is_active'] === 1; ?>
        <tr class="category-row" data-name="<?php echo escapeHtml((string) $category['name']); ?>" data-active="<?php echo $active ? '1' : '0'; ?>">
            <td><?php echo $index + 1; ?></td><td><strong><?php echo escapeHtml((string) $category['name']); ?></strong></td><td class="category-description"><?php echo escapeHtml((string) ($category['description'] ?? '')); ?></td><td><span class="admin-status <?php echo $active ? 'admin-status-approved' : 'admin-status-hidden'; ?>"><?php echo $active ? 'Đang hoạt động' : 'Ngừng sử dụng'; ?></span></td><td><?php echo (int) $category['place_count']; ?></td>
            <td><div class="category-actions"><button type="button" class="category-edit" data-bs-toggle="modal" data-bs-target="#category-modal" data-mode="edit" data-id="<?php echo (int) $category['category_id']; ?>" data-name="<?php echo escapeHtml((string) $category['name']); ?>" data-description="<?php echo escapeHtml((string) ($category['description'] ?? '')); ?>">Sửa</button><form method="post" action="<?php echo escapeHtml(createRouteUrl('admin/categories/status')); ?>"><input type="hidden" name="csrf_token" value="<?php echo escapeHtml($token); ?>"><input type="hidden" name="category_id" value="<?php echo (int) $category['category_id']; ?>"><input type="hidden" name="is_active" value="<?php echo $active ? '0' : '1'; ?>"><button class="admin-manage-action <?php echo $active ? 'admin-hide-action' : 'admin-restore-action'; ?>" type="submit"><?php echo $active ? 'Ngừng' : 'Kích hoạt'; ?></button></form></div></td>
        </tr><?php } ?>
        <tr id="category-empty"<?php if ($categories !== []) { ?> hidden<?php } ?>><td colspan="6" class="admin-empty">Không có danh mục phù hợp.</td></tr>
        </tbody></table></div></div>
        <p class="admin-review-note">Danh mục ngừng sử dụng không xuất hiện trong form đăng bài mới. Các địa điểm đã duyệt vẫn hiển thị công khai.</p>
    </main>
</div>
<div class="modal fade" id="category-modal" tabindex="-1" aria-labelledby="category-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content category-modal-content">
        <form id="category-form" method="post" action="<?php echo escapeHtml(createRouteUrl('admin/categories/store')); ?>" data-create-url="<?php echo escapeHtml(createRouteUrl('admin/categories/store')); ?>" data-update-url="<?php echo escapeHtml(createRouteUrl('admin/categories/update')); ?>">
            <div class="modal-body"><h2 id="category-modal-title">Thêm danh mục mới</h2><p>Danh mục sẽ xuất hiện trong bộ lọc và form đăng địa điểm khi ở trạng thái hoạt động.</p><input type="hidden" name="csrf_token" value="<?php echo escapeHtml($token); ?>"><input type="hidden" name="category_id" id="category-id" value="">
                <label for="category-name">Tên danh mục <span>*</span></label><input class="form-control" id="category-name" name="name" maxlength="100" required>
                <label for="category-description">Mô tả (tùy chọn)</label><textarea class="form-control" id="category-description" name="description" maxlength="300" rows="3"></textarea>
            </div><div class="modal-footer"><button class="category-edit" type="button" data-bs-dismiss="modal">Hủy</button><button class="category-primary" type="submit" id="category-submit">Thêm danh mục</button></div>
        </form>
    </div></div>
</div>
<script src="assets/js/admin-categories.js"></script>
