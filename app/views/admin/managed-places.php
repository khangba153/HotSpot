<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller. */
$managedPlaces = $viewData['managedPlaces'] ?? [];
$csrfToken = (string) ($viewData['csrfToken'] ?? '');
$approvedCount = 0;
$hiddenCount = 0;
$categories = [];
$provinces = [];
foreach ($managedPlaces as $place) {
    if ($place['status_code'] === 'APPROVED') {
        $approvedCount++;
    } else {
        $hiddenCount++;
    }
    $categories[(string) $place['category_name']] = (string) $place['category_name'];
    $provinces[(string) $place['province_name']] = (string) $place['province_name'];
}
sort($categories);
sort($provinces);
?>
<div class="admin-shell admin-list-shell">
    <section class="admin-content">
        <div class="container-fluid px-0"><div class="row"><div class="col-12">
        <div class="admin-heading"><div><small>HOT SPOT / QUẢN TRỊ</small><h1>Quản lý địa điểm</h1><p>Theo dõi các bài đã duyệt, tạm ẩn hoặc khôi phục địa điểm công khai.</p></div><a class="category-primary" href="<?php echo escapeHtml(createRouteUrl('places/create')); ?>">Đăng địa điểm</a></div>
        <?php if ($adminMessage !== '') { ?><div class="admin-flash admin-flash--<?php echo escapeHtml($adminMessageType); ?>" role="status"><?php echo escapeHtml($adminMessage); ?></div><?php } ?>
        <div class="admin-management-tabs" aria-label="Lọc trạng thái">
            <button type="button" class="selected" data-status-filter=""><span>Tất cả địa điểm</span><strong><?php echo count($managedPlaces); ?></strong></button>
            <button type="button" data-status-filter="APPROVED"><span>Đã duyệt</span><strong><?php echo $approvedCount; ?></strong></button>
            <button type="button" data-status-filter="HIDDEN"><span>Đã ẩn</span><strong><?php echo $hiddenCount; ?></strong></button>
        </div>
        <div class="admin-toolbar">
            <label class="admin-search"><input id="managed-search" type="search" placeholder="Tìm theo tên địa điểm..." aria-label="Tìm theo tên địa điểm"></label>
            <select id="managed-category" aria-label="Lọc danh mục"><option value="">Tất cả danh mục</option><?php foreach ($categories as $category) { ?><option value="<?php echo escapeHtml($category); ?>"><?php echo escapeHtml($category); ?></option><?php } ?></select>
            <select id="managed-province" aria-label="Lọc tỉnh thành"><option value="">Tất cả tỉnh/thành</option><?php foreach ($provinces as $province) { ?><option value="<?php echo escapeHtml($province); ?>"><?php echo escapeHtml($province); ?></option><?php } ?></select>
        </div>
        <div class="admin-table-card"><div class="table-responsive"><table class="admin-table"><thead><tr><th>#</th><th>ĐỊA ĐIỂM</th><th>DANH MỤC</th><th>TỈNH/THÀNH</th><th>TRẠNG THÁI</th><th>NGÀY DUYỆT</th><th>THAO TÁC</th></tr></thead><tbody>
        <?php foreach ($managedPlaces as $index => $place) {
            $imagePath = (string) ($place['image_path'] ?? '');
            $validLocalImage = preg_match('#^(assets|uploads)/[A-Za-z0-9._/-]+$#', $imagePath) === 1 && strpos($imagePath, '..') === false && $imagePath !== 'assets/images/demo-placeholder.svg';
            $validRemoteImage = preg_match('#^https?://#i', $imagePath) === 1 && filter_var($imagePath, FILTER_VALIDATE_URL) !== false;
            if (!$validLocalImage && !$validRemoteImage) {
                $imagePath = $categoryImages[(int) $place['category_id']] ?? 'assets/images/home/travel.webp';
            }
            $isApproved = $place['status_code'] === 'APPROVED';
            $approvalDate = empty($place['approved_at']) ? false : strtotime((string) $place['approved_at']);
        ?>
        <tr class="managed-place-row" data-title="<?php echo escapeHtml((string) $place['title']); ?>" data-category="<?php echo escapeHtml((string) $place['category_name']); ?>" data-province="<?php echo escapeHtml((string) $place['province_name']); ?>" data-status="<?php echo escapeHtml((string) $place['status_code']); ?>">
            <td><?php echo $index + 1; ?></td><td><div class="admin-place-name"><img src="<?php echo escapeHtml($imagePath); ?>" alt=""><strong><?php echo escapeHtml((string) $place['title']); ?></strong></div></td>
            <td><?php echo escapeHtml((string) $place['category_name']); ?></td><td><?php echo escapeHtml((string) $place['province_name']); ?></td>
            <td><span class="admin-status <?php echo $isApproved ? 'admin-status-approved' : 'admin-status-hidden'; ?>"><?php echo $isApproved ? 'Đã duyệt' : 'Đã ẩn'; ?></span></td>
            <td><?php echo $approvalDate === false ? '—' : escapeHtml(date('d/m/Y', $approvalDate)); ?></td>
            <td><form method="post" action="<?php echo escapeHtml(createRouteUrl('admin/places/status')); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo escapeHtml($csrfToken); ?>"><input type="hidden" name="place_id" value="<?php echo (int) $place['place_id']; ?>">
                <input type="hidden" name="moderation_action" value="<?php echo $isApproved ? 'hide' : 'restore'; ?>"><input type="hidden" name="target_status" value="<?php echo $isApproved ? 'HIDDEN' : 'APPROVED'; ?>">
                <button class="admin-manage-action <?php echo $isApproved ? 'admin-hide-action' : 'admin-restore-action'; ?>" type="submit"><?php echo $isApproved ? 'Ẩn địa điểm' : 'Khôi phục'; ?></button>
            </form></td>
        </tr><?php } ?>
        <tr id="managed-empty"<?php if (count($managedPlaces) > 0) { ?> hidden<?php } ?>><td colspan="7" class="admin-empty">Không có địa điểm phù hợp.</td></tr>
        </tbody></table></div></div>
        <div class="admin-pagination" id="managed-pagination" aria-label="Phân trang"></div>
        <p class="admin-table-summary" id="managed-summary"></p>
        <p class="admin-management-note">Chỉ chuyển trạng thái APPROVED ↔ HIDDEN. Địa điểm ẩn không hiển thị công khai.</p>
        </div></div></div>
    </section>
</div>
<script src="assets/js/admin-managed-places.js?v=20261108-pagination" defer></script>
