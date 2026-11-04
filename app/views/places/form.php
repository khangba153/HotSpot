<?php
/** @var array<string, mixed> $viewData Dữ liệu từ Controller. */

$pageTitle = 'Đăng địa điểm mới';
if (isset($viewData['title']) && is_string($viewData['title']) && trim($viewData['title']) !== '') {
    $pageTitle = trim($viewData['title']);
}

$csrfToken = '';
if (isset($viewData['csrfToken']) && is_string($viewData['csrfToken'])) {
    $csrfToken = $viewData['csrfToken'];
}

$categories = [];
if (isset($viewData['categories']) && is_array($viewData['categories'])) {
    $categories = $viewData['categories'];
}

$provinces = [];
if (isset($viewData['provinces']) && is_array($viewData['provinces'])) {
    $provinces = $viewData['provinces'];
}

$wards = [];
if (isset($viewData['wards']) && is_array($viewData['wards'])) {
    $wards = $viewData['wards'];
}

$tags = [];
if (isset($viewData['tags']) && is_array($viewData['tags'])) {
    $tags = $viewData['tags'];
}

$formValues = [
    'title' => '',
    'description' => '',
    'category_id' => '',
    'province_code' => '',
    'ward_code' => '',
    'address' => '',
    'latitude' => '',
    'longitude' => '',
];

if (isset($viewData['formValues']) && is_array($viewData['formValues'])) {
    foreach ($formValues as $fieldKey => $defaultValue) {
        if (isset($viewData['formValues'][$fieldKey]) && is_scalar($viewData['formValues'][$fieldKey])) {
            $formValues[$fieldKey] = trim((string) $viewData['formValues'][$fieldKey]);
        }
    }
}

$selectedTagIds = [];
if (isset($viewData['selectedTagIds']) && is_array($viewData['selectedTagIds'])) {
    foreach ($viewData['selectedTagIds'] as $rawTagId) {
        if (is_numeric($rawTagId)) {
            $tagIdValue = (int) $rawTagId;
            if ($tagIdValue > 0 && in_array($tagIdValue, $selectedTagIds, true) === false) {
                $selectedTagIds[] = $tagIdValue;
            }
        }
    }
}

$formErrors = [];
if (isset($viewData['formErrors']) && is_array($viewData['formErrors'])) {
    $formErrors = $viewData['formErrors'];
}

$formMessage = '';
if (isset($viewData['formMessage']) && is_string($viewData['formMessage'])) {
    $formMessage = trim($viewData['formMessage']);
}

$formMessageType = 'warning';
if (isset($viewData['formMessageType']) && is_string($viewData['formMessageType'])) {
    if (in_array($viewData['formMessageType'], ['success', 'danger', 'warning', 'info'], true)) {
        $formMessageType = $viewData['formMessageType'];
    }
}

$missingDependencies = [];
if (isset($viewData['missingDependencies']) && is_array($viewData['missingDependencies'])) {
    $missingDependencies = $viewData['missingDependencies'];
}

$isPreviewSample = false;
if (isset($viewData['isPreviewSample']) && $viewData['isPreviewSample'] === true) {
    $isPreviewSample = true;
}

$isStoreReady = false;
if (isset($viewData['isStoreReady']) && $viewData['isStoreReady'] === true) {
    $isStoreReady = true;
}

$homePageUrl = createRouteUrl('home');
$createPageUrl = createRouteUrl('places/create');
$previewSampleUrl = $createPageUrl . '&preview=1';
$formActionUrl = $createPageUrl;
$storeReadyAttribute = 'false';
if ($isStoreReady) {
    $formActionUrl = createRouteUrl('places/store');
    $storeReadyAttribute = 'true';
}
if ($isStoreReady && isset($viewData['formAction']) && is_string($viewData['formAction']) && trim($viewData['formAction']) !== '') {
    $formActionUrl = trim($viewData['formAction']);
}

