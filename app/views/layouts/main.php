<?php
/** @var array<string, mixed> $viewData Dữ liệu được truyền từ View::render(). */
/** @var string $pageContent Nội dung HTML được View::render() tạo từ View con. */
$pageTitle = 'Hot Spot';

if (isset($viewData['title'])) {
    $pageTitle = $viewData['title'];
}

$homePageUrl = createRouteUrl('home');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo escapeHtml($pageTitle); ?> | Hot Spot</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <header class="border-bottom bg-white">
        <nav class="container py-3">
            <a class="fs-4 fw-bold text-decoration-none" href="<?php echo escapeHtml($homePageUrl); ?>">
                Hot Spot
            </a>
        </nav>
    </header>
    <main class="container py-4 flex-grow-1">
        <?php echo $pageContent; ?>
    </main>
    <footer class="border-top py-3 text-center text-secondary">
        Hot Spot — Dự án môn Phát triển Web
    </footer>
    <script src="assets/vendor/bootstrap.bundle.min.js"></script>
</body>
</html>