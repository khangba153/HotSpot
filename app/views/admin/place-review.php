<?php
/** @var array<string, mixed> $viewData */
$place = $viewData['place'];
$images = $viewData['imageUrls'];
$mainImage = $images[0] ?? 'assets/images/home/coffee.webp';
$csrfToken = (string) ($viewData['csrfToken'] ?? '');
$adminMessage = (string) ($viewData['adminMessage'] ?? '');
?>
<div class="admin-shell">
    <aside class="admin-sidebar"><small>QUẢN LÝ HỆ THỐNG</small><a href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">▦ &nbsp; Tổng quan</a><a class="active" href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">◷ &nbsp; Chờ duyệt địa điểm</a><a href="<?php echo escapeHtml(createRouteUrl('admin/places')); ?>">⌘ &nbsp; Quản lý địa điểm</a><a>☆ &nbsp; Quản lý đánh giá</a><a>▤ &nbsp; Quản lý danh mục</a><a class="admin-back" href="<?php echo escapeHtml(createRouteUrl('home')); ?>">↩ &nbsp; Quay về trang chủ</a></aside>
    <main class="admin-content">
        <div class="admin-heading"><div><small>HOT SPOT / QUẢN TRỊ</small><h1>Xem xét địa điểm</h1><p>Đọc thông tin bài đăng, kiểm tra hình ảnh và quyết định duyệt hoặc từ chối</p></div><span class="admin-pill">● &nbsp; PENDING</span></div>
        <?php if ($adminMessage !== '') { ?><div class="admin-action-note" role="alert"><?php echo escapeHtml($adminMessage); ?></div><?php } ?>
        <div class="admin-review-grid">
            <div>
                <section class="admin-card admin-place-card">
                    <img class="admin-hero-image" src="<?php echo escapeHtml($mainImage); ?>" alt="Ảnh địa điểm">
                    <div class="admin-thumbnails"><?php foreach (array_slice($images, 1) as $image) { ?><img src="<?php echo escapeHtml($image); ?>" alt="Ảnh địa điểm bổ sung"><?php } ?></div>
                    <h2><?php echo escapeHtml((string) $place['title']); ?></h2>
                    <p class="admin-muted"><?php echo escapeHtml((string) $place['category_name']); ?> · <?php echo escapeHtml((string) $place['province_name']); ?></p>
                    <p><?php echo nl2br(escapeHtml((string) $place['description'])); ?></p>
                    <span class="admin-tag">Địa chỉ</span>
                </section>
                <section class="admin-card admin-map"><h3>Vị trí địa điểm</h3><p><?php echo escapeHtml((string) $place['address']); ?><?php if (!empty($place['ward_name'])) { ?>, <?php echo escapeHtml((string) $place['ward_name']); ?><?php } ?>, <?php echo escapeHtml((string) $place['province_name']); ?></p>
                <?php if ($place['latitude'] !== null && $place['longitude'] !== null) { ?><a href="https://www.openstreetmap.org/?mlat=<?php echo rawurlencode((string) $place['latitude']); ?>&amp;mlon=<?php echo rawurlencode((string) $place['longitude']); ?>" target="_blank" rel="noopener noreferrer">Mở vị trí trên OpenStreetMap</a><?php } else { ?><span class="admin-muted">Người đăng chưa cung cấp tọa độ.</span><?php } ?></section>
            </div>
            <aside class="admin-review-side"><section class="admin-card"><h3>Thông tin bài đăng</h3>
                <dl><dt>Người đăng</dt><dd><?php echo escapeHtml((string) $place['owner_name']); ?></dd><dt>Email</dt><dd><?php echo escapeHtml((string) $place['owner_email']); ?></dd><dt>Ngày gửi</dt><dd><?php echo escapeHtml(date('d/m/Y · H:i', strtotime((string) $place['created_at']))); ?></dd><dt>Danh mục</dt><dd><?php echo escapeHtml((string) $place['category_name']); ?></dd><dt>Tỉnh/thành</dt><dd><?php echo escapeHtml((string) $place['province_name']); ?></dd><dt>Hình ảnh</dt><dd><?php echo count($images); ?> ảnh đã gửi</dd><dt>Trạng thái</dt><dd><span class="admin-pill">● Chờ duyệt</span></dd></dl>
                <div class="admin-actions">
                    <form method="post" action="<?php echo escapeHtml(createRouteUrl('admin/places/status')); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo escapeHtml($csrfToken); ?>">
                        <input type="hidden" name="place_id" value="<?php echo (int) $place['place_id']; ?>">
                        <input type="hidden" name="target_status" value="APPROVED">
                        <button type="submit">✓ &nbsp; Duyệt bài</button>
                    </form>
                    <button type="submit" form="reject-form" class="admin-reject-shortcut">× &nbsp; Từ chối</button>
                </div>
            </section><section class="admin-card admin-reject"><h3>Từ chối địa điểm</h3><p>Nhập lý do cụ thể để thành viên biết cần chỉnh sửa những gì.</p>
                <form id="reject-form" method="post" action="<?php echo escapeHtml(createRouteUrl('admin/places/status')); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo escapeHtml($csrfToken); ?>">
                    <input type="hidden" name="place_id" value="<?php echo (int) $place['place_id']; ?>">
                    <input type="hidden" name="target_status" value="REJECTED">
                    <label for="reject-reason">Lý do từ chối *</label><textarea id="reject-reason" name="rejection_reason" rows="3" required placeholder="Ví dụ: Ảnh chưa rõ nét; mô tả cần chi tiết hơn..."></textarea>
                    <button type="submit">Xác nhận từ chối</button>
                </form>
            </section></aside>
        </div>
    </main>
</div>
