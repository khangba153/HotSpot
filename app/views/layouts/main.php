<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= h(csrf_token()) ?>">
  <title><?= h($title) ?> | Hot Spot</title>
  <link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="d-flex flex-column min-vh-100">
  <header class="border-bottom bg-white">
    <nav class="navbar navbar-expand-lg container py-3">
      <a class="navbar-brand fw-bold" href="<?= h(url('home')) ?>">Hot Spot<span class="text-primary">.</span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHotSpot" aria-label="Mở menu"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="navbarHotSpot">
        <div class="navbar-nav me-auto"><a class="nav-link" href="<?= h(url('home')) ?>">Khám phá</a></div>
        <div class="navbar-nav gap-1 align-items-lg-center">
          <?php if (current_user()): ?>
            <span class="nav-link"><?= h(current_user()['full_name']) ?></span>
            <?php if ((current_user()['role_code'] ?? '') === 'ADMIN'): ?><a class="nav-link" href="<?= h(url('admin')) ?>">Admin</a><?php endif; ?>
            <form action="<?= h(url('auth/logout')) ?>" method="post" class="m-0"><?= csrf_field() ?><button class="btn btn-outline-secondary btn-sm" type="submit">Đăng xuất</button></form>
          <?php else: ?>
            <a class="nav-link" href="<?= h(url('auth/login')) ?>">Đăng nhập</a><a class="btn btn-primary btn-sm" href="<?= h(url('auth/register')) ?>">Đăng ký</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </header>
  <main class="container py-4 flex-grow-1">
    <?php $notice = pull_flash(); if ($notice): ?>
      <div class="alert alert-<?= h($notice['type']) ?>" role="status"><?= h($notice['message']) ?></div>
    <?php endif; ?>
    <?= $content ?>
  </main>
  <footer class="border-top py-3 text-center small text-secondary">Hot Spot — Website cộng đồng chia sẻ địa điểm · Dự án môn học</footer>
  <script src="assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>