$wardsApiUrl = createRouteUrl('api/locations/wards');
if (isset($viewData['wardsApiUrl']) && is_string($viewData['wardsApiUrl']) && trim($viewData['wardsApiUrl']) !== '') {
    $wardsApiUrl = trim($viewData['wardsApiUrl']);
}

if (function_exists('mb_strlen')) {
    $initialTitleLength = mb_strlen($formValues['title'], 'UTF-8');
    $initialDescriptionLength = mb_strlen($formValues['description'], 'UTF-8');
} else {
    $initialTitleLength = strlen($formValues['title']);
    $initialDescriptionLength = strlen($formValues['description']);
}
?>
<section class="hs-place-form-hero" aria-labelledby="place-form-hero-title">
    <div class="hs-place-form-hero__overlay" aria-hidden="true"></div>
    <div class="container hs-place-form-hero__inner">
        <div class="hs-place-form-hero__content">
            <nav class="hs-place-form-breadcrumb" aria-label="Đường dẫn">
                <a href="<?php echo escapeHtml($homePageUrl); ?>">Trang chủ</a>
                <span aria-hidden="true">›</span>
                <span aria-current="page"><?php echo escapeHtml($pageTitle); ?></span>
            </nav>
            <h1 id="place-form-hero-title" class="hs-place-form-hero__title">
                <?php echo escapeHtml($pageTitle); ?>
            </h1>
            <p class="hs-place-form-hero__subtitle">
                Chia sẻ những địa điểm thú vị, góp phần làm phong phú cộng đồng du lịch Việt Nam
            </p>
        </div>
        <p class="hs-place-form-hero__slogan" aria-hidden="true">
            Cùng nhau khám phá<br>Việt Nam đẹp hơn!
        </p>
    </div>
</section>

