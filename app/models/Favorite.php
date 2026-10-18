<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;
use RuntimeException;
use Throwable;

final class Favorite extends Model
{
    // Kiểm tra người dùng đã lưu địa điểm này hay chưa.
    public function isFavorited(int $userId, int $placeId): bool
    {
        if ($userId <= 0 || $placeId <= 0) {
            return false;
        }

        $databaseConnection = self::getConnection();

        $sqlQuery = 'SELECT COUNT(*)
                     FROM favorites
                     WHERE user_id = :user_id
                       AND place_id = :place_id';

        $preparedStatement = $databaseConnection->prepare($sqlQuery);

        $preparedStatement->bindValue(
            ':user_id',
            $userId,
            PDO::PARAM_INT
        );

        $preparedStatement->bindValue(
            ':place_id',
            $placeId,
            PDO::PARAM_INT
        );

        $preparedStatement->execute();

        $favoriteCount = (int) $preparedStatement->fetchColumn();

        return $favoriteCount > 0;
    }

    // Đặt trạng thái yêu thích bằng routine trong một transaction.
    public function setState(
        int $userId,
        int $placeId,
        int $desiredState
    ): bool {
        $databaseConnection = self::getConnection();

        $databaseConnection->beginTransaction();

        try {
            $sqlQuery = 'CALL KH_SET_FAVORITE(
                            :user_id,
                            :place_id,
                            :desired_state
                        )';

            $preparedStatement = $databaseConnection->prepare($sqlQuery);

            $preparedStatement->bindValue(
                ':user_id',
                $userId,
                PDO::PARAM_INT
            );

            $preparedStatement->bindValue(
                ':place_id',
                $placeId,
                PDO::PARAM_INT
            );

            $preparedStatement->bindValue(
                ':desired_state',
                $desiredState,
                PDO::PARAM_INT
            );

            $preparedStatement->execute();

            $favoriteInformation = $preparedStatement->fetch(PDO::FETCH_ASSOC);

            // Đọc hết kết quả để kết nối có thể thực hiện commit.
            while ($preparedStatement->nextRowset()) {
            }

            $preparedStatement->closeCursor();

            if ($favoriteInformation === false) {
                throw new RuntimeException(
                    'Không nhận được kết quả cập nhật yêu thích.'
                );
            }

            $databaseConnection->commit();

            return (int) $favoriteInformation['is_favorite'] === 1;
        } catch (Throwable $exception) {
            if ($databaseConnection->inTransaction()) {
                $databaseConnection->rollBack();
            }

            throw $exception;
        }
    }
}