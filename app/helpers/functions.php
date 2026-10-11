<?php
declare(strict_types=1);

function escapeHtml(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function createRouteUrl(string $routeName = 'home'): string
{
    $encodedRouteName = rawurlencode($routeName);
    $routeUrl = 'index.php?route=' . $encodedRouteName;

    return $routeUrl;
}