<?php
/** @var array<string, mixed> $viewData */

// Nhận dữ liệu widget do Controller trang Detail cung cấp.
$favoriteWidget = $viewData['favoriteWidget'];

$placeId = (int) $favoriteWidget['place_id'];
$isFavorited = (bool) $favoriteWidget['is_favorited'];
$isLoggedIn = (bool) $favoriteWidget['is_logged_in'];
$csrfToken = (string) $favoriteWidget['csrf_token'];

$favoriteUrl = createRouteUrl('favorites/set');
$loginUrl = createRouteUrl('login');

$desiredState = '1';
$buttonText = '♡ Lưu địa điểm';
$buttonClass = 'btn btn-outline-danger';
$ariaPressed = 'false';

if ($isFavorited) {
    $desiredState = '0';
    $buttonText = '♥ Đã lưu địa điểm';
    $buttonClass = 'btn btn-danger';
    $ariaPressed = 'true';
}
?>

<?php if ($isLoggedIn) { ?>
    <form
        class="favorite-form"
        method="post"
        action="<?php echo escapeHtml($favoriteUrl); ?>">

        <input
            type="hidden"
            name="place_id"
            value="<?php echo $placeId; ?>">

        <input
            type="hidden"
            name="desired_state"
            value="<?php echo escapeHtml($desiredState); ?>">

        <input
            type="hidden"
            name="csrf_token"
            value="<?php echo escapeHtml($csrfToken); ?>">

        <button
            type="submit"
            class="<?php echo escapeHtml($buttonClass); ?>"
            aria-pressed="<?php echo escapeHtml($ariaPressed); ?>">
            <?php echo escapeHtml($buttonText); ?>
        </button>

        <p
            class="favorite-message small mt-2 mb-0"
            role="status"
            aria-live="polite"></p>
    </form>
<?php } else { ?>
    <a
        class="btn btn-outline-danger"
        href="<?php echo escapeHtml($loginUrl); ?>">
        ♡ Đăng nhập để lưu
    </a>
<?php } ?>