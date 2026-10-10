<?php
declare(strict_types=1);

use HotSpot\Core\HttpException;
use HotSpot\Core\View;

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $route = 'home', array $query = []): string
{
    $params = array_merge(['route' => $route], $query);
    return 'index.php?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $provided = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($provided) || !hash_equals(csrf_token(), $provided)) {
        throw new HttpException(403, 'CSRF token không hợp lệ. Tải lại trang và thử lại.');
    }
}

function json_response(bool $success, string $message, mixed $data = null, array $errors = [], int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data, 'errors' => (object) $errors], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function redirect_to(string $route, array $params = []): void
{
    header('Location: ' . url($route, $params), true, 303);
    exit;
}

function current_user(): ?array
{
    $user = $_SESSION['user'] ?? null;
    return is_array($user) && isset($user['user_id']) ? $user : null;
}

function must_login(bool $api = false): array
{
    $user = current_user();
    if ($user !== null) {
        return $user;
    }
    if ($api) {
        throw new HttpException(401, 'Bạn cần đăng nhập.');
    }
    $route = input_text($_GET, 'route', 'home');
    redirect_to('auth/login', ['next' => preg_match('~^[a-z0-9/]+$~', $route) ? $route : 'home']);
}

function must_admin(bool $api = false): array
{
    $user = must_login($api);
    if (($user['role_code'] ?? '') !== 'ADMIN') {
        throw new HttpException(403, 'Bạn không có quyền truy cập.');
    }
    return $user;
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return is_array($flash) ? $flash : null;
}

function safe_next(mixed $next): string
{
    return is_string($next) && preg_match('~^[a-z0-9/]{1,80}$~', $next) && !str_starts_with($next, 'auth/') ? $next : 'home';
}

// HTML forms should send strings; arrays are invalid input, not cast to "Array".
function input_text(array $input, string $key, string $default = ''): string
{
    return isset($input[$key]) && is_string($input[$key]) ? $input[$key] : $default;
}
