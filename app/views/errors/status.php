<?php
/** @var array<string, mixed> $viewData Dữ liệu được truyền từ View::render(). */
$httpStatusText = (string) $viewData['status'];
$errorMessage = $viewData['message'];
?>
<section class="alert alert-danger">
    <h1 class="h4">Thông báo <?php echo escapeHtml($httpStatusText); ?></h1>
    <p class="mb-0"><?php echo escapeHtml($errorMessage); ?></p>
</section>