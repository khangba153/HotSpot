<?php
declare(strict_types=1);

namespace HotSpot\Services;

use RuntimeException;

final class CategoryImage
{
    // Chỉ nhận đường dẫn ảnh nội bộ hoặc URL HTTPS, không nhận mã HTML/SVG.
    public function validatePath(string $imagePath): ?string
    {
        $imagePath = trim($imagePath);
        if ($imagePath === '') {
            return null;
        }
        if (strlen($imagePath) > 500) {
            throw new RuntimeException('Đường dẫn ảnh tối đa 500 ký tự.');
        }
        if (filter_var($imagePath, FILTER_VALIDATE_URL) !== false
            && str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }
        if (preg_match('#^(assets/images|uploads/categories)/[A-Za-z0-9_./-]+\.(png|jpg|jpeg|webp)$#i', $imagePath) === 1
            && strpos($imagePath, '..') === false
            && is_file(dirname(__DIR__, 2) . '/public/' . $imagePath)) {
            return $imagePath;
        }
        throw new RuntimeException('Chọn ảnh PNG, JPG, WebP có sẵn trong dự án, URL HTTPS hoặc tải ảnh mới.');
    }

    // Kiểm tra file thật trước khi lưu bằng tên ngẫu nhiên vào thư mục danh mục.
    public function saveUpload(array $uploadedFile): string
    {
        if (($uploadedFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Không tải được ảnh. Vui lòng chọn lại file tối đa 2 MB.');
        }
        $temporaryPath = $uploadedFile['tmp_name'] ?? '';
        if (!is_string($temporaryPath) || !is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('File tải lên không hợp lệ.');
        }
        $fileSize = filesize($temporaryPath);
        if ($fileSize === false || $fileSize < 1 || $fileSize > 2 * 1024 * 1024) {
            throw new RuntimeException('Ảnh danh mục phải nhỏ hơn hoặc bằng 2 MB.');
        }
        $fileInformation = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInformation->file($temporaryPath);
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $imageSize = @getimagesize($temporaryPath);
        if (!isset($extensions[$mimeType]) || $imageSize === false
            || $imageSize[0] > 6000 || $imageSize[1] > 6000) {
            throw new RuntimeException('Chỉ nhận ảnh PNG, JPG, WebP hợp lệ, kích thước tối đa 6000 × 6000 px.');
        }
        $relativePath = 'uploads/categories/' . bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
        $targetDirectory = dirname(__DIR__, 2) . '/public/uploads/categories';
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true)) {
            throw new RuntimeException('Không tạo được thư mục ảnh danh mục.');
        }
        if (!move_uploaded_file($temporaryPath, dirname(__DIR__, 2) . '/public/' . $relativePath)) {
            throw new RuntimeException('Không lưu được ảnh danh mục.');
        }
        return $relativePath;
    }

    // Chỉ dọn file vừa tải nếu lưu database thất bại.
    public function removeFailedUpload(string $imagePath): void
    {
        if (preg_match('#^uploads/categories/[a-f0-9]{32}\.(png|jpg|webp)$#', $imagePath) === 1) {
            $fullPath = dirname(__DIR__, 2) . '/public/' . $imagePath;
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
