<?php
declare(strict_types=1);

namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;
use Throwable;

final class Review extends Model
{
    /**
     * Lưu hoặc cập nhật review của thành viên bằng routine đã thống nhất.
     * Routine giữ nguyên trạng thái hiện tại nếu review đã tồn tại.
     *
     * @return array{review_id:int,status_code:string,rating:int}
     */
    public function saveOrUpdate(int $userId, int $placeId, int $rating, string $content): array
    {
        if ($userId <= 0 || $placeId <= 0) {
            throw new \InvalidArgumentException('Thông tin đánh giá không hợp lệ.');
        }

        $connection = self::getConnection();
        $connection->beginTransaction();

        try {
            $statement = $connection->prepare(
                'CALL HTH_SAVE_OR_UPDATE_REVIEW(:user_id, :place_id, :rating, :content)'
            );
            $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $statement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
            $statement->bindValue(':rating', $rating, PDO::PARAM_INT);
            $statement->bindValue(':content', $content, PDO::PARAM_STR);
            $statement->execute();

            $savedReview = $statement->fetch(PDO::FETCH_ASSOC);
            while ($statement->nextRowset()) {
                if ($savedReview === false) {
                    $savedReview = $statement->fetch(PDO::FETCH_ASSOC);
                }
            }
            $statement->closeCursor();

            if (
                !is_array($savedReview)
                || !isset($savedReview['review_id'], $savedReview['status_code'], $savedReview['rating'])
            ) {
                throw new \RuntimeException('Routine không trả về thông tin đánh giá đã lưu.');
            }

            $connection->commit();

            return [
                'review_id' => (int) $savedReview['review_id'],
                'status_code' => (string) $savedReview['status_code'],
                'rating' => (int) $savedReview['rating'],
            ];
        } catch (Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function findVisibleByPlaceId(int $placeId): array
    {
        if ($placeId <= 0) {
            return [];
        }

        $statement = self::getConnection()->prepare(
            "SELECT reviews.review_id, reviews.user_id, reviews.place_id, reviews.status_code,
                    reviews.rating, reviews.content, reviews.created_at, users.full_name
             FROM reviews
             INNER JOIN users ON users.user_id = reviews.user_id
             INNER JOIN places ON places.place_id = reviews.place_id
             WHERE reviews.place_id = :place_id
               AND reviews.status_code = 'VISIBLE'
               AND places.status_code = 'APPROVED'
             ORDER BY reviews.created_at DESC, reviews.review_id DESC"
        );
        $statement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        $statement->execute();
        $reviewList = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $reviewList;
    }

    /** Find this member's review for prefill, including a review hidden by moderation. */
    public function findForUserAndPlace(int $userId, int $placeId): ?array
    {
        if ($userId <= 0 || $placeId <= 0) {
            return null;
        }

        $statement = self::getConnection()->prepare(
            'SELECT review_id, user_id, place_id, status_code, rating, content, created_at
             FROM reviews
             WHERE user_id = :user_id AND place_id = :place_id
             LIMIT 1'
        );
        $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $statement->bindValue(':place_id', $placeId, PDO::PARAM_INT);
        $statement->execute();
        $review = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $review === false ? null : $review;
    }
}
