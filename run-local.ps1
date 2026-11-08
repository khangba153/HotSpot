# Chạy từ thư mục dự án; cấu hình upload giống giới hạn trên form.
$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot
& php -d upload_max_filesize=5M -d post_max_size=20M -S 127.0.0.1:8000 -t public
