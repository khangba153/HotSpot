<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;
use RuntimeException;
use Throwable;

final class PlaceImage extends Model
{
    public const MIN_IMAGES_FOR_CREATE = 1;
    public const MAX_IMAGES_PER_PLACE = 3;
    public const MAX_FILE_SIZE_BYTES = 5242880;
    public const UPLOAD_RELATIVE_DIRECTORY = 'uploads/places';

    private const ALLOWED_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    // Lấy ảnh của địa điểm theo thứ tự hiển thị (sort_order 1–3).
    public function findByPlaceId(int $placeId): array
    {
        if ($placeId <= 0) {
            return [];
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT image_id, image_path, sort_order
                     FROM place_images
                     WHERE place_id = :place_id
                     ORDER BY sort_order, image_id';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);
        $preparedStatement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        $preparedStatement->execute();

        $imageList = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
        $preparedStatement->closeCursor();

        return $imageList;
    }

    // Xóa bản ghi ảnh trong giao dịch; file cũ chỉ được dọn sau khi Controller commit.
    public function removeRecordsForPlace(int $placeId): void
    {
        $databaseConnection = self::getConnection();
        if ($databaseConnection->inTransaction() === false) {
            throw new RuntimeException('Phải mở giao dịch trước khi thay ảnh địa điểm.');
        }
        $statement = $databaseConnection->prepare('DELETE FROM place_images WHERE place_id = :place_id');
        $statement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        try {
            $statement->execute();
        } finally {
            $statement->closeCursor();
        }
    }

    /**
     * Kiểm tra lô file ảnh tải lên (TV3-04): số lượng 1–3, dung lượng thực tế, MIME thật và khả năng đọc ảnh.
     *
     * @param array<string, mixed> $filesInput Mảng $_FILES['images'] hoặc danh sách file đã chuẩn hóa.
     * @return array{
     *     valid: bool,
     *     errors: array<int, string>,
     *     files: array<int, array{
     *         tmp_name: string,
     *         original_name: string,
     *         mime_type: string,
     *         extension: string,
     *         size_bytes: int,
     *         sort_order: int
     *     }>
     * }
     */
    public function validateUploadBatch(
        array $filesInput,
        int $minCount = self::MIN_IMAGES_FOR_CREATE,
        int $maxCount = self::MAX_IMAGES_PER_PLACE,
        bool $requireHttpUploadedFile = true
    ): array {
        $errors = [];
        $normalizedItems = $this->normalizeFilesArray($filesInput);

        $providedItems = [];
        foreach ($normalizedItems as $fileItem) {
            $errorCode = UPLOAD_ERR_NO_FILE;
            if (isset($fileItem['error'])) {
                $errorCode = (int) $fileItem['error'];
            }

            $temporaryFileName = '';
            if (isset($fileItem['tmp_name'])) {
                $temporaryFileName = trim((string) $fileItem['tmp_name']);
            }

            $originalName = '';
            if (isset($fileItem['name'])) {
                $originalName = trim((string) $fileItem['name']);
            }

            if ($errorCode === UPLOAD_ERR_NO_FILE && $temporaryFileName === '' && $originalName === '') {
                continue;
            }

            $providedItems[] = $fileItem;
        }

        $providedCount = count($providedItems);

        if ($providedCount < $minCount) {
            $errors[] = 'Vui lòng chọn từ ' . $minCount . ' đến ' . $maxCount . ' hình ảnh cho địa điểm.';
            return [
                'valid' => false,
                'errors' => $errors,
                'files' => [],
            ];
        }

        if ($providedCount > $maxCount) {
            $errors[] = 'Chỉ được tải lên tối đa ' . $maxCount . ' hình ảnh cho mỗi địa điểm.';
            return [
                'valid' => false,
                'errors' => $errors,
                'files' => [],
            ];
        }

        $validatedFiles = [];
        $fileInformationHandle = null;

        if (function_exists('finfo_open')) {
            $fileInformationHandle = finfo_open(FILEINFO_MIME_TYPE);
        }

        try {
            foreach ($providedItems as $itemIndex => $fileItem) {
                $slotNumber = $itemIndex + 1;
                $validatedFile = $this->validateSingleUploadedImage(
                    $fileItem,
                    $slotNumber,
                    $requireHttpUploadedFile,
                    $fileInformationHandle,
                    $errors
                );
                if ($validatedFile !== null) {
                    // Chỉ ảnh hợp lệ mới chiếm một vị trí trong danh sách kết quả.
                    $validatedFile['sort_order'] = count($validatedFiles) + 1;
                    $validatedFiles[] = $validatedFile;
                }
            }
        } finally {
            if ($fileInformationHandle !== false && $fileInformationHandle !== null && function_exists('finfo_close')) {
                finfo_close($fileInformationHandle);
            }
        }

        if (count($errors) > 0) {
            return [
                'valid' => false,
                'errors' => $errors,
                'files' => [],
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
            'files' => $validatedFiles,
        ];
    }

    // Kiểm tra từng ảnh; lỗi được gom vào danh sách chung, ảnh sai trả về null.
    private function validateSingleUploadedImage(
        array $fileItem,
        int $slotNumber,
        bool $requireHttpUploadedFile,
        mixed $fileInformationHandle,
        array &$errors
    ): ?array {
        $errorCode = UPLOAD_ERR_NO_FILE;
        if (isset($fileItem['error'])) {
            $errorCode = (int) $fileItem['error'];
        }

        $temporaryFilePath = '';
        if (isset($fileItem['tmp_name'])) {
            $temporaryFilePath = trim((string) $fileItem['tmp_name']);
        }

        $originalName = 'Ảnh #' . $slotNumber;
        if (isset($fileItem['name']) && trim((string) $fileItem['name']) !== '') {
            $originalName = trim((string) $fileItem['name']);
        }

        if ($errorCode !== UPLOAD_ERR_OK) {
            $errors[] = $this->mapUploadErrorMessage($errorCode, $slotNumber);
            return null;
        }

        if ($temporaryFilePath === '' || is_file($temporaryFilePath) === false || is_readable($temporaryFilePath) === false) {
            $errors[] = 'Không đọc được tệp tải lên ở vị trí ảnh #' . $slotNumber . '.';
            return null;
        }

        if ($requireHttpUploadedFile && is_uploaded_file($temporaryFilePath) === false) {
            $errors[] = 'Tệp ở vị trí ảnh #' . $slotNumber . ' không phải tệp tải lên qua HTTP hợp lệ.';
            return null;
        }

        $actualFileSize = filesize($temporaryFilePath);
        $reportedFileSize = 0;
        if (isset($fileItem['size']) && is_numeric($fileItem['size'])) {
            $reportedFileSize = (int) $fileItem['size'];
        }

        if ($actualFileSize === false || $actualFileSize <= 0) {
            $errors[] = 'Tệp ở vị trí ảnh #' . $slotNumber . ' bị rỗng hoặc không hợp lệ.';
            return null;
        }

        if ($reportedFileSize > 0 && $reportedFileSize !== $actualFileSize) {
            $errors[] = 'Kích thước tệp ở vị trí ảnh #' . $slotNumber . ' không khớp dữ liệu tải lên.';
            return null;
        }

        if ($actualFileSize > self::MAX_FILE_SIZE_BYTES) {
            $errors[] = 'Ảnh #' . $slotNumber . ' vượt quá dung lượng tối đa 5MB.';
            return null;
        }

        $detectedMimeType = '';
        if ($fileInformationHandle !== false && $fileInformationHandle !== null) {
            $detectedMimeValue = finfo_file($fileInformationHandle, $temporaryFilePath);
            if (is_string($detectedMimeValue)) {
                $detectedMimeType = strtolower(trim($detectedMimeValue));
            }
        }

        if (isset(self::ALLOWED_MIME_EXTENSIONS[$detectedMimeType]) === false) {
            $errors[] = 'Ảnh #' . $slotNumber . ' không đúng định dạng cho phép (chỉ nhận JPG, PNG, WebP).';
            return null;
        }

        // Đối chiếu khả năng đọc cấu trúc ảnh thực tế và khớp MIME.
        $imageSizeInfo = @getimagesize($temporaryFilePath);
        if (is_array($imageSizeInfo) === false) {
            $errors[] = 'Ảnh #' . $slotNumber . ' không phải tệp hình ảnh hợp lệ hoặc dữ liệu ảnh bị hỏng.';
            return null;
        }

        $imageWidth = 0;
        if (isset($imageSizeInfo[0])) {
            $imageWidth = (int) $imageSizeInfo[0];
        }

        $imageHeight = 0;
        if (isset($imageSizeInfo[1])) {
            $imageHeight = (int) $imageSizeInfo[1];
        }

        $imageMimeFromHeader = '';
        if (isset($imageSizeInfo['mime'])) {
            $imageMimeFromHeader = strtolower(trim((string) $imageSizeInfo['mime']));
        }

        if ($imageWidth <= 0 || $imageHeight <= 0 || $imageMimeFromHeader !== $detectedMimeType) {
            $errors[] = 'Nội dung ảnh #' . $slotNumber . ' không khớp định dạng hình ảnh hợp lệ.';
            return null;
        }

        $safeExtension = self::ALLOWED_MIME_EXTENSIONS[$detectedMimeType];

        return [
            'tmp_name' => $temporaryFilePath,
            'original_name' => $originalName,
            'mime_type' => $detectedMimeType,
            'extension' => $safeExtension,
            'size_bytes' => $actualFileSize,
        ];
    }

    /**
     * Lưu các file ảnh đã qua kiểm tra vào thư mục public/uploads/places.
     * Nếu có bất kỳ file nào lưu thất bại giữa chừng, tự động dọn toàn bộ file vừa tạo.
     *
     * @param array<int, array<string, mixed>> $validatedFiles Danh sách file từ validateUploadBatch().
     * @return array<int, array{image_path: string, full_path: string, sort_order: int}>
     */
    public function saveValidatedFiles(
        array $validatedFiles,
        ?string $publicRootDirectory = null,
        bool $useMoveUploadedFile = true
    ): array {
        if (count($validatedFiles) < self::MIN_IMAGES_FOR_CREATE || count($validatedFiles) > self::MAX_IMAGES_PER_PLACE) {
            throw new RuntimeException('Số lượng ảnh cần lưu phải từ 1 đến 3 ảnh.');
        }

        $resolvedPublicRoot = $publicRootDirectory;
        if ($resolvedPublicRoot === null || trim($resolvedPublicRoot) === '') {
            $resolvedPublicRoot = dirname(__DIR__, 2) . '/public';
        }
        $resolvedPublicRoot = rtrim(str_replace('\\', '/', $resolvedPublicRoot), '/');

        $targetDirectory = $resolvedPublicRoot . '/' . self::UPLOAD_RELATIVE_DIRECTORY;
        if (is_dir($targetDirectory) === false) {
            $createdDirectory = @mkdir($targetDirectory, 0755, true);
            if ($createdDirectory === false && is_dir($targetDirectory) === false) {
                throw new RuntimeException('Không thể khởi tạo thư mục lưu ảnh địa điểm.');
            }
        }

        $savedFiles = [];

        try {
            foreach ($validatedFiles as $index => $fileItem) {
                $temporaryFilePath = '';
                if (isset($fileItem['tmp_name'])) {
                    $temporaryFilePath = trim((string) $fileItem['tmp_name']);
                }

                $mimeType = '';
                if (isset($fileItem['mime_type'])) {
                    $mimeType = strtolower(trim((string) $fileItem['mime_type']));
                }

                if (isset(self::ALLOWED_MIME_EXTENSIONS[$mimeType]) === false) {
                    throw new RuntimeException('Định dạng MIME của ảnh không hợp lệ khi lưu.');
                }

                // Đuôi file lưu trên đĩa luôn lấy từ MIME đã xác thực.
                $extension = self::ALLOWED_MIME_EXTENSIONS[$mimeType];

                $sortOrder = $index + 1;
                if (isset($fileItem['sort_order'])) {
                    $sortOrder = (int) $fileItem['sort_order'];
                }

                if ($sortOrder < 1 || $sortOrder > self::MAX_IMAGES_PER_PLACE) {
                    throw new RuntimeException('Thứ tự ảnh (sort_order) phải nằm trong khoảng 1 đến 3.');
                }

                $safeFileName = $this->generateUniqueSafeFileName($targetDirectory, $extension);
                $relativeImagePath = self::UPLOAD_RELATIVE_DIRECTORY . '/' . $safeFileName;
                $destinationFullPath = $targetDirectory . '/' . $safeFileName;

                $moveSucceeded = false;
                if ($useMoveUploadedFile) {
                    if (is_uploaded_file($temporaryFilePath)) {
                        $moveSucceeded = @move_uploaded_file($temporaryFilePath, $destinationFullPath);
                    }
                } else {
                    if (is_file($temporaryFilePath)) {
                        $moveSucceeded = @copy($temporaryFilePath, $destinationFullPath);
                    }
                }

                if ($moveSucceeded === false || is_file($destinationFullPath) === false) {
                    throw new RuntimeException('Không thể lưu tệp ảnh #' . $sortOrder . ' lên máy chủ.');
                }

                $savedFiles[] = [
                    'image_path' => $relativeImagePath,
                    'full_path' => $destinationFullPath,
                    'sort_order' => $sortOrder,
                ];
            }
        } catch (Throwable $exception) {
            // Dọn các file vừa tạo của batch hiện tại và giữ nguyên ngoại lệ gốc.
            $this->cleanupSavedFiles($savedFiles, $resolvedPublicRoot);
            throw $exception;
        }

        return $savedFiles;
    }

    /**
     * Dọn các file ảnh do batch hiện tại vừa tạo trên đĩa khi lưu một phần thất bại hoặc khi rollback DB.
     * Trả về danh sách đường dẫn tương đối chưa xóa được (nếu có) và ghi log để caller kiểm soát.
     *
     * @param array<int, mixed> $savedFilesOrPaths
     * @return array<int, string> Danh sách đường dẫn tương đối chưa xóa được (rỗng nếu đã dọn sạch).
     */
    public function cleanupSavedFiles(array $savedFilesOrPaths, ?string $publicRootDirectory = null): array
    {
        $resolvedPublicRoot = $publicRootDirectory;
        if ($resolvedPublicRoot === null || trim($resolvedPublicRoot) === '') {
            $resolvedPublicRoot = dirname(__DIR__, 2) . '/public';
        }
        $resolvedPublicRoot = rtrim(str_replace('\\', '/', $resolvedPublicRoot), '/');

        $undeletedPaths = [];

        foreach ($savedFilesOrPaths as $entry) {
            $relativeImagePath = '';
            if (is_array($entry) && isset($entry['image_path'])) {
                $relativeImagePath = trim((string) $entry['image_path']);
            } elseif (is_string($entry)) {
                $relativeImagePath = trim($entry);
            }

            if ($this->isSafeUploadedPlacePath($relativeImagePath) === false) {
                continue;
            }

            $fullPath = $resolvedPublicRoot . '/' . $relativeImagePath;
            if (is_file($fullPath)) {
                $unlinkSucceeded = @unlink($fullPath);
                if ($unlinkSucceeded === false || is_file($fullPath)) {
                    $undeletedPaths[] = $relativeImagePath;
                    error_log('PlaceImage::cleanupSavedFiles failed to remove file: ' . $relativeImagePath);
                }
            }
        }

        return $undeletedPaths;
    }

    /**
     * Gọi thủ tục CTH_ADD_PLACE_IMAGE(owner_id, place_id, image_path, sort_order) trong một giao dịch đang mở.
     * Controller chịu trách nhiệm lấy ownerId từ session và quản lý beginTransaction/commit/rollBack.
     */
    public function addPlaceImage(int $ownerId, int $placeId, string $imagePath, int $sortOrder): void
    {
        if ($ownerId <= 0) {
            throw new RuntimeException('Mã người đăng không hợp lệ khi lưu ảnh địa điểm.');
        }

        if ($placeId <= 0) {
            throw new RuntimeException('Mã địa điểm không hợp lệ khi lưu ảnh.');
        }

        $cleanImagePath = trim($imagePath);
        if ($this->isSafeUploadedPlacePath($cleanImagePath) === false) {
            throw new RuntimeException('Đường dẫn ảnh lưu vào cơ sở dữ liệu không hợp lệ.');
        }

        if ($sortOrder < 1 || $sortOrder > self::MAX_IMAGES_PER_PLACE) {
            throw new RuntimeException('Thứ tự ảnh phải từ 1 đến 3.');
        }

        $databaseConnection = self::getConnection();
        if ($databaseConnection->inTransaction() === false) {
            throw new RuntimeException('Thao tác lưu ảnh địa điểm phải được thực hiện trong một giao dịch (transaction) đang mở.');
        }

        $preparedStatement = $databaseConnection->prepare(
            'CALL CTH_ADD_PLACE_IMAGE(:owner_id, :place_id, :image_path, :sort_order)'
        );

        $preparedStatement->bindValue(':owner_id', $ownerId, PDO::PARAM_INT);
        $preparedStatement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        $preparedStatement->bindValue(':image_path', $cleanImagePath, PDO::PARAM_STR);
        $preparedStatement->bindValue(':sort_order', $sortOrder, PDO::PARAM_INT);

        try {
            $preparedStatement->execute();
            while ($preparedStatement->nextRowset()) {
            }
        } finally {
            $preparedStatement->closeCursor();
        }
    }

    // Kiểm tra đường dẫn tương đối thuộc đúng thư mục uploads/places và không chứa '..'.
    public function isSafeUploadedPlacePath(string $imagePath): bool
    {
        if (strpos($imagePath, '..') !== false) {
            return false;
        }

        return preg_match('#^uploads/places/[A-Za-z0-9._-]+\.(jpg|png|webp)$#', $imagePath) === 1;
    }

    // Sinh tên file ngẫu nhiên 16 byte an toàn, không dùng tên gốc từ người dùng và tránh trùng lặp.
    private function generateUniqueSafeFileName(string $targetDirectory, string $extension): string
    {
        $cleanDirectory = rtrim(str_replace('\\', '/', $targetDirectory), '/');

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $randomHex = bin2hex(random_bytes(16));
            $candidateName = 'place_' . date('Ymd_His') . '_' . $randomHex . '.' . $extension;
            $candidateFullPath = $cleanDirectory . '/' . $candidateName;

            if (file_exists($candidateFullPath) === false) {
                return $candidateName;
            }
        }

        throw new RuntimeException('Không thể tạo tên tệp ảnh duy nhất.');
    }

    /**
     * Chuẩn hóa cấu trúc mảng $_FILES['images'] (dạng đa file hoặc đơn file) về danh sách đồng nhất.
     *
     * @param array<string, mixed> $filesInput
     * @return array<int, array{name: string, type: string, tmp_name: string, error: int, size: int}>
     */
    private function normalizeFilesArray(array $filesInput): array
    {
        if (count($filesInput) === 0) {
            return [];
        }

        // Input images[]: ghép các cột của $_FILES thành từng file riêng.
        if (isset($filesInput['name']) && is_array($filesInput['name'])) {
            $fileColumns = [];
            foreach (['type', 'tmp_name', 'error', 'size'] as $fieldName) {
                $fileColumns[$fieldName] = [];
                if (isset($filesInput[$fieldName]) && is_array($filesInput[$fieldName])) {
                    $fileColumns[$fieldName] = $filesInput[$fieldName];
                }
            }

            $normalizedFiles = [];
            foreach ($filesInput['name'] as $fileIndex => $originalName) {
                $fileItem = ['name' => $originalName];
                foreach ($fileColumns as $fieldName => $columnValues) {
                    $fileItem[$fieldName] = $columnValues[$fileIndex] ?? null;
                }
                $normalizedFiles[] = $this->normalizeSingleFile($fileItem);
            }
            return $normalizedFiles;
        }

        // Input một file đơn vẫn trả về danh sách có một phần tử.
        if (isset($filesInput['name']) && is_scalar($filesInput['name'])) {
            return [$this->normalizeSingleFile($filesInput)];
        }

        // Danh sách đã tách sẵn dùng cùng quy tắc chuẩn hóa với $_FILES.
        $normalizedFiles = [];
        foreach ($filesInput as $fileItem) {
            if (is_array($fileItem)) {
                $normalizedFiles[] = $this->normalizeSingleFile($fileItem);
            }
        }
        return $normalizedFiles;
    }

    // Giữ tên khóa theo chuẩn PHP; chỉ chuyển kiểu các giá trị đầu vào hợp lệ.
    private function normalizeSingleFile(array $fileItem): array
    {
        $normalizedFile = ['name' => '', 'type' => '', 'tmp_name' => '',
            'error' => UPLOAD_ERR_NO_FILE, 'size' => 0];
        foreach (['name', 'type', 'tmp_name'] as $fieldName) {
            if (isset($fileItem[$fieldName]) && is_scalar($fileItem[$fieldName])) {
                $normalizedFile[$fieldName] = (string) $fileItem[$fieldName];
            }
        }
        if (isset($fileItem['error']) && is_numeric($fileItem['error'])) {
            $normalizedFile['error'] = (int) $fileItem['error'];
        }
        if (isset($fileItem['size']) && is_numeric($fileItem['size'])) {
            $normalizedFile['size'] = (int) $fileItem['size'];
        }
        return $normalizedFile;
    }

    // Chuyển mã lỗi upload của PHP thành thông báo tiếng Việt rõ ràng.
    private function mapUploadErrorMessage(int $errorCode, int $slotNumber): string
    {
        if ($errorCode === UPLOAD_ERR_INI_SIZE || $errorCode === UPLOAD_ERR_FORM_SIZE) {
            return 'Ảnh #' . $slotNumber . ' vượt quá dung lượng tải lên cho phép (tối đa 5MB).';
        }

        if ($errorCode === UPLOAD_ERR_PARTIAL) {
            return 'Ảnh #' . $slotNumber . ' chỉ mới được tải lên một phần. Vui lòng thử lại.';
        }

        if ($errorCode === UPLOAD_ERR_NO_FILE) {
            return 'Vị trí ảnh #' . $slotNumber . ' chưa có tệp được chọn.';
        }

        return 'Không thể tải lên ảnh #' . $slotNumber . '. Vui lòng kiểm tra lại tệp.';
    }
}
