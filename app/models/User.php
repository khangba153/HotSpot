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

        $sqlQuery = 'SELECT user_id, full_name, email, role_code, created_at
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
}