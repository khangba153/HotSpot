<section class="p-4 p-md-5 bg-white rounded-3 border">
  <h1 class="display-6 fw-bold">Chào mừng đến với Hot Spot</h1>
  <p class="lead text-secondary">Cùng khám phá và chia sẻ những địa điểm yêu thích.</p>
  <?php if (current_user()): ?>
    <p class="mb-0">Xin chào, <strong><?= h(current_user()['full_name']) ?></strong>!</p>
  <?php else: ?>
    <a class="btn btn-primary" href="<?= h(url('auth/register')) ?>">Tạo tài khoản</a>
    <a class="btn btn-outline-secondary" href="<?= h(url('auth/login')) ?>">Đăng nhập</a>
  <?php endif; ?>
</section>
