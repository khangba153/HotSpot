<?php
declare(strict_types=1);
namespace HotSpot\Models;
use HotSpot\Core\Model;
use PDO;
use Throwable;
final class User extends Model
{
    public static function findByEmail(string $email): ?array
    {
        $stmt=self::db()->prepare('CALL KH_GET_USER_BY_EMAIL(?)');
        try {
            $stmt->execute([$email]);
            $row=$stmt->fetch(PDO::FETCH_ASSOC);
            while ($stmt->nextRowset()) {}
            return $row ?: null;
        } finally { $stmt->closeCursor(); }
    }
    public static function create(string $name, string $email, string $hash): int
    {
        $pdo=self::db();
        $pdo->beginTransaction(); // KH_CREATE_USER requires a caller transaction.
        try {
            $stmt=$pdo->prepare('CALL KH_CREATE_USER(?,?,?)');
            try {
                $stmt->execute([$name,$email,$hash]);
                $row=$stmt->fetch(PDO::FETCH_ASSOC);
                while ($stmt->nextRowset()) {}
            } finally { $stmt->closeCursor(); }
            $pdo->commit();
            return (int)$row['user_id'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
}
