<?php
/** @var array<string, mixed> $viewData */
$places = $viewData['pendingPlaces'] ?? [];
$adminMessage = (string) ($viewData['adminMessage'] ?? '');
$adminMessageType = ($viewData['adminMessageType'] ?? 'success') === 'danger' ? 'danger' : 'success';
$categoryImages = [1 => 'assets/images/home/food.webp', 2 => 'assets/images/home/coffee.webp', 3 => 'assets/images/home/travel.webp', 4 => 'assets/images/home/fun.webp', 5 => 'assets/images/home/nature.webp', 6 => 'assets/images/home/culture.webp'];
?>
<div class="admin-shell admin-list-shell">
    <aside class="admin-sidebar"><small>QUẢN LÝ HỆ THỐNG</small><a href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">▦ &nbsp; Tổng quan</a><a class="active" href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">◷ &nbsp; Chờ duyệt địa điểm</a><a href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">⌘ &nbsp; Quản lý địa điểm</a><a>☆ &nbsp; Quản lý đánh giá</a><a>▤ &nbsp; Quản lý danh mục</a><a class="admin-back" href="<?php echo escapeHtml(createRouteUrl('home')); ?>">↩ &nbsp; Quay về trang chủ</a></aside>
    <main class="admin-content">
        <div class="admin-heading"><div><small>HOT SPOT / QUẢN TRỊ</small><h1>Địa điểm chờ duyệt</h1><p>Xem các địa điểm thành viên gửi lên và xử lý từng bài theo quy định.</p></div><span class="admin-pill">● &nbsp; <?php echo count($places); ?> bài cần xem xét</span></div>
        <?php if ($adminMessage !== '') { ?><div class="admin-flash admin-flash--<?php echo escapeHtml($adminMessageType); ?>" role="status"><?php echo escapeHtml($adminMessage); ?></div><?php } ?>
        <div class="admin-notice">ⓘ &nbsp; Chỉ Admin được duyệt hoặc từ chối địa điểm. Khi từ chối phải nhập lý do để thành viên chỉnh sửa.</div>
        <div class="admin-toolbar"><label class="admin-search">⌕ <input id="admin-place-search" type="search" placeholder="Tìm theo tên địa điểm, người đăng..."></label><select aria-label="Lọc danh mục"><option>Tất cả danh mục</option></select><select aria-label="Lọc tỉnh thành"><option>Tất cả tỉnh/thành</option></select><button type="button">Bộ lọc⌄</button></div>
        <div class="admin-table-card"><div class="table-responsive"><table class="admin-table"><thead><tr><th>#</th><th>ĐỊA ĐIỂM</th><th>NGƯỜI ĐĂNG</th><th>DANH MỤC</th><th>TỈNH/THÀNH</th><th>NGÀY GỬI</th><th>TRẠNG THÁI</th><th>THAO TÁC</th></tr></thead><tbody>
        <?php if ($places === []) { ?><tr><td colspan="8" class="admin-empty">Hiện chưa có địa điểm nào chờ duyệt.</td></tr><?php } else { foreach ($places as $index => $place) { $imagePath = (string) ($place['image_path'] ?? ''); $isPlaceholder = $imagePath === 'assets/images/demo-placeholder.svg'; $validImage = !$isPlaceholder && preg_match('#^(assets|uploads)/[A-Za-z0-9._/-]+$#', $imagePath) === 1 && strpos($imagePath, '..') === false; if (!$validImage && !$isPlaceholder && filter_var($imagePath, FILTER_VALIDATE_URL) !== false && preg_match('#^https?://#i', $imagePath) === 1) { $validImage = true; } if (!$validImage) { $imagePath = $categoryImages[(int) ($place['category_id'] ?? 0)] ?? 'assets/images/home/travel.webp'; } $date = strtotime((string) $place['created_at']); ?>
        <tr class="admin-place-row"><td><?php echo str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT); ?></td><td><div class="admin-place-name"><img src="<?php echo escapeHtml($imagePath); ?>" alt=""><span><strong><?php echo escapeHtml((string) $place['title']); ?></strong><small>Địa điểm mới gửi</small></span></div></td><td><?php echo escapeHtml((string) $place['owner_name']); ?></td><td><?php echo escapeHtml((string) $place['category_name']); ?></td><td><?php echo escapeHtml((string) $place['province_name']); ?></td><td><?php echo $date === false ? '—' : escapeHtml(date('d/m/Y', $date)); ?></td><td><span class="admin-status">● &nbsp; Chờ duyệt</span></td><td><a class="admin-review-link" href="<?php echo escapeHtml(createRouteUrl('admin/places/review') . '&id=' . (int) $place['place_id']); ?>">Xem xét →</a></td></tr><?php } } ?>
        </tbody></table></div></div>
        <div class="admin-pagination"><button disabled>‹</button><button class="selected">1</button><button disabled>2</button><button disabled>3</button><button disabled>4</button><button disabled>›</button></div><p class="admin-table-summary">Hiển thị <?php echo count($places); ?> trong tổng số <?php echo count($places); ?> địa điểm đang chờ duyệt</p>
    </main>
</div>
<script>
document.getElementById('admin-place-search').addEventListener('input', function () {
    var query = this.value.toLocaleLowerCase();
    document.querySelectorAll('.admin-place-row').forEach(function (row) {
        row.hidden = row.textContent.toLocaleLowerCase().indexOf(query) === -1;
    });
});
</script>
