<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\HttpException;
use HotSpot\Models\Place;
use HotSpot\Models\PlaceImage;
use HotSpot\Models\Review;
use HotSpot\Models\User;

final class PlaceController extends Controller
{
    // Ảnh minh họa theo danh mục, dùng khi bài chỉ có ảnh placeholder của seed.
    private const DEMO_IMAGE_BY_CATEGORY = [
        1 => 'assets/images/home/food.webp',
        2 => 'assets/images/home/coffee.webp',
        3 => 'assets/images/home/travel.webp',
        4 => 'assets/images/home/fun.webp',
        5 => 'assets/images/home/nature.webp',
        6 => 'assets/images/home/culture.webp',
    ];

    private const DEFAULT_DEMO_IMAGE = 'assets/images/home/travel.webp';
    private const SEED_PLACEHOLDER_IMAGE = 'assets/images/demo-placeholder.svg';

    // Hiển thị trang chi tiết: Guest chỉ xem APPROVED; chủ bài/Admin xem được bài chưa duyệt.
    public function show(): void
    {
        $placeId = $this->readPlaceIdFromQuery();

        if ($placeId === 0) {
            throw new HttpException(404, 'Không tìm thấy địa điểm.');
        }

        $placeModel = new Place();
        $placeInformation = $placeModel->findDetailById($placeId);

        if ($placeInformation === null) {
            throw new HttpException(404, 'Không tìm thấy địa điểm.');
        }

        // Người xem lấy từ session, không lấy từ URL.
        $userInformation = null;

        if (isset($_SESSION['user_id'])) {
            $userModel = new User();
            $userInformation = $userModel->findById((int) $_SESSION['user_id']);
        }

        $isApproved = $placeInformation['status_code'] === 'APPROVED';
        $isOwner = false;
        $isAdmin = false;

        if ($userInformation !== null) {
            if ((int) $userInformation['user_id'] === (int) $placeInformation['owner_id']) {
                $isOwner = true;
            }

            if ($userInformation['role_code'] === 'ADMIN') {
                $isAdmin = true;
            }
        }

        $reviewList = [];
        $currentReview = null;
        $isLoggedIn = $userInformation !== null;
        $canReview = $isApproved && $userInformation !== null
            && ($userInformation['role_code'] ?? '') === 'MEMBER';
        if ($isApproved) {
            $reviewModel = new Review();
            $reviewList = $reviewModel->findVisibleByPlaceId($placeId);
            if ($canReview) {
                $currentReview = $reviewModel->findForUserAndPlace((int) $userInformation['user_id'], $placeId);
            }
        }

        // Bài chưa công khai: chỉ chủ bài hoặc Admin được xem.
        if ($isApproved === false) {
            if ($isOwner === false && $isAdmin === false) {
                throw new HttpException(403, 'Địa điểm này chưa được công khai.');
            }
        }

        $placeImageModel = new PlaceImage();
        $imageList = $placeImageModel->findByPlaceId($placeId);
        $tagList = $placeModel->findTagsByPlaceId($placeId);

        $categoryId = (int) $placeInformation['category_id'];
        $imageUrlList = $this->buildImageUrlList($imageList, $categoryId);

        $hasLocation = false;

        // Chỉ coi là có vị trí khi cả vĩ độ và kinh độ đều có giá trị.
        if ($placeInformation['latitude'] !== null && $placeInformation['longitude'] !== null) {
            $hasLocation = true;
        }

        $viewData = [
            'title' => $placeInformation['title'],
            'place' => $placeInformation,
            'imageUrls' => $imageUrlList,
            'fallbackImageUrl' => $this->getDemoImage($categoryId),
            'tags' => $tagList,
            'hasLocation' => $hasLocation,
            'isApproved' => $isApproved,
            'isOwner' => $isOwner,
            'isAdmin' => $isAdmin,
            'userInformation' => $userInformation,
            'reviewList' => $reviewList,
            'currentReview' => $currentReview,
            'isLoggedIn' => $isLoggedIn,
            'canReview' => $canReview,
            'csrfToken' => (string) ($_SESSION['csrf_token'] ?? ''),
            'placeId' => $placeId,
        ];

        $this->render('places/show', $viewData);
    }

    // Đọc id từ query string; trả về 0 nếu không phải số nguyên dương.
    private function readPlaceIdFromQuery(): int
    {
        if (isset($_GET['id']) === false) {
            return 0;
        }

        if (is_string($_GET['id']) === false) {
            return 0;
        }

        $rawPlaceId = trim($_GET['id']);

        if ($rawPlaceId === '' || ctype_digit($rawPlaceId) === false) {
            return 0;
        }

        // Giới hạn độ dài để tránh số quá lớn khi ép kiểu.
        if (strlen($rawPlaceId) > 18) {
            return 0;
        }

        $placeId = (int) $rawPlaceId;

        if ($placeId <= 0) {
            return 0;
        }

        return $placeId;
    }

    // Chuyển danh sách ảnh trong database thành URL an toàn; thay placeholder bằng ảnh minh họa.
    private function buildImageUrlList(array $imageList, int $categoryId): array
    {
        $imageUrlList = [];
        $demoImage = $this->getDemoImage($categoryId);

        foreach ($imageList as $imageInformation) {
            $imagePath = trim((string) $imageInformation['image_path']);
            $imageUrl = $demoImage;

            if ($imagePath !== '' && $imagePath !== self::SEED_PLACEHOLDER_IMAGE) {
                if ($this->isSafeImagePath($imagePath)) {
                    $imageUrl = $imagePath;
                }
            }

            $imageUrlList[] = $imageUrl;
        }

        // Bài không có ảnh nào vẫn hiển thị một ảnh minh họa.
        if (count($imageUrlList) === 0) {
            $imageUrlList[] = $demoImage;
        }

        return $imageUrlList;
    }

    // Chỉ chấp nhận đường dẫn nội bộ trong assets/uploads hoặc URL http(s) hợp lệ.
    private function isSafeImagePath(string $imagePath): bool
    {
        $isLocalPath = preg_match('#^(assets|uploads)/[A-Za-z0-9._/-]+$#', $imagePath) === 1;

        if ($isLocalPath && strpos($imagePath, '..') === false) {
            return true;
        }

        $isRemotePath = preg_match('#^https?://#i', $imagePath) === 1;

        if ($isRemotePath && filter_var($imagePath, FILTER_VALIDATE_URL) !== false) {
            return true;
        }

        return false;
    }

    // Lấy ảnh minh họa theo danh mục; danh mục lạ dùng ảnh mặc định.
    private function getDemoImage(int $categoryId): string
    {
        if (isset(self::DEMO_IMAGE_BY_CATEGORY[$categoryId])) {
            return self::DEMO_IMAGE_BY_CATEGORY[$categoryId];
        }

        return self::DEFAULT_DEMO_IMAGE;
    }
}
