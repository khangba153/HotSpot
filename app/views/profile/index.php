<?php

/** @var array<string, mixed> $viewData Dữ liệu từ ProfileController. */

// Nhận thông tin tài khoản và dữ liệu cho form đăng xuất.
$userInformation = $viewData['userInformation'];
$csrfToken = $viewData['csrfToken'];
$logoutUrl = createRouteUrl('logout');
?>

<div
    class="profile-cover"
    role="img"
    aria-label="Phong cảnh vịnh Hạ Long"></div>

<div class="container py-4">
    <div class="row g-4">

        <div class="col-12 col-lg-3">
            <aside class="profile-sidebar">
                <div class="text-center">
                    <img
                        src="assets/images/default-avatar.svg"
                        alt="Ảnh đại diện mặc định"
                        class="profile-avatar"
                        width="160"
                        height="160">

                    <h2 class="profile-name">
                        <?php echo escapeHtml($userInformation['full_name']); ?>
                    </h2>

                    <p class="profile-description">
                        Chưa có thông tin giới thiệu.
                    </p>
                </div>

                <nav class="profile-menu" aria-label="Menu hồ sơ">
                    <span class="profile-menu-item active" aria-current="page">
                        Hồ sơ
                    </span>

                    <span class="profile-menu-item" aria-disabled="true">
                        Địa điểm của tôi
                        <small>Chưa hỗ trợ</small>
                    </span>

                    <span class="profile-menu-item" aria-disabled="true">
                        Yêu thích
                        <small>Chưa hỗ trợ</small>
                    </span>

                    <span class="profile-menu-item" aria-disabled="true">
                        Đã đánh giá
                        <small>Chưa hỗ trợ</small>
                    </span>
                </nav>

                <form
                    method="post"
                    action="<?php echo escapeHtml($logoutUrl); ?>"
                    class="mt-4">
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo escapeHtml($csrfToken); ?>">

                    <button
                        type="submit"
                        class="btn btn-outline-danger w-100">
                        Đăng xuất
                    </button>
                </form>
            </aside>
        </div>

        <div class="col-12 col-lg-9">
            <section class="profile-overview mb-4">
                <h2 class="profile-section-title">
                    Tổng quan hoạt động
                </h2>

                <div class="row g-3">
                    <div class="col-6 col-xl-3">
                        <div class="profile-statistic">
                            <span class="statistic-icon statistic-favorite" aria-hidden="true">
                                ♥
                            </span>

                            <div>
                                <strong class="statistic-number">—</strong>
                                <span class="statistic-label">Địa điểm yêu thích</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="profile-statistic">
                            <span class="statistic-icon statistic-place" aria-hidden="true">
                                ⌖
                            </span>

                            <div>
                                <strong class="statistic-number">—</strong>
                                <span class="statistic-label">Địa điểm đã đăng</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="profile-statistic">
                            <span class="statistic-icon statistic-review" aria-hidden="true">
                                ★
                            </span>

                            <div>
                                <strong class="statistic-number">—</strong>
                                <span class="statistic-label">Bài đánh giá</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-xl-3">
                        <div class="profile-statistic">
                            <span class="statistic-icon statistic-photo" aria-hidden="true">
                                ▣
                            </span>

                            <div>
                                <strong class="statistic-number">—</strong>
                                <span class="statistic-label">Ảnh đã chia sẻ</span>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="statistic-note">
                    Thống kê chưa được kết nối dữ liệu.
                </p>
            </section>

            <section class="profile-information">
                <h1 class="profile-section-title">
                    Thông tin cá nhân
                </h1>

                <p class="text-secondary mb-4">
                    Thông tin tài khoản của bạn. Chức năng chỉnh sửa sẽ được bổ sung sau.
                </p>

                <div class="row align-items-center mb-3">
                    <div class="col-12 col-sm-3">
                        <label for="profile-full-name" class="form-label">
                            Họ và tên
                        </label>
                    </div>

                    <div class="col-12 col-sm-9">
                        <input
                            type="text"
                            id="profile-full-name"
                            class="form-control"
                            value="<?php echo escapeHtml($userInformation['full_name']); ?>"
                            readonly>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-12 col-sm-3">
                        <label for="profile-email" class="form-label">
                            Email
                        </label>
                    </div>

                    <div class="col-12 col-sm-9">
                        <input
                            type="email"
                            id="profile-email"
                            class="form-control"
                            value="<?php echo escapeHtml($userInformation['email']); ?>"
                            readonly>
                    </div>
                </div>

                <!-- Các trường này chưa có trong bảng users, chỉ dựng giao diện. -->
                <div class="row align-items-center mb-3">
                    <div class="col-12 col-sm-3">
                        <label for="profile-phone" class="form-label">
                            Số điện thoại
                        </label>
                    </div>

                    <div class="col-12 col-sm-9">
                        <input
                            type="text"
                            id="profile-phone"
                            class="form-control"
                            placeholder="Chưa hỗ trợ"
                            disabled>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-12 col-sm-3">
                        <label for="profile-province" class="form-label">
                            Tỉnh/Thành
                        </label>
                    </div>

                    <div class="col-12 col-sm-9">
                        <select
                            id="profile-province"
                            class="form-select"
                            disabled>
                            <option>Chưa hỗ trợ</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 col-sm-3">
                        <label for="profile-introduction" class="form-label">
                            Giới thiệu bản thân
                        </label>
                    </div>

                    <div class="col-12 col-sm-9">
                        <textarea
                            id="profile-introduction"
                            class="form-control"
                            rows="3"
                            placeholder="Chưa hỗ trợ"
                            disabled></textarea>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-sm-9 offset-sm-3">
                        <p class="profile-account-detail">
                            Vai trò:
                            <?php echo escapeHtml($userInformation['role_code']); ?>
                            <br>
                            Ngày tạo tài khoản:
                            <?php echo escapeHtml($userInformation['created_at']); ?>
                        </p>

                        <div class="text-end">
                            <button
                                type="button"
                                class="btn button-brand"
                                disabled>
                                Lưu thay đổi
                            </button>
                        </div>
                    </div>
                </div>
            </section>

        </div>

    </div>
</div>