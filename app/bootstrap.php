<?php
declare(strict_types=1);

// Khởi tạo session để lưu và đọc thông tin đăng nhập.
session_start();

// Nạp các thư viện được cài bằng Composer.
require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/helpers/functions.php';