<?php
declare(strict_types=1);

namespace HotSpot\Controllers;

use HotSpot\Core\Controller;
use HotSpot\Core\HttpException;
use HotSpot\Core\Database;
use HotSpot\Models\Place;
use HotSpot\Models\PlaceImage;
use HotSpot\Models\PlaceTag;
use HotSpot\Models\Category;
use HotSpot\Models\Province;
use HotSpot\Models\Tag;
use HotSpot\Models\Review;
use HotSpot\Models\User;
use HotSpot\Models\Favorite;

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

        // Chuẩn bị dữ liệu cho widget yêu thích của Khang.
        $favoriteWidget = [
            'place_id' => $placeId,
            'is_favorited' => false,
            'is_logged_in' => $userInformation !== null,
            'csrf_token' => '',
        ];

        if ($isApproved && $userInformation !== null) {
            if (isset($_SESSION['csrf_token']) === false) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            $favoriteModel = new Favorite();

            $favoriteWidget['is_favorited'] = $favoriteModel->isFavorited(
                (int) $userInformation['user_id'],
                $placeId
            );

            $favoriteWidget['csrf_token'] = $_SESSION['csrf_token'];
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
            'favoriteWidget' => $favoriteWidget,
        ];

        $this->render('places/show', $viewData);
    }

    // Hiển thị form đăng địa điểm mới (TV3-02): bắt buộc đăng nhập.
    public function create(): void
    {
        $userInformation = $this->requirePlaceAuthor();
        $this->renderCreateForm($userInformation);
    }

    // Đăng bài, ảnh và nhãn trong cùng một giao dịch; lỗi ở bước nào cũng hoàn tác.
    public function store(): void
    {
        $userInformation = $this->requirePlaceAuthor();
        $submittedToken = $_POST['csrf_token'] ?? null;
        if (is_string($submittedToken) === false
            || isset($_SESSION['csrf_token']) === false
            || is_string($_SESSION['csrf_token']) === false
            || hash_equals($_SESSION['csrf_token'], $submittedToken) === false) {
            throw new HttpException(403, 'Phiên biểu mẫu không hợp lệ. Vui lòng tải lại trang.');
        }

        $allowedFields = ['csrf_token', 'title', 'description', 'category_id', 'province_code',
            'ward_code', 'address', 'latitude', 'longitude', 'tag_ids'];
        foreach (array_keys($_POST) as $fieldName) {
            if (in_array($fieldName, $allowedFields, true) === false) {
                throw new HttpException(400, 'Biểu mẫu có trường không được hỗ trợ.');
            }
        }
        foreach (array_keys($_FILES) as $fileField) {
            if ($fileField !== 'images') {
                throw new HttpException(400, 'Trường tải ảnh không hợp lệ.');
            }
        }

        $formErrors = [];
        $formValues = $this->readPlaceFormValues($formErrors);
        $placeData = $this->validatePlaceFormValues($formValues, $formErrors);
        $placeImageModel = new PlaceImage();
        $placeTagModel = new PlaceTag();
        $imageInput = [];
        if (isset($_FILES['images']) && is_array($_FILES['images'])) {
            $imageInput = $_FILES['images'];
        }

        // Giữ mặc định kiểm tra HTTP upload/MIME thật và nhãn đang hoạt động.
        $imageValidation = $placeImageModel->validateUploadBatch($imageInput);
        $tagValidation = $placeTagModel->validateAndNormalizeTagIds($_POST['tag_ids'] ?? null);
        $formErrors = array_merge($formErrors, $imageValidation['errors'], $tagValidation['errors']);

        if (count($formErrors) > 0) {
            http_response_code(422);
            $this->renderCreateForm($userInformation, $formValues, $tagValidation['tag_ids'], $formErrors);
            return;
        }

        $databaseConnection = Database::getConnection();
        $savedFiles = [];
        try {
            $databaseConnection->beginTransaction();
            $placeModel = new Place();
            $placeId = $placeModel->submitPendingPlace((int) $userInformation['user_id'], $placeData);
            $savedFiles = $placeImageModel->saveValidatedFiles($imageValidation['files']);

            foreach ($savedFiles as $savedFile) {
                $placeImageModel->addPlaceImage(
                    (int) $userInformation['user_id'],
                    $placeId,
                    $savedFile['image_path'],
                    $savedFile['sort_order']
                );
            }
            $placeTagModel->attachTagsToPlace($placeId, $tagValidation['tag_ids']);
            $databaseConnection->commit();
        } catch (\Throwable $exception) {
            try {
                if ($databaseConnection->inTransaction()) {
                    $databaseConnection->rollBack();
                }
            } catch (\Throwable $rollbackException) {
                error_log('PlaceController::store rollback failed: ' . $rollbackException->getMessage());
            }
            $undeletedPaths = $placeImageModel->cleanupSavedFiles($savedFiles);
            if (count($undeletedPaths) > 0) {
                error_log('PlaceController::store incomplete cleanup: ' . implode(', ', $undeletedPaths));
            }
            error_log('PlaceController::store failed: ' . $exception->getMessage());

            $errorStatus = 500;
            $errorMessage = 'Chưa thể lưu địa điểm. Vui lòng thử lại sau.';
            if ($exception instanceof \PDOException && $exception->getCode() === '45000') {
                $errorStatus = 422;
                $errorMessage = 'Dữ liệu địa điểm không còn hợp lệ. Vui lòng kiểm tra lại danh mục và khu vực đã chọn.';
            }
            if ($exception instanceof \InvalidArgumentException) {
                $errorStatus = 422;
                $errorMessage = 'Nhãn đã chọn không còn hợp lệ. Vui lòng chọn lại nhãn.';
            }
            http_response_code($errorStatus);
            $this->renderCreateForm($userInformation, $formValues, $tagValidation['tag_ids'], [$errorMessage]);
            return;
        }

        $_SESSION['place_success_message'] = 'Địa điểm đã được gửi thành công và đang chờ duyệt.';
        header('Location: ' . createRouteUrl('places/mine'), true, 303);
    }

    // Lấy người đăng từ session; không nhận owner/status hay cờ bỏ qua kiểm tra từ request.
    private function requirePlaceAuthor(): array
    {
        if (isset($_SESSION['user_id']) === false) {
            throw new HttpException(401, 'Vui lòng đăng nhập để đăng địa điểm.');
        }

        $sessionUserId = $_SESSION['user_id'];
        if (is_int($sessionUserId) === false && is_string($sessionUserId) === false) {
            throw new HttpException(401, 'Phiên đăng nhập không hợp lệ.');
        }
        $userId = filter_var($sessionUserId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($userId === false) {
            throw new HttpException(401, 'Phiên đăng nhập không hợp lệ.');
        }
        $userModel = new User();
        $userInformation = $userModel->findById($userId);

        if ($userInformation === null) {
            throw new HttpException(401, 'Tài khoản không tồn tại. Vui lòng đăng nhập lại.');
        }

        if ($userInformation['role_code'] !== 'MEMBER') {
            throw new HttpException(403, 'Chỉ thành viên được đăng địa điểm.');
        }

        if (isset($_SESSION['csrf_token']) === false) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $userInformation;
    }

    // Tái sử dụng form khi mở mới hoặc trả lỗi; không giữ file upload trong session.
    private function renderCreateForm(
        array $userInformation,
        array $formValues = [],
        array $selectedTagIds = [],
        array $formErrors = []
    ): void {

        // C02: tái sử dụng nguồn dữ liệu thật của TV2, chỉ chọn danh mục/nhãn đang hoạt động.
        $categories = [];
        $provinces = [];
        $wards = [];
        $tags = [];
        $dataLoadErrors = [];

        try {
            $categoryModel = new Category();
            $categories = $categoryModel->findActiveCategories();
        } catch (\Throwable $exception) {
            error_log('PlaceController::create categories load error: ' . $exception->getMessage());
            $dataLoadErrors[] = 'Chưa tải được danh mục địa điểm. Vui lòng tải lại trang để thử lại.';
        }

        try {
            $provinceModel = new Province();
            $provinces = $provinceModel->findAllProvinces();
        } catch (\Throwable $exception) {
            error_log('PlaceController::create provinces load error: ' . $exception->getMessage());
            $dataLoadErrors[] = 'Chưa tải được tỉnh/thành phố. Vui lòng tải lại trang để thử lại.';
        }

        try {
            $tagModel = new Tag();
            $tags = $tagModel->findActiveTags();
        } catch (\Throwable $exception) {
            error_log('PlaceController::create tags load error: ' . $exception->getMessage());
            $dataLoadErrors[] = 'Chưa tải được danh sách nhãn. Bạn có thể để trống nhãn hoặc tải lại trang.';
        }

        $viewData = [
            'title' => 'Đăng địa điểm mới',
            'userInformation' => $userInformation,
            'csrfToken' => $_SESSION['csrf_token'],
            'categories' => $categories,
            'provinces' => $provinces,
            'wards' => $wards,
            'tags' => $tags,
            'dataLoadErrors' => $dataLoadErrors,
            'formValues' => $formValues,
            'selectedTagIds' => $selectedTagIds,
            'formErrors' => $formErrors,
            'isStoreReady' => count($categories) > 0 && count($provinces) > 0,
        ];

        $this->render('places/form', $viewData);
    }

    // Chỉ nhận chuỗi UTF-8 ở các trường văn bản; giữ nội dung hợp lệ để điền lại form.
    private function readPlaceFormValues(array &$formErrors): array
    {
        $formValues = [];
        $fieldNames = ['title', 'description', 'category_id', 'province_code', 'ward_code',
            'address', 'latitude', 'longitude'];
        foreach ($fieldNames as $fieldName) {
            $rawValue = $_POST[$fieldName] ?? '';
            $formValues[$fieldName] = '';
            if (is_string($rawValue) === false || mb_check_encoding($rawValue, 'UTF-8') === false) {
                $formErrors[] = 'Dữ liệu trường ' . $fieldName . ' không đúng định dạng.';
            } else {
                $formValues[$fieldName] = trim($rawValue);
            }
        }
        return $formValues;
    }

    // Kiểm tra giới hạn của form và cặp tọa độ; routine kiểm tra danh mục/khu vực trong giao dịch.
    private function validatePlaceFormValues(array $formValues, array &$formErrors): array
    {
        $textFields = [
            'title' => ['Tên địa điểm', 100],
            'description' => ['Mô tả', 1000],
            'address' => ['Địa chỉ', 300],
            'province_code' => ['Tỉnh/thành', 10],
        ];
        foreach ($textFields as $fieldName => $fieldRules) {
            if ($formValues[$fieldName] === '') {
                $formErrors[] = $fieldRules[0] . ' là thông tin bắt buộc.';
            } elseif (mb_strlen($formValues[$fieldName], 'UTF-8') > $fieldRules[1]) {
                $formErrors[] = $fieldRules[0] . ' vượt quá độ dài cho phép.';
            }
        }
        if (mb_strlen($formValues['ward_code'], 'UTF-8') > 10) {
            $formErrors[] = 'Mã phường/xã không hợp lệ.';
        }
        $categoryId = filter_var($formValues['category_id'], FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
        if ($categoryId === false) {
            $formErrors[] = 'Vui lòng chọn danh mục hợp lệ.';
        }

        $latitude = null;
        $longitude = null;
        if ($formValues['latitude'] !== '' || $formValues['longitude'] !== '') {
            if ($formValues['latitude'] === '' || $formValues['longitude'] === ''
                || is_numeric($formValues['latitude']) === false || is_numeric($formValues['longitude']) === false) {
                $formErrors[] = 'Vĩ độ và kinh độ phải cùng để trống hoặc cùng là số hợp lệ.';
            } else {
                $latitudeNumber = (float) $formValues['latitude'];
                $longitudeNumber = (float) $formValues['longitude'];
                if (is_finite($latitudeNumber) === false || is_finite($longitudeNumber) === false
                    || $latitudeNumber < -90 || $latitudeNumber > 90
                    || $longitudeNumber < -180 || $longitudeNumber > 180
                    || ($latitudeNumber == 0 && $longitudeNumber == 0)) {
                    $formErrors[] = 'Tọa độ ngoài phạm vi hợp lệ hoặc là tọa độ giả (0, 0).';
                } else {
                    $latitude = number_format($latitudeNumber, 7, '.', '');
                    $longitude = number_format($longitudeNumber, 7, '.', '');
                    if ((float) $latitude == 0 && (float) $longitude == 0) {
                        $formErrors[] = 'Tọa độ quá nhỏ và trở thành (0, 0) khi lưu. Vui lòng để trống nếu chưa có vị trí.';
                    }
                }
            }
        }

        $placeData = $formValues;
        $placeData['category_id'] = $categoryId;
        $placeData['latitude'] = $latitude;
        $placeData['longitude'] = $longitude;
        $placeData['ward_code'] = null;
        if ($formValues['ward_code'] !== '') {
            $placeData['ward_code'] = $formValues['ward_code'];
        }
        return $placeData;
    }

    // Hiển thị danh sách địa điểm của tài khoản đang đăng nhập (TV3-05, hợp đồng C08).
    public function mine(): void
    {
        if (isset($_SESSION['user_id']) === false) {
            throw new HttpException(401, 'Vui lòng đăng nhập để xem địa điểm đã đăng.');
        }

        // Chủ bài luôn lấy từ session, không nhận từ URL/request.
        $ownerId = (int) $_SESSION['user_id'];
        $userModel = new User();
        $userInformation = $userModel->findById($ownerId);

        if ($userInformation === null) {
            throw new HttpException(401, 'Tài khoản không tồn tại. Vui lòng đăng nhập lại.');
        }

        if (isset($_SESSION['csrf_token']) === false) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $successMessage = '';
        if (isset($_SESSION['place_success_message']) && is_string($_SESSION['place_success_message'])) {
            $successMessage = $_SESSION['place_success_message'];
        }
        unset($_SESSION['place_success_message']);

        $allowedStatusFilters = ['ALL', 'PENDING', 'APPROVED', 'REJECTED', 'HIDDEN'];
        $activeStatusFilter = 'ALL';

        if (isset($_GET['status']) && is_string($_GET['status'])) {
            $requestedStatus = strtoupper(trim($_GET['status']));
            if (in_array($requestedStatus, $allowedStatusFilters, true)) {
                $activeStatusFilter = $requestedStatus;
            }
        }

        $placeModel = new Place();
        $rawPlaceList = $placeModel->findByOwnerId($ownerId);

        $myPlaceList = [];
        $statusCounts = [
            'total' => 0,
            'PENDING' => 0,
            'APPROVED' => 0,
            'REJECTED' => 0,
            'HIDDEN' => 0,
        ];

        foreach ($rawPlaceList as $placeItem) {
            $categoryId = (int) $placeItem['category_id'];
            $fallbackImageUrl = $this->getDemoImage($categoryId);
            $rawCoverImage = trim((string) ($placeItem['cover_image'] ?? ''));
            $coverImageUrl = $fallbackImageUrl;

            if ($rawCoverImage !== '' && $rawCoverImage !== self::SEED_PLACEHOLDER_IMAGE) {
                if ($this->isSafeImagePath($rawCoverImage)) {
                    $coverImageUrl = $rawCoverImage;
                }
            }

            $statusCode = (string) ($placeItem['status_code'] ?? '');
            $statusCounts['total'] = $statusCounts['total'] + 1;

            if (isset($statusCounts[$statusCode])) {
                $statusCounts[$statusCode] = $statusCounts[$statusCode] + 1;
            }

            if ($activeStatusFilter !== 'ALL' && $statusCode !== $activeStatusFilter) {
                continue;
            }

            $placeItem['cover_image_url'] = $coverImageUrl;
            $placeItem['fallback_image_url'] = $fallbackImageUrl;
            $myPlaceList[] = $placeItem;
        }

        $viewData = [
            'title' => 'Địa điểm của tôi',
            'userInformation' => $userInformation,
            'csrfToken' => (string) $_SESSION['csrf_token'],
            'myPlaces' => $myPlaceList,
            'successMessage' => $successMessage,
            'statusCounts' => $statusCounts,
            'activeStatusFilter' => $activeStatusFilter,
        ];

        $this->render('places/mine', $viewData);
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
