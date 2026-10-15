<?php
/** @var array<string, mixed> $viewData */
$places = $viewData['pendingPlaces'] ?? [];
?>
<section class="container py-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <p class="text-uppercase small fw-semibold text-danger mb-1">Hot Spot / Quản trị</p>
            <h1 class="h2 fw-bold mb-2">Địa điểm chờ duyệt</h1>
            <p class="text-secondary mb-0">Xem các địa điểm thành viên gửi lên và xử lý từng bài theo quy định.</p>
        </div>
        <span class="badge rounded-pill text-bg-warning px-3 py-2"><?php echo count($places); ?> bài cần xem xét</span>
    </div>

    <div class="alert alert-warning" role="note">
        Chỉ Admin được duyệt hoặc từ chối địa điểm. Khi từ chối phải nhập lý do để thành viên chỉnh sửa.
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Địa điểm</th>
                        <th scope="col">Người đăng</th>
                        <th scope="col">Danh mục</th>
                        <th scope="col">Tỉnh/thành</th>
                        <th scope="col">Ngày gửi</th>
                        <th scope="col">Trạng thái</th>
                        <th scope="col"><span class="visually-hidden">Thao tác</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($places === []) : ?>
                        <tr><td colspan="8" class="text-center py-5 text-secondary">Hiện chưa có địa điểm nào chờ duyệt.</td></tr>
                    <?php else : ?>
                        <?php foreach ($places as $index => $place) : ?>
                            <tr>
                                <td class="text-secondary"><?php echo (int) $index + 1; ?></td>
                                <td>
                                    <strong><?php echo escapeHtml((string) $place['title']); ?></strong>
                                    <div class="small text-secondary">Địa điểm mới gửi</div>
                                </td>
                                <td><?php echo escapeHtml((string) $place['owner_name']); ?></td>
                                <td><?php echo escapeHtml((string) $place['category_name']); ?></td>
                                <td><?php echo escapeHtml((string) $place['province_name']); ?></td>
                                <td><?php echo escapeHtml(date('d/m/Y', strtotime((string) $place['created_at']))); ?></td>
                                <td><span class="badge rounded-pill text-bg-warning">Chờ duyệt</span></td>
                                <td><a class="btn btn-sm btn-outline-danger" href="<?php echo escapeHtml(createRouteUrl('places/show') . '&id=' . (int) $place['place_id']); ?>">Xem xét <span aria-hidden="true">→</span></a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
