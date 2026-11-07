<?php

declare(strict_types=1);

namespace HotSpot\Core;

final class AiSummaryCache
{
    private string $cacheDirectory;

    public function __construct()
    {
        $this->cacheDirectory = dirname(__DIR__, 2)
            . '/storage/cache/ai';
    }

    // Đọc bản tóm tắt còn hạn trong vòng một giờ.
    public function read(string $cacheKey): ?string
    {
        $filePath = $this->cacheDirectory
            . '/'
            . hash('sha256', $cacheKey)
            . '.json';

        if (is_file($filePath) === false) {
            return null;
        }

        $fileContent = file_get_contents($filePath);

        if ($fileContent === false) {
            return null;
        }

        $cacheData = json_decode($fileContent, true);

        if (is_array($cacheData) === false) {
            return null;
        }

        $createdAt = $cacheData['created_at'] ?? 0;
        $summary = $cacheData['summary'] ?? '';

        if (
            is_int($createdAt) === false
            || time() - $createdAt >= 3600
            || is_string($summary) === false
            || trim($summary) === ''
        ) {
            return null;
        }

        return $summary;
    }

    // Lưu bản tóm tắt để những yêu cầu sau có thể dùng lại.
    public function write(string $cacheKey, string $summary): bool
    {
        if (is_dir($this->cacheDirectory) === false) {
            $directoryCreated = mkdir(
                $this->cacheDirectory,
                0755,
                true
            );

            if ($directoryCreated === false) {
                return false;
            }
        }

        $filePath = $this->cacheDirectory
            . '/'
            . hash('sha256', $cacheKey)
            . '.json';

        $fileContent = json_encode([
            'created_at' => time(),
            'summary' => $summary,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $writtenBytes = file_put_contents(
            $filePath,
            $fileContent,
            LOCK_EX
        );

        return $writtenBytes !== false;
    }
}