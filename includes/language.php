<?php
/**
 * Language handling
 * ------------------
 * Default language: Japanese (ja).
 * Switch via ?lang=ja or ?lang=en, stored in session, visitor stays on
 * the same page after switching.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$allowedLangs = ['ja', 'en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowedLangs, true)) {
    $_SESSION['site_lang'] = $_GET['lang'];
}

if (!isset($_SESSION['site_lang']) || !in_array($_SESSION['site_lang'], $allowedLangs, true)) {
    $_SESSION['site_lang'] = 'ja'; // default
}

$CURRENT_LANG = $_SESSION['site_lang'];

// Load static UI strings for the active language.
$LANG = require __DIR__ . '/lang/' . $CURRENT_LANG . '.php';

/**
 * t() — translate a static UI string by key.
 */
function t(string $key): string
{
    global $LANG;
    return $LANG[$key] ?? $key;
}

/**
 * tf() — pick the correct language field from a DB row, e.g. tf($product, 'name').
 * Looks for {field}_ja / {field}_en. Falls back to Japanese if English is empty,
 * and vice versa, so the page never shows a blank string.
 */
function tf(array $row, string $field): string
{
    global $CURRENT_LANG;

    $primary   = $row[$field . '_' . $CURRENT_LANG] ?? '';
    $fallback  = $CURRENT_LANG === 'ja'
        ? ($row[$field . '_en'] ?? '')
        : ($row[$field . '_ja'] ?? '');

    $value = trim((string) $primary) !== '' ? $primary : $fallback;

    return (string) $value;
}

/**
 * Build a URL that keeps the current query string but switches ?lang=
 */
function langSwitchUrl(string $lang): string
{
    $params = $_GET;
    $params['lang'] = $lang;
    return '?' . http_build_query($params);
}
