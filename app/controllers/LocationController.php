<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Models\Province;
use HotSpot\Models\Ward;
use InvalidArgumentException;
use Throwable;

final class LocationController extends Controller
{
    public function wards(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');

        try {
            $provinceCode = $this->validateProvinceCode($_GET['province_code'] ?? null);

            $provinceModel = new Province();
            if ($provinceModel->existsProvinceCode($provinceCode) === false) {
                $this->respond(422, false, 'Tỉnh/thành không hợp lệ.', null, []);
                return;
            }

            $wardModel = new Ward();
            $wardList = $wardModel->findActiveByProvinceCode($provinceCode);

            $this->respond(
                200,
                true,
                'Lấy danh sách phường/xã thành công.',
                ['wards' => $wardList],
                []
            );
        } catch (InvalidArgumentException $exception) {
            $this->respond(422, false, $exception->getMessage(), null, []);
        } catch (Throwable $exception) {
            error_log('LocationController::wards error: ' . $exception->getMessage());
            $this->respond(500, false, 'Chưa thể tải danh sách phường/xã. Vui lòng thử lại.', null, []);
        }
    }

    private function validateProvinceCode(mixed $rawProvinceCode): string
    {
        if (is_string($rawProvinceCode) === false) {
            throw new InvalidArgumentException('Mã tỉnh/thành không hợp lệ.');
        }

        $provinceCode = trim($rawProvinceCode);

        if ($provinceCode === '' || strlen($provinceCode) > 10) {
            throw new InvalidArgumentException('Mã tỉnh/thành không hợp lệ.');
        }

        return $provinceCode;
    }

    private function respond(
        int $statusCode,
        bool $success,
        string $message,
        ?array $data,
        array $errors
    ): void {
        http_response_code($statusCode);

        echo json_encode(
            [
                'success' => $success,
                'message' => $message,
                'data' => $data,
                'errors' => $errors,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }
}
