<?php
/** @var array<string, mixed> $userInformation */
/** @var string $activeAccountSection */

// Các trang cá nhân dùng chung thông tin, menu và form đăng xuất.
$biography = (string) ($userInformation['bio'] ?? '');
$csrfToken = (string) $viewData['csrfToken'];
$logoutUrl = createRouteUrl('logout');
$accountMenuItems = [
    ['section' => 'overview', 'label' => 'Hồ sơ', 'url' => createRouteUrl('profile')],
    ['section' => 'places', 'label' => 'Địa điểm của tôi', 'url' => createRouteUrl('places/mine')],
    ['section' => 'favorites', 'label' => 'Yêu thích', 'url' => createRouteUrl('favorites')],
    ['section' => 'reviews', 'label' => 'Đã đánh giá', 'url' => createRouteUrl('profile') . '&tab=reviews'],
];
?>
            <aside class="profile-sidebar" aria-label="Điều hướng tài khoản">
                <div class="text-center">
                    <img
                        src="assets/images/default-avatar.svg"
                        alt="Ảnh đại diện mặc định"
                        class="profile-avatar"
                        width="160"
                        height="160">

                    <h2 class="profile-name">
                        <?php echo escapeHtml($userInformation['full_name']); ?>
                    </h2>

                    <p class="profile-description">
                        <?php if ($biography === '') { ?>
                            Chưa có thông tin giới thiệu.
                        <?php } else { ?>
                            <?php echo escapeHtml($biography); ?>
                        <?php } ?>
                    </p>
                </div>
                <nav class="profile-menu" aria-label="Menu hồ sơ">
                    <?php foreach ($accountMenuItems as $accountMenuItem) {
                        $accountMenuClass = 'profile-menu-item';
                        if ($activeAccountSection === $accountMenuItem['section']) {
                            $accountMenuClass .= ' active';
                        }
                    ?>
                        <a class="<?php echo escapeHtml($accountMenuClass); ?>"
                           href="<?php echo escapeHtml($accountMenuItem['url']); ?>"
                           <?php if ($activeAccountSection === $accountMenuItem['section']) { ?>aria-current="page"<?php } ?>>
                            <?php echo escapeHtml($accountMenuItem['label']); ?>
                        </a>
                    <?php } ?>
                </nav>

                <form
                    method="post"
                    action="<?php echo escapeHtml($logoutUrl); ?>"
                    class="mt-4">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo escapeHtml($csrfToken); ?>">

                    <button
                        type="submit"
                        class="btn btn-outline-danger w-100">
                        Đăng xuất
                    </button>
                </form>
            </aside>
