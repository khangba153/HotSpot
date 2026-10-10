<div class="auth-card mx-auto"><h1 class="h3 mb-4">Tạo tài khoản</h1>
<?php if (isset($errors['general'])): ?><div class="alert alert-danger"><?= h($errors['general']) ?></div><?php endif; ?>
<form action="<?= h(url('auth/register')) ?>" method="post" class="vstack gap-3">
  <?= csrf_field() ?>
  <div><label for="full_name" class="form-label">Họ tên</label><input class="form-control" name="full_name" id="full_name" required maxlength="120" value="<?= h($form['name'] ?? '') ?>"><?php if (isset($errors['full_name'])): ?><small class="text-danger"><?= h($errors['full_name']) ?></small><?php endif; ?></div>
  <div><label for="email" class="form-label">Email</label><input class="form-control" type="email" name="email" id="email" required maxlength="254" value="<?= h($form['email'] ?? '') ?>"><?php if (isset($errors['email'])): ?><small class="text-danger"><?= h($errors['email']) ?></small><?php endif; ?></div>
  <div><label for="password" class="form-label">Mật khẩu (8–72 byte)</label><input class="form-control" type="password" name="password" id="password" required minlength="8" autocomplete="new-password"><?php if (isset($errors['password'])): ?><small class="text-danger"><?= h($errors['password']) ?></small><?php endif; ?></div>
  <div><label for="confirm" class="form-label">Xác nhận mật khẩu</label><input class="form-control" type="password" name="password_confirmation" id="confirm" required autocomplete="new-password"><?php if (isset($errors['password_confirmation'])): ?><small class="text-danger"><?= h($errors['password_confirmation']) ?></small><?php endif; ?></div>
  <button class="btn btn-primary" type="submit">Đăng ký</button>
</form><p class="mt-3 text-secondary small">Đã có tài khoản? <a href="<?= h(url('auth/login')) ?>">Đăng nhập</a>.</p></div>
