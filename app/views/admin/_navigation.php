<?php
$navigationRoute = '';
if (isset($_GET['route']) && is_string($_GET['route'])) {
    $navigationRoute = trim($_GET['route'], '/');
}
$managedView = isset($_GET['view']) && $_GET['view'] === 'managed';
$navigationItems = [
    ['route' => 'admin', 'label' => 'Tổng quan'],
    ['route' => 'admin/places', 'label' => 'Chờ duyệt'],
    ['route' => 'admin/places', 'label' => 'Quản lý địa điểm', 'managed' => true],
    ['route' => 'admin/reviews', 'label' => 'Đánh giá'],
    ['route' => 'admin/categories', 'label' => 'Danh mục'],
    ['route' => 'places/create', 'label' => 'Đăng địa điểm'],
];
?>
<nav class="admin-top-navigation" aria-label="Điều hướng quản trị">
        <?php foreach ($navigationItems as $navigationItem) {
            $navigationUrl = createRouteUrl($navigationItem['route']);
            $activeNavigation = $navigationRoute === $navigationItem['route'];
            if ($navigationRoute === 'admin/places/review' && $navigationItem['route'] === 'admin/places') {
                $activeNavigation = true;
            }
            if (isset($navigationItem['managed'])) {
                $navigationUrl .= '&view=managed';
                $activeNavigation = $activeNavigation && $managedView;
            } elseif ($navigationItem['route'] === 'admin/places') {
                $activeNavigation = $activeNavigation && !$managedView;
            }
        ?>
            <a href="<?php echo escapeHtml($navigationUrl); ?>"<?php if ($activeNavigation) { ?> class="active" aria-current="page"<?php } ?>><?php echo escapeHtml($navigationItem['label']); ?></a>
        <?php } ?>
        <a class="admin-back" href="<?php echo escapeHtml(createRouteUrl('home')); ?>">Về website</a>
</nav>
