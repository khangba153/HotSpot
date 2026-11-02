<?php

declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;
use RuntimeException;
use Throwable;

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

    // Lấy lịch sử đánh giá của người dùng, gồm cả đánh giá đã bị ẩn.
    public function findReviewHistoryByUserId(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT reviews.review_id,
                        reviews.place_id,
                        reviews.rating,
                        reviews.content,
                        reviews.status_code AS review_status,
                        reviews.created_at AS review_created_at,
                        places.title AS place_title,
                        places.status_code AS place_status
                 FROM reviews
                 INNER JOIN places
                     ON places.place_id = reviews.place_id
                 WHERE reviews.user_id = :user_id
                 ORDER BY reviews.created_at DESC,
                          reviews.review_id DESC';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        $preparedStatement->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $preparedStatement->execute();

        $reviewHistory = $preparedStatement->fetchAll(PDO::FETCH_ASSOC);

        $preparedStatement->closeCursor();

        return $reviewHistory;
    }

    // Tạo tài khoản Member bằng routine và trả về ID mới.
    public function createUser(
        string $fullName,
        string $email,
        string $passwordHash
    ): int {
        $databaseConnection = self::getConnection();

        $databaseConnection->beginTransaction();

        try {
            $sqlQuery = 'CALL KH_CREATE_USER(
                        :full_name,
                        :email,
                        :password_hash
                    )';

            $preparedStatement = $databaseConnection->prepare($sqlQuery);

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

            $preparedStatement->bindValue(
                ':password_hash',
                $passwordHash,
                PDO::PARAM_STR
            );

            $preparedStatement->execute();

            $userId = (int) $preparedStatement->fetchColumn();

            // Đọc hết kết quả trước khi commit transaction.
            while ($preparedStatement->nextRowset()) {
            }

            $preparedStatement->closeCursor();

            if ($userId <= 0) {
                throw new RuntimeException(
                    'Không nhận được ID tài khoản mới.'
                );
            }

            $databaseConnection->commit();

            return $userId;
        } catch (Throwable $exception) {
            if ($databaseConnection->inTransaction()) {
                $databaseConnection->rollBack();
            }

            throw $exception;
        }
    }

    // Tìm tài khoản Hot Spot đã liên kết với mã định danh Google.
    public function findByGoogleSubject(string $googleSubject): ?array
    {
        if ($googleSubject === '') {
            return null;
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT user_id,
                        full_name,
                        email,
                        role_code,
                        google_subject
                 FROM users
                 WHERE google_subject = :google_subject
                 LIMIT 1';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        $preparedStatement->bindValue(
            ':google_subject',
            $googleSubject,
            PDO::PARAM_STR
        );

        $preparedStatement->execute();

        $userInformation = $preparedStatement->fetch(PDO::FETCH_ASSOC);

        $preparedStatement->closeCursor();

        if ($userInformation === false) {
            return null;
        }

        return $userInformation;
    }

    // Tạo tài khoản Member mới từ thông tin Google đã xác minh.
    public function createGoogleUser(
        string $fullName,
        string $email,
        string $googleSubject
    ): int {
        $databaseConnection = self::getConnection();

        // Mật khẩu ngẫu nhiên không được dùng trong luồng đăng nhập Google.
        $randomPassword = bin2hex(random_bytes(32));
        $passwordHash = password_hash($randomPassword, PASSWORD_BCRYPT);

        $sqlQuery = 'INSERT INTO users (
                    role_code,
                    full_name,
                    email,
                    password_hash,
                    google_subject
                ) VALUES (
                    :role_code,
                    :full_name,
                    :email,
                    :password_hash,
                    :google_subject
                )';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        $preparedStatement->bindValue(
            ':role_code',
            'MEMBER',
            PDO::PARAM_STR
        );

        $preparedStatement->bindValue(
            ':full_name',
            $fullName,
            PDO::PARAM_STR
        );

        $preparedStatement->bindValue(
            ':email',
            strtolower(trim($email)),
            PDO::PARAM_STR
        );

        $preparedStatement->bindValue(
            ':password_hash',
            $passwordHash,
            PDO::PARAM_STR
        );

        $preparedStatement->bindValue(
            ':google_subject',
            $googleSubject,
            PDO::PARAM_STR
        );

        $preparedStatement->execute();

        $userId = (int) $databaseConnection->lastInsertId();

        $preparedStatement->closeCursor();

        return $userId;
    }







}
