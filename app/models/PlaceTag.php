<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;
use RuntimeException;

final class PlaceTag extends Model
{
    public const MAX_TAGS_PER_PLACE = 5;

    /**
     * Chuẩn hóa và kiểm tra danh sách tag_ids khi đăng địa điểm mới (TV3-04):
     * - Chỉ nhận số nguyên dương hợp lệ.
     * - Loại bỏ ID trùng lặp và giới hạn tối đa 5 nhãn khác nhau.
     * - Đối chiếu nhãn tồn tại và đang hoạt động (is_active = 1).
     *
     * @param mixed $rawTagIds Dữ liệu nhãn từ request (ví dụ $_POST['tag_ids']).
     * @param bool $verifyDatabase Có truy vấn bảng tags để xác thực trạng thái active hay không.
     * @param array<int, int>|null $knownActiveTagIds Danh sách ID nhãn active đã biết (nếu truyền sẵn).
     * @return array{valid: bool, errors: array<int, string>, tag_ids: array<int, int>}
     */
    public function validateAndNormalizeTagIds(
        mixed $rawTagIds,
        bool $verifyDatabase = true,
        ?array $knownActiveTagIds = null
    ): array {
        if ($rawTagIds === null) {
            return [
                'valid' => true,
                'errors' => [],
                'tag_ids' => [],
            ];
        }

        if (is_array($rawTagIds) === false) {
            return [
                'valid' => false,
                'errors' => ['Dữ liệu nhãn gửi lên không đúng định dạng.'],
                'tag_ids' => [],
            ];
        }

        $distinctTagIds = [];

        foreach ($rawTagIds as $rawItem) {
            $parsedTagId = $this->parsePositiveTagId($rawItem);

            if ($parsedTagId <= 0) {
                return [
                    'valid' => false,
                    'errors' => ['Mã nhãn đã chọn không đúng định dạng.'],
                    'tag_ids' => [],
                ];
            }

            if (in_array($parsedTagId, $distinctTagIds, true) === false) {
                $distinctTagIds[] = $parsedTagId;
            }
        }

        if (count($distinctTagIds) > self::MAX_TAGS_PER_PLACE) {
            return [
                'valid' => false,
                'errors' => ['Bạn chỉ được chọn tối đa ' . self::MAX_TAGS_PER_PLACE . ' nhãn cho mỗi địa điểm.'],
                'tag_ids' => [],
            ];
        }

        if (count($distinctTagIds) === 0) {
            return [
                'valid' => true,
                'errors' => [],
                'tag_ids' => [],
            ];
        }

        $activeTagIds = null;
        if ($knownActiveTagIds !== null) {
            $activeTagIds = [];
            foreach ($knownActiveTagIds as $knownId) {
                $parsedKnownId = $this->parsePositiveTagId($knownId);
                if ($parsedKnownId > 0 && in_array($parsedKnownId, $activeTagIds, true) === false) {
                    $activeTagIds[] = $parsedKnownId;
                }
            }
        } elseif ($verifyDatabase) {
            $activeTagIds = $this->findActiveTagIdsByIds($distinctTagIds);
        }

        if (is_array($activeTagIds)) {
            foreach ($distinctTagIds as $requestedTagId) {
                if (in_array($requestedTagId, $activeTagIds, true) === false) {
                    return [
                        'valid' => false,
                        'errors' => ['Một hoặc nhiều nhãn đã chọn không tồn tại hoặc đã ngừng hoạt động.'],
                        'tag_ids' => [],
                    ];
                }
            }
        }

        return [
            'valid' => true,
            'errors' => [],
            'tag_ids' => $distinctTagIds,
        ];
    }

