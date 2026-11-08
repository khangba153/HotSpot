<?php
declare(strict_types=1);
namespace HotSpot\Models;

use HotSpot\Core\Model;
use PDO;

final class AdminCategory extends Model
{
    // Lấy danh mục và số địa điểm thật, kể cả danh mục chưa có địa điểm.
    public function findAll(): array
    {
        $statement = self::getConnection()->prepare('SELECT c.category_id, c.name, c.description, c.image_path, c.card_label_image_path, c.is_active, COUNT(p.place_id) AS place_count FROM categories c LEFT JOIN places p ON p.category_id = c.category_id GROUP BY c.category_id, c.name, c.description, c.image_path, c.card_label_image_path, c.is_active ORDER BY c.category_id');
        $statement->execute();
        $categories = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $categories;
    }

    // Dùng collation của database để kiểm tra tên trùng.
    public function nameExists(string $name, int $exceptId = 0): bool
    {
        $statement = self::getConnection()->prepare('SELECT 1 FROM categories WHERE name = :name AND category_id <> :category_id LIMIT 1');
        $statement->bindValue(':name', $name, PDO::PARAM_STR);
        $statement->bindValue(':category_id', $exceptId, PDO::PARAM_INT);
        $statement->execute();
        $exists = $statement->fetchColumn() !== false;
        $statement->closeCursor();
        return $exists;
    }

    public function create(string $name, ?string $description, ?string $imagePath, ?string $cardLabelImagePath): void
    {
        $statement = self::getConnection()->prepare('INSERT INTO categories (name, description, image_path, card_label_image_path, is_active) VALUES (:name, :description, :image_path, :card_label_image_path, 1)');
        $statement->execute([':name' => $name, ':description' => $description, ':image_path' => $imagePath, ':card_label_image_path' => $cardLabelImagePath]);
        $statement->closeCursor();
    }

    public function update(int $id, string $name, ?string $description, ?string $imagePath, ?string $cardLabelImagePath): bool
    {
        $statement = self::getConnection()->prepare('UPDATE categories SET name = :name, description = :description, image_path = :image_path, card_label_image_path = :card_label_image_path WHERE category_id = :id');
        $statement->execute([':name' => $name, ':description' => $description, ':image_path' => $imagePath, ':card_label_image_path' => $cardLabelImagePath, ':id' => $id]);
        $statement->closeCursor();
        return $this->exists($id);
    }

    public function setStatus(int $id, int $active): bool
    {
        $statement = self::getConnection()->prepare('UPDATE categories SET is_active = :active WHERE category_id = :id');
        $statement->bindValue(':active', $active, PDO::PARAM_INT);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $statement->closeCursor();
        return $this->exists($id);
    }

    public function exists(int $id): bool
    {
        $statement = self::getConnection()->prepare('SELECT 1 FROM categories WHERE category_id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $exists = $statement->fetchColumn() !== false;
        $statement->closeCursor();
        return $exists;
    }
}
