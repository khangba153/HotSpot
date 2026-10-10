<div class="auth-card mx-auto"><h1 class="h3 mb-4">Đăng nhập</h1>
<form action="<?= h(url('auth/login')) ?>" method="post" class="vstack gap-3">
  <?= csrf_field() ?><input type="hidden" name="next" value="<?= h($next) ?>">
  <div><label for="email" class="form-label">Email</label><input class="form-control" type="email" name="email" id="email" required autocomplete="email" value="<?= h($email) ?>"></div>
  <div><label for="password" class="form-label">Mật khẩu</label><input class="form-control" type="password" name="password" id="password" required autocomplete="current-password"></div>
  <button class="btn btn-primary" type="submit">Đăng nhập</button>
</form>
<p class="mt-3 text-secondary small">Chưa có tài khoản? <a href="<?= h(url('auth/register')) ?>">Đăng ký</a>.</p></div>