    /**
     * Truy vấn bảng tags để lấy các tag_id thực sự tồn tại và đang hoạt động (is_active = 1).
     *
     * @param array<int, int> $tagIds
     * @return array<int, int>
     */
    public function findActiveTagIdsByIds(array $tagIds): array
    {
        $cleanIds = [];
        foreach ($tagIds as $rawId) {
            $parsedId = $this->parsePositiveTagId($rawId);
            if ($parsedId > 0 && in_array($parsedId, $cleanIds, true) === false) {
                $cleanIds[] = $parsedId;
            }
        }

        if (count($cleanIds) === 0) {
            return [];
        }

        $placeholders = [];
        foreach ($cleanIds as $unusedId) {
            $placeholders[] = '?';
        }

        $sqlQuery = 'SELECT tag_id
                     FROM tags
                     WHERE tag_id IN (' . implode(', ', $placeholders) . ')
                       AND is_active = 1';

        $databaseConnection = self::getConnection();
        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        foreach ($cleanIds as $parameterIndex => $tagId) {
            $preparedStatement->bindValue($parameterIndex + 1, $tagId, PDO::PARAM_INT);
        }

        try {
            $preparedStatement->execute();
            $rows = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

            $activeTagIds = [];
            foreach ($rows as $row) {
                if (isset($row['tag_id']) && is_numeric($row['tag_id'])) {
                    $activeId = (int) $row['tag_id'];
                    if ($activeId > 0 && in_array($activeId, $activeTagIds, true) === false) {
                        $activeTagIds[] = $activeId;
                    }
                }
            }

            return $activeTagIds;
        } finally {
            $preparedStatement->closeCursor();
        }
    }

    /**
     * Gắn danh sách nhãn đã xác thực cho địa điểm mới tạo trong một giao dịch đang mở (TV3-04).
     * Controller chịu trách nhiệm quản lý beginTransaction/commit/rollBack.
     *
     * @param array<int, int> $tagIds Danh sách tối đa 5 tag_id hợp lệ, không trùng.
     */
    public function attachTagsToPlace(int $placeId, array $tagIds): void
    {
        if ($placeId <= 0) {
            throw new RuntimeException('Mã địa điểm không hợp lệ khi gắn nhãn.');
        }

        $normalizedResult = $this->validateAndNormalizeTagIds($tagIds, false);
        if ($normalizedResult['valid'] === false) {
            $firstError = 'Danh sách nhãn không hợp lệ.';
            if (isset($normalizedResult['errors'][0])) {
                $firstError = $normalizedResult['errors'][0];
            }
            throw new RuntimeException($firstError);
        }

        $distinctTagIds = $normalizedResult['tag_ids'];
        if (count($distinctTagIds) === 0) {
            return;
        }

        $databaseConnection = self::getConnection();
        if ($databaseConnection->inTransaction() === false) {
            throw new RuntimeException('Thao tác gắn nhãn địa điểm phải được thực hiện trong một giao dịch (transaction) đang mở.');
        }

        $preparedStatement = $databaseConnection->prepare(
            'INSERT INTO place_tags (place_id, tag_id) VALUES (:place_id, :tag_id)'
        );

        try {
            foreach ($distinctTagIds as $tagId) {
                $preparedStatement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
                $preparedStatement->bindValue(':tag_id', $tagId, PDO::PARAM_INT);
                $preparedStatement->execute();
            }
        } finally {
            $preparedStatement->closeCursor();
        }
    }

    // Chuyển một giá trị đầu vào thành số nguyên dương; trả về 0 nếu sai định dạng.
    private function parsePositiveTagId(mixed $rawValue): int
    {
        if (is_int($rawValue)) {
            if ($rawValue > 0) {
                return $rawValue;
            }
            return 0;
        }

        if (is_string($rawValue) === false) {
            return 0;
        }

        $trimmedValue = trim($rawValue);
        if ($trimmedValue === '' || ctype_digit($trimmedValue) === false) {
            return 0;
        }

        if (strlen($trimmedValue) > 18) {
            return 0;
        }

        $tagId = (int) $trimmedValue;
        if ($tagId > 0) {
            return $tagId;
        }

        return 0;
    }
}
