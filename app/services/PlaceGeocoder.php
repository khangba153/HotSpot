<?php
declare(strict_types=1);

namespace HotSpot\Services;

use HotSpot\Core\HttpException;

final class PlaceGeocoder
{
    private string $cacheDirectory;

    public function __construct()
    {
        $this->cacheDirectory = dirname(__DIR__, 2) . '/storage/cache/geocoding';
    }

    // Cache chung cho mọi thành viên; khóa toàn ứng dụng để không vượt 1 request/giây.
    public function search(string $query): array
    {
        $endpoint = getenv('HOTSPOT_GEOCODE_ENDPOINT');
        if ($endpoint === false || $endpoint === '' || $endpoint === '0') {
            $endpoint = 'https://nominatim.openstreetmap.org/search';
        }
        $endpointParts = parse_url($endpoint);
        if (filter_var($endpoint, FILTER_VALIDATE_URL) === false || ($endpointParts['scheme'] ?? '') !== 'https'
            || isset($endpointParts['user']) || isset($endpointParts['pass']) || isset($endpointParts['query']) || isset($endpointParts['fragment'])) {
            throw new HttpException(503, 'Dịch vụ tìm vị trí chưa được cấu hình hợp lệ. Bạn có thể nhập hoặc bỏ trống tọa độ.');
        }
        if (!is_dir($this->cacheDirectory) && !@mkdir($this->cacheDirectory, 0700, true) && !is_dir($this->cacheDirectory)) {
            throw new HttpException(503, 'Chưa thể tìm vị trí. Bạn vẫn có thể nhập hoặc bỏ trống tọa độ.');
        }
        $cachePath = $this->cacheDirectory . '/' . hash('sha256', $endpoint . '|vi|vn|' . mb_strtolower($query, 'UTF-8')) . '.json';
        $cached = $this->readCache($cachePath);
        if ($cached !== null) {
            return $cached;
        }

        $lock = @fopen($this->cacheDirectory . '/requests.lock', 'c+');
        if ($lock === false) {
            throw new HttpException(503, 'Chưa thể tìm vị trí. Bạn vẫn có thể lưu bài không có tọa độ.');
        }
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            throw new HttpException(429, 'Đang có lượt tìm vị trí khác. Vui lòng thử lại sau vài giây.');
        }
        try {
            $cached = $this->readCache($cachePath);
            if ($cached !== null) {
                return $cached;
            }
            rewind($lock);
            $lastRequest = (float) stream_get_contents($lock);
            if (microtime(true) - $lastRequest < 1.1) {
                throw new HttpException(429, 'Vui lòng chờ vài giây trước khi tìm vị trí tiếp.');
            }
            rewind($lock);
            ftruncate($lock, 0);
            if (fwrite($lock, (string) microtime(true)) === false || !fflush($lock)) {
                throw new HttpException(503, 'Chưa thể tìm vị trí. Bạn vẫn có thể lưu bài không có tọa độ.');
            }
            $results = $this->requestResults($endpoint, $query);
            $cacheLifetimeSeconds = 3600;
            if (count($results) > 0) {
                $cacheLifetimeSeconds = 86400;
            }
            @file_put_contents($cachePath, json_encode(['expires' => time() + $cacheLifetimeSeconds, 'results' => $results], JSON_UNESCAPED_UNICODE), LOCK_EX);
            $this->pruneCache();
            return $results;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    // Đọc kết quả còn hạn; cache hỏng được coi như chưa có kết quả.
    private function readCache(string $path): ?array
    {
        if (!is_file($path)) {
            return null;
        }
        $value = json_decode((string) @file_get_contents($path), true);
        if (!is_array($value) || !is_int($value['expires'] ?? null) || $value['expires'] <= time()
            || !is_array($value['results'] ?? null)) {
            return null;
        }
        return $value['results'];
    }

    // HTTPS giữ kiểm tra chứng chỉ; không theo redirect hoặc nhận URL từ request của người dùng.
    private function requestResults(string $endpoint, string $query): array
    {
        $parameters = http_build_query(['q' => $query, 'format' => 'jsonv2', 'countrycodes' => 'vn',
            'limit' => 3, 'accept-language' => 'vi'], '', '&', PHP_QUERY_RFC3986);
        $request = curl_init($endpoint . '?' . $parameters);
        curl_setopt_array($request, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'HotSpot/1.0 (+https://github.com/khangba153/HotSpot)',
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_MAXFILESIZE => 262144,
        ]);
        try {
            $body = curl_exec($request);
            $status = (int) curl_getinfo($request, CURLINFO_HTTP_CODE);
            if ($body === false) {
                throw new HttpException(503, 'Chưa kết nối được dịch vụ tìm vị trí. Bạn có thể chọn trên bản đồ, nhập hoặc bỏ trống tọa độ.');
            }
            if ($status === 429) {
                throw new HttpException(429, 'Dịch vụ tìm vị trí đang bận. Vui lòng thử lại sau; bạn vẫn có thể lưu bài.');
            }
            $rows = json_decode($body, true);
            if ($status !== 200 || strlen($body) > 262144 || !is_array($rows) || !array_is_list($rows)) {
                throw new HttpException(502, 'Dịch vụ tìm vị trí chưa trả kết quả hợp lệ. Bạn vẫn có thể lưu bài không có tọa độ.');
            }
            $results = [];
            foreach (array_slice($rows, 0, 3) as $row) {
                if (!is_array($row) || !is_numeric($row['lat'] ?? null) || !is_numeric($row['lon'] ?? null)
                    || !is_string($row['display_name'] ?? null)) {
                    continue;
                }
                $latitude = round((float) $row['lat'], 7);
                $longitude = round((float) $row['lon'], 7);
                if (!is_finite($latitude) || !is_finite($longitude) || $latitude < -90 || $latitude > 90
                    || $longitude < -180 || $longitude > 180 || ($latitude == 0 && $longitude == 0)) {
                    continue;
                }
                $results[] = ['latitude' => $latitude, 'longitude' => $longitude,
                    'label' => mb_substr($row['display_name'], 0, 500, 'UTF-8')];
            }
            if (count($rows) > 0 && count($results) === 0) {
                throw new HttpException(502, 'Kết quả tìm kiếm không có tọa độ hợp lệ. Bạn có thể nhập hoặc bỏ trống tọa độ.');
            }
            return $results;
        } finally {
            curl_close($request);
        }
    }

    // Giới hạn 200 kết quả cache; xóa mục hết hạn trước rồi mục cũ nhất.
    private function pruneCache(): void
    {
        $paths = glob($this->cacheDirectory . '/*.json');
        if ($paths === false) {
            $paths = [];
        }
        $active = [];
        foreach ($paths as $path) {
            if ($this->readCache($path) === null) {
                @unlink($path);
            } else {
                $active[$path] = (int) filemtime($path);
            }
        }
        asort($active);
        while (count($active) > 200) {
            $path = array_key_first($active);
            @unlink($path);
            unset($active[$path]);
        }
    }
}