<section class="hs-place-form-page">
    <div class="container">
        <?php if ($isPreviewSample) { ?>
            <div class="alert alert-warning hs-place-form-banner" role="status">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <strong>Chế độ xem thử giao diện (Dữ liệu mẫu):</strong>
                        Danh mục, tỉnh/thành phố và nhãn đang hiển thị bên dưới là dữ liệu mẫu phục vụ kiểm tra biểu mẫu, <strong>không phải dữ liệu thật từ hệ thống</strong>.
                    </div>
                    <a class="btn btn-sm btn-outline-dark" href="<?php echo escapeHtml($createPageUrl); ?>">
                        Tắt dữ liệu mẫu
                    </a>
                </div>
            </div>
        <?php } elseif (count($missingDependencies) > 0) { ?>
            <div class="alert alert-info hs-place-form-banner" role="status">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <strong>Thông báo dữ liệu:</strong>
                        <?php echo escapeHtml(implode(' ', $missingDependencies)); ?>
                    </div>
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo escapeHtml($previewSampleUrl); ?>">
                        Xem thử với dữ liệu mẫu
                    </a>
                </div>
            </div>
        <?php } ?>

        <?php if ($formMessage !== '') { ?>
            <div class="alert alert-<?php echo escapeHtml($formMessageType); ?> hs-place-form-banner" role="alert">
                <?php echo escapeHtml($formMessage); ?>
            </div>
        <?php } ?>

        <?php if (count($formErrors) > 0) { ?>
            <div class="alert alert-danger hs-place-form-banner" role="alert">
                <strong>Vui lòng kiểm tra lại thông tin đã nhập:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    <?php foreach ($formErrors as $errorText) { ?>
                        <?php if (is_string($errorText) && trim($errorText) !== '') { ?>
                            <li><?php echo escapeHtml($errorText); ?></li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>
        <?php } ?>

        <div
            id="place-form-status"
            class="alert hs-place-form-banner"
            role="status"
            aria-live="polite"
            hidden></div>

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <form
                    id="place-create-form"
                    class="hs-place-form"
                    method="post"
                    action="<?php echo escapeHtml($formActionUrl); ?>"
                    enctype="multipart/form-data"
                    data-wards-url="<?php echo escapeHtml($wardsApiUrl); ?>"
                    data-store-ready="<?php echo $storeReadyAttribute; ?>"
                    novalidate>

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo escapeHtml($csrfToken); ?>">

                    <!-- Bước 1: Thông tin cơ bản -->
                    <section class="hs-place-form-card" aria-labelledby="place-step-basic">
                        <div class="hs-place-form-card__header">
                            <span class="hs-place-form-step" aria-hidden="true">1</span>
                            <h2 id="place-step-basic" class="hs-place-form-card__title">Thông tin cơ bản</h2>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="place-title" class="form-label hs-place-form__label">
                                    Tên địa điểm <span class="hs-place-form__required" aria-hidden="true">*</span>
                                </label>
                                <div class="hs-place-form__input-wrap">
                                    <input
                                        type="text"
                                        id="place-title"
                                        name="title"
                                        class="form-control hs-place-form__control"
                                        value="<?php echo escapeHtml($formValues['title']); ?>"
                                        maxlength="100"
                                        placeholder="Nhập tên địa điểm (ví dụ: Tiệm Cà Phê Bình Yên)"
                                        required
                                        aria-describedby="place-title-counter place-title-error">
                                </div>
                                <div class="hs-place-form__meta-row">
                                    <p
                                        id="place-title-error"
                                        class="hs-place-form__field-error"
                                        role="alert"
                                        hidden></p>
                                    <small id="place-title-counter" class="hs-place-form__counter">
                                        <span data-place-title-count><?php echo $initialTitleLength; ?></span>/100
                                    </small>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="place-description" class="form-label hs-place-form__label">
                                    Mô tả <span class="hs-place-form__required" aria-hidden="true">*</span>
                                </label>
                                <textarea
                                    id="place-description"
                                    name="description"
                                    class="form-control hs-place-form__control hs-place-form__textarea"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Giới thiệu ngắn gọn về địa điểm, điểm đặc biệt, trải nghiệm nổi bật,..."
                                    required
                                    aria-describedby="place-description-counter place-description-error"><?php echo escapeHtml($formValues['description']); ?></textarea>
                                <div class="hs-place-form__meta-row">
                                    <p
                                        id="place-description-error"
                                        class="hs-place-form__field-error"
                                        role="alert"
                                        hidden></p>
                                    <small id="place-description-counter" class="hs-place-form__counter">
                                        <span data-place-description-count><?php echo $initialDescriptionLength; ?></span>/1000
                                    </small>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Bước 2: Danh mục và nhãn -->
                    <section class="hs-place-form-card" aria-labelledby="place-step-category">
                        <div class="hs-place-form-card__header">
                            <span class="hs-place-form-step" aria-hidden="true">2</span>
                            <h2 id="place-step-category" class="hs-place-form-card__title">Danh mục và nhãn</h2>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="place-category" class="form-label hs-place-form__label">
                                    Danh mục <span class="hs-place-form__required" aria-hidden="true">*</span>
                                </label>
                                <select
                                    id="place-category"
                                    name="category_id"
                                    class="form-select hs-place-form__control"
                                    required
                                    aria-describedby="place-category-error">
                                    <option value="">Chọn danh mục phù hợp</option>
                                    <?php foreach ($categories as $categoryItem) {
                                        if (is_array($categoryItem) === false) {
                                            continue;
                                        }
                                        $rawCategoryId = $categoryItem['category_id'] ?? '';
                                        $categoryName = trim((string) ($categoryItem['name'] ?? ''));
                                        if ($rawCategoryId === '' || $categoryName === '') {
                                            continue;
                                        }
                                        $categoryIdString = (string) (int) $rawCategoryId;
                                        $isSelectedCategory = $formValues['category_id'] === $categoryIdString;
                                    ?>
                                        <option
                                            value="<?php echo escapeHtml($categoryIdString); ?>"
                                            <?php if ($isSelectedCategory) { echo 'selected'; } ?>>
                                            <?php echo escapeHtml($categoryName); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <p
                                    id="place-category-error"
                                    class="hs-place-form__field-error mt-1"
                                    role="alert"
                                    hidden></p>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label for="place-tag-filter" class="form-label hs-place-form__label mb-0">
                                        Nhãn <span class="hs-place-form__optional">(tối đa 5 nhãn có sẵn)</span>
                                    </label>
                                    <small class="hs-place-form__counter" id="place-tag-counter">
                                        <span data-place-tag-count><?php echo count($selectedTagIds); ?></span>/5
                                    </small>
                                </div>

                                <div class="hs-place-form__tag-filter-shell">
                                    <input
                                        type="search"
                                        id="place-tag-filter"
                                        class="form-control hs-place-form__control"
                                        placeholder="Tìm nhanh nhãn có sẵn..."
                                        autocomplete="off"
                                        aria-describedby="place-tag-counter place-tags-feedback">
                                </div>

                                <div
                                    id="place-selected-tags"
                                    class="hs-place-form__selected-tags"
                                    aria-label="Các nhãn đã chọn"></div>

                                <?php if (count($tags) === 0) { ?>
                                    <p class="hs-place-form__empty-hint mb-0 mt-2">
                                        Danh sách nhãn hiện chưa sẵn sàng. Bạn vẫn có thể tiếp tục mà không cần chọn nhãn.
                                    </p>
                                <?php } else { ?>
                                    <div
                                        id="place-tag-options"
                                        class="hs-place-form__tag-list"
                                        role="group"
                                        aria-label="Danh sách nhãn có sẵn">
                                        <?php foreach ($tags as $tagItem) {
                                            if (is_array($tagItem) === false) {
                                                continue;
                                            }
                                            $tagId = isset($tagItem['tag_id']) ? (int) $tagItem['tag_id'] : 0;
                                            $tagName = trim((string) ($tagItem['name'] ?? ''));
                                            if ($tagId <= 0 || $tagName === '') {
                                                continue;
                                            }
                                            $isTagChecked = in_array($tagId, $selectedTagIds, true);
                                            $tagInputId = 'place-tag-' . $tagId;
                                        ?>
                                            <label
                                                class="hs-place-tag-choice<?php if ($isTagChecked) { echo ' is-selected'; } ?>"
                                                for="<?php echo escapeHtml($tagInputId); ?>"
                                                data-tag-label="<?php echo escapeHtml($tagName); ?>">
                                                <input
                                                    type="checkbox"
                                                    id="<?php echo escapeHtml($tagInputId); ?>"
                                                    name="tag_ids[]"
                                                    value="<?php echo $tagId; ?>"
                                                    class="hs-place-tag-choice__input"
                                                    data-place-tag-checkbox
                                                    data-tag-name="<?php echo escapeHtml($tagName); ?>"
                                                    <?php if ($isTagChecked) { echo 'checked'; } ?>>
                                                <span class="hs-place-tag-choice__text">
                                                    <?php echo escapeHtml($tagName); ?>
                                                </span>
                                            </label>
                                        <?php } ?>
                                    </div>
                                <?php } ?>

                                <p
                                    id="place-tags-feedback"
                                    class="hs-place-form__field-error mt-2"
                                    role="alert"
                                    aria-live="polite"
                                    hidden></p>
                            </div>
                        </div>
                    </section>

                    <!-- Bước 3: Địa chỉ và khu vực -->
                    <section class="hs-place-form-card" aria-labelledby="place-step-location">
                        <div class="hs-place-form-card__header">
                            <span class="hs-place-form-step" aria-hidden="true">3</span>
                            <h2 id="place-step-location" class="hs-place-form-card__title">Địa chỉ và khu vực</h2>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="place-province" class="form-label hs-place-form__label">
                                    Tỉnh/Thành <span class="hs-place-form__required" aria-hidden="true">*</span>
                                </label>
                                <select
                                    id="place-province"
                                    name="province_code"
                                    class="form-select hs-place-form__control"
                                    required
                                    aria-describedby="place-province-error">
                                    <option value="">Chọn tỉnh/thành</option>
                                    <?php foreach ($provinces as $provinceItem) {
                                        if (is_array($provinceItem) === false) {
                                            continue;
                                        }
                                        // Giữ nguyên mã tỉnh dạng chuỗi để không mất số 0 ở đầu.
                                        $provinceCode = trim((string) ($provinceItem['province_code'] ?? ''));
                                        $provinceName = trim((string) ($provinceItem['province_name'] ?? ''));
                                        if ($provinceCode === '' || $provinceName === '') {
                                            continue;
                                        }
                                        $isSelectedProvince = $formValues['province_code'] === $provinceCode;
                                    ?>
                                        <option
                                            value="<?php echo escapeHtml($provinceCode); ?>"
                                            <?php if ($isSelectedProvince) { echo 'selected'; } ?>>
                                            <?php echo escapeHtml($provinceName); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <p
                                    id="place-province-error"
                                    class="hs-place-form__field-error mt-1"
                                    role="alert"
                                    hidden></p>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="place-ward" class="form-label hs-place-form__label">
                                    Phường/Xã <span class="hs-place-form__optional">(không bắt buộc)</span>
                                </label>
                                <select
                                    id="place-ward"
                                    name="ward_code"
                                    class="form-select hs-place-form__control"
                                    data-initial-ward="<?php echo escapeHtml($formValues['ward_code']); ?>"
                                    aria-describedby="place-ward-hint">
                                    <option value="">Chọn phường/xã</option>
                                    <?php foreach ($wards as $wardItem) {
                                        if (is_array($wardItem) === false) {
                                            continue;
                                        }
                                        $wardCode = trim((string) ($wardItem['ward_code'] ?? ''));
                                        $wardName = trim((string) ($wardItem['ward_name'] ?? ''));
                                        if ($wardCode === '' || $wardName === '') {
                                            continue;
                                        }
                                        $isSelectedWard = $formValues['ward_code'] === $wardCode;
                                    ?>
                                        <option
                                            value="<?php echo escapeHtml($wardCode); ?>"
                                            <?php if ($isSelectedWard) { echo 'selected'; } ?>>
                                            <?php echo escapeHtml($wardName); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <small id="place-ward-hint" class="hs-place-form__hint">
                                    Tùy chọn; tự động làm mới khi đổi tỉnh/thành.
                                </small>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="place-address" class="form-label hs-place-form__label">
                                    Địa chỉ chi tiết <span class="hs-place-form__required" aria-hidden="true">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="place-address"
                                    name="address"
                                    class="form-control hs-place-form__control"
                                    value="<?php echo escapeHtml($formValues['address']); ?>"
                                    maxlength="255"
                                    placeholder="Số nhà, tên đường..."
                                    required
                                    aria-describedby="place-address-error">
                                <p
                                    id="place-address-error"
                                    class="hs-place-form__field-error mt-1"
                                    role="alert"
                                    hidden></p>
                            </div>
                        </div>
                    </section>

                    <!-- Bước 4: Vị trí trên bản đồ (tùy chọn) -->
                    <section class="hs-place-form-card" aria-labelledby="place-step-map">
                        <div class="hs-place-form-card__header">
                            <span class="hs-place-form-step" aria-hidden="true">4</span>
                            <h2 id="place-step-map" class="hs-place-form-card__title">Vị trí trên bản đồ (tùy chọn)</h2>
                        </div>

                        <div class="row g-3 align-items-stretch">
                            <div class="col-12 col-md-6">
                                <div id="place-map-preview" class="hs-place-form__map-preview">
                                    <p class="hs-place-form__map-placeholder-title mb-2">
                                        Bản đồ trực quan chưa hỗ trợ
                                    </p>
                                    <p class="hs-place-form__map-placeholder-copy mb-2">
                                        Khung chọn vị trí trên bản đồ đang được hoàn thiện. Bạn có thể để trống tọa độ hoặc nhập cặp vĩ độ và kinh độ ở khung bên cạnh.
                                    </p>
                                    <p id="place-map-coord-readout" class="hs-place-form__map-readout mb-0">
                                        Chưa nhập tọa độ (không bắt buộc)
                                    </p>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="hs-place-form__geo-panel">
                                    <p class="hs-place-form__geo-prompt">
                                        Tọa độ địa lý là thông tin tùy chọn. Chức năng tìm vị trí tự động hiện chưa hỗ trợ.
                                    </p>

                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                        <button
                                            type="button"
                                            id="place-geocode-btn"
                                            class="btn hs-place-form__geocode-btn"
                                            disabled
                                            aria-disabled="true">
                                            Tìm vị trí (Chưa hỗ trợ)
                                        </button>

                                        <button
                                            type="button"
                                            id="place-clear-coords-btn"
                                            class="btn btn-sm btn-outline-secondary">
                                            Xóa tọa độ
                                        </button>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label for="place-latitude" class="form-label hs-place-form__sublabel">
                                                Vĩ độ (Latitude)
                                            </label>
                                            <input
                                                type="text"
                                                id="place-latitude"
                                                name="latitude"
                                                class="form-control form-control-sm hs-place-form__control"
                                                inputmode="decimal"
                                                value="<?php echo escapeHtml($formValues['latitude']); ?>"
                                                placeholder="VD: 11.9404 (tùy chọn)"
                                                aria-describedby="place-coords-error">
                                        </div>
                                        <div class="col-6">
                                            <label for="place-longitude" class="form-label hs-place-form__sublabel">
                                                Kinh độ (Longitude)
                                            </label>
                                            <input
                                                type="text"
                                                id="place-longitude"
                                                name="longitude"
                                                class="form-control form-control-sm hs-place-form__control"
                                                inputmode="decimal"
                                                value="<?php echo escapeHtml($formValues['longitude']); ?>"
                                                placeholder="VD: 108.4583 (tùy chọn)"
                                                aria-describedby="place-coords-error">
                                        </div>
                                    </div>

                                    <p
                                        id="place-coords-error"
                                        class="hs-place-form__field-error mb-2"
                                        role="alert"
                                        hidden></p>

                                    <div class="hs-place-form__geo-note" role="note">
                                        <span class="hs-place-form__geo-note-icon" aria-hidden="true">i</span>
                                        <p class="mb-0">
                                            <strong>Lưu ý:</strong> Việc tìm kiếm vị trí sử dụng Nominatim (OpenStreetMap) có thể không thành công trong một số trường hợp. Bạn vẫn có thể gửi địa điểm mà không cần chọn vị trí trên bản đồ.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Bước 5: Hình ảnh địa điểm -->
                    <section class="hs-place-form-card" aria-labelledby="place-step-images">
                        <div class="hs-place-form-card__header">
                            <span class="hs-place-form-step" aria-hidden="true">5</span>
                            <h2 id="place-step-images" class="hs-place-form-card__title">
                                Hình ảnh địa điểm <span class="hs-place-form__required" aria-hidden="true">*</span>
                            </h2>
                            <small class="hs-place-form__counter ms-auto" id="place-image-counter">
                                <span data-place-image-count>0</span>/3 hình ảnh
                            </small>
                        </div>

                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-5">
                                <input
                                    type="file"
                                    id="place-images"
                                    name="images[]"
                                    class="visually-hidden"
                                    accept="image/jpeg,image/png,image/webp"
                                    multiple
                                    aria-describedby="place-images-help place-images-feedback">

                                <label
                                    for="place-images"
                                    id="place-dropzone"
                                    class="hs-place-form__dropzone"
                                    tabindex="0"
                                    role="button"
                                    aria-label="Kéo thả hình ảnh vào đây hoặc nhấp để chọn từ 1 đến 3 ảnh">
                                    <span class="hs-place-form__dropzone-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M16 16l-4-4-4 4"></path>
                                            <path d="M12 12v9"></path>
                                            <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"></path>
                                        </svg>
                                    </span>
                                    <span class="hs-place-form__dropzone-text">
                                        <strong>Kéo thả hình ảnh vào đây hoặc nhấp để chọn</strong>
                                        <small id="place-images-help">
                                            Hỗ trợ định dạng: JPG, PNG, WebP. Tối đa 3 hình ảnh (bắt buộc 1–3 ảnh).
                                        </small>
                                    </span>
                                </label>
                            </div>

                            <div class="col-12 col-md-7">
                                <div
                                    id="place-image-previews"
                                    class="hs-place-form__previews"
                                    aria-live="polite">
                                    <div class="hs-place-form__preview-placeholder">
                                        Chưa có hình ảnh nào được chọn. Hãy chọn từ 1 đến 3 ảnh minh họa địa điểm.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p
                            id="place-images-feedback"
                            class="hs-place-form__field-error mt-2 mb-0"
                            role="alert"
                            aria-live="polite"
                            hidden></p>
                    </section>

                    <?php if ($isStoreReady === false) { ?>
                        <div class="alert alert-secondary hs-place-form__store-notice" role="status">
                            <strong>Trạng thái gửi bài:</strong>
                            Chức năng lưu địa điểm mới đang được hoàn thiện nên nút gửi lưu đang tạm khóa. Bạn có thể bấm <strong>Kiểm tra thông tin đã nhập</strong> để xác nhận tính hợp lệ của biểu mẫu.
                        </div>
                    <?php } ?>

                    <div class="hs-place-form__actions">
                        <a href="<?php echo escapeHtml($homePageUrl); ?>" class="btn hs-place-form__cancel-btn">
                            Hủy bỏ
                        </a>
                        <?php if ($isStoreReady) { ?>
                            <button type="submit" id="place-submit-btn" class="btn hs-place-form__submit-btn">
                                Gửi địa điểm chờ duyệt
                            </button>
                        <?php } else { ?>
                            <button type="button" id="place-check-btn" class="btn hs-place-form__check-btn">
                                Kiểm tra thông tin đã nhập
                            </button>
                            <button
                                type="submit"
                                id="place-submit-btn"
                                class="btn hs-place-form__submit-btn"
                                disabled
                                aria-disabled="true">
                                Gửi địa điểm (Chưa hỗ trợ)
                            </button>
                        <?php } ?>
                    </div>
                </form>
            </div>

            <div class="col-12 col-lg-4">
                <aside class="hs-place-form-sidebar" aria-label="Hướng dẫn đăng địa điểm">
                    <section class="hs-place-form-sidecard hs-place-form-sidecard--tips" aria-labelledby="place-tips-heading">
                        <h2 id="place-tips-heading" class="hs-place-form-sidecard__title">
                            Mẹo đăng địa điểm
                        </h2>
                        <ul class="hs-place-form-tips">
                            <li>
                                <span>Sử dụng hình ảnh rõ nét, chất lượng cao</span>
                            </li>
                            <li>
                                <span>Viết mô tả chi tiết, chân thực và hữu ích</span>
                            </li>
                            <li>
                                <span>Chọn danh mục và nhãn phù hợp</span>
                            </li>
                            <li>
                                <span>Thêm vị trí trên bản đồ để dễ tìm kiếm hơn</span>
                            </li>
                            <li>
                                <span>Chia sẻ những trải nghiệm thực tế, độc đáo của bạn</span>
                            </li>
                        </ul>
                    </section>

                    <section class="hs-place-form-sidecard hs-place-form-sidecard--rules" aria-labelledby="place-rules-heading">
                        <h2 id="place-rules-heading" class="hs-place-form-sidecard__title">
                            Quy định nội dung
                        </h2>
                        <ul class="hs-place-form-rules">
                            <li>
                                <span>Nội dung phải liên quan đến địa điểm du lịch, ẩm thực, vui chơi, trải nghiệm tại Việt Nam.</span>
                            </li>
                            <li>
                                <span>Thông tin phải chính xác, không gây hiểu nhầm.</span>
                            </li>
                            <li>
                                <span>Không đăng nội dung vi phạm pháp luật, thuần phong mỹ tục.</span>
                            </li>
                            <li>
                                <span>Hình ảnh phải do bạn sở hữu hoặc có quyền sử dụng.</span>
                            </li>
                        </ul>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</section>
<script src="assets/js/place-form.js" defer></script>
