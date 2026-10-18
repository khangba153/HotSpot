<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class User extends Model
{
    // Lấy thông tin Profile theo ID từ session; trả về null nếu không tìm thấy.
    public function findById(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT user_id,
                    full_name,
                    email,
                    role_code,
                    phone,
                    province_code,
                    bio,
                    created_at
             FROM users
             WHERE user_id = :user_id
             LIMIT 1';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        // Truyền ID bằng tham số để tránh SQL injection.
        $preparedStatement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $preparedStatement->execute();

        $userInformation = $preparedStatement->fetch(PDO::FETCH_ASSOC);

        if ($userInformation === false) {
            return null;
        }

        return $userInformation;
    }

    // Gọi routine để tìm tài khoản theo email khi đăng nhập.
    public function findByEmail(string $email): ?array
    {
        $databaseConnection = self::getConnection();

        $sqlQuery = 'CALL KH_GET_USER_BY_EMAIL(:email)';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);
        $preparedStatement->bindValue(':email', $email, PDO::PARAM_STR);
        $preparedStatement->execute();

        $userInformation = $preparedStatement->fetch(PDO::FETCH_ASSOC);

        // Đọc hết các bộ kết quả còn lại trước khi truy vấn tiếp.
        while ($preparedStatement->nextRowset()) {
        }

        $preparedStatement->closeCursor();

        if ($userInformation === false) {
            return null;
        }

        return $userInformation;
    }

    // Cập nhật các thông tin hồ sơ được phép sửa.
    public function updateProfile(
        int $userId,
        string $fullName,
        string $email,
        ?string $phone = null,
        ?string $biography = null
    ): void {
        $databaseConnection = self::getConnection();

        $sqlQuery = 'UPDATE users
                    SET full_name = :full_name,
                        email = :email,
                        phone = :phone,
                        bio = :bio
                    WHERE user_id = :user_id';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        $preparedStatement->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $preparedStatement->bindValue(
            ':full_name',
            $fullName,
            PDO::PARAM_STR
        );

        $preparedStatement->bindValue(
            ':email',
            $email,
            PDO::PARAM_STR
        );

        if ($phone === null) {
            $preparedStatement->bindValue(
                ':phone',
                null,
                PDO::PARAM_NULL
            );
        } else {
            $preparedStatement->bindValue(
                ':phone',
                $phone,
                PDO::PARAM_STR
            );
        }

        if ($biography === null) {
            $preparedStatement->bindValue(
                ':bio',
                null,
                PDO::PARAM_NULL
            );
        } else {
            $preparedStatement->bindValue(
                ':bio',
                $biography,
                PDO::PARAM_STR
            );
        }

        $preparedStatement->execute();
    }

    // Đếm hoạt động của người dùng để hiển thị trên Profile.
    public function getProfileStatistics(int $userId): array
    {
        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT
                        (
                            SELECT COUNT(*)
                            FROM favorites
                            WHERE user_id = :favorite_user_id
                        ) AS favorite_count,
                        (
                            SELECT COUNT(*)
                            FROM places
                            WHERE owner_id = :place_owner_id
                        ) AS place_count,
                        (
                            SELECT COUNT(*)
                            FROM reviews
                            WHERE user_id = :review_user_id
                        ) AS review_count,
                        (
                            SELECT COUNT(*)
                            FROM place_images
                            INNER JOIN places
                                ON places.place_id = place_images.place_id
                            WHERE places.owner_id = :image_owner_id
                        ) AS image_count';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        $preparedStatement->bindValue(
            ':favorite_user_id',
            $userId,
            PDO::PARAM_INT
        );

        $preparedStatement->bindValue(
            ':place_owner_id',
            $userId,
            PDO::PARAM_INT
        );

        $preparedStatement->bindValue(
            ':review_user_id',
            $userId,
            PDO::PARAM_INT
        );

        $preparedStatement->bindValue(
            ':image_owner_id',
            $userId,
            PDO::PARAM_INT
        );

        $preparedStatement->execute();

        $statistics = $preparedStatement->fetch(PDO::FETCH_ASSOC);

        return [
            'favorite_count' => (int) $statistics['favorite_count'],
            'place_count' => (int) $statistics['place_count'],
            'review_count' => (int) $statistics['review_count'],
            'image_count' => (int) $statistics['image_count'],
        ];
    }
}