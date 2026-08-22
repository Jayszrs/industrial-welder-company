<?php
/**
 * Shared helper functions used across the public site and admin panel.
 */

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ----------------------------------------------------------------------
 * CSRF protection
 * -------------------------------------------------------------------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* ----------------------------------------------------------------------
 * Output escaping / sanitization
 * -------------------------------------------------------------------- */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function clean(?string $value): string
{
    return trim((string) ($value ?? ''));
}

function slugify(string $text): string
{
    $text = preg_replace('/[^\pL\d]+/u', '-', $text);
    $text = trim($text, '-');
    $text = function_exists('iconv') ? (@iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text) : $text;
    $text = strtolower($text);
    $text = preg_replace('/[^-a-z0-9]+/', '', $text);
    $text = preg_replace('/-+/', '-', $text);
    if ($text === '' || $text === false) {
        $text = 'item-' . substr(bin2hex(random_bytes(4)), 0, 8);
    }
    return $text;
}

function unique_slug(PDO $pdo, string $table, string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    $original = $slug;
    $i = 2;

    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = :slug";
        if ($ignoreId) {
            $sql .= " AND id != :id";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($ignoreId) {
            $stmt->bindValue(':id', $ignoreId, PDO::PARAM_INT);
        }
        $stmt->execute();
        if (!$stmt->fetch()) {
            break;
        }
        $slug = $original . '-' . $i;
        $i++;
    }

    return $slug;
}

/* ----------------------------------------------------------------------
 * Redirect / flash messages
 * -------------------------------------------------------------------- */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* ----------------------------------------------------------------------
 * Image upload handling
 * -------------------------------------------------------------------- */
function handle_image_upload(string $fieldName, string $subfolder): ?string
{
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no file submitted, not an error
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload error code: ' . $file['error']);
    }

    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('File is too large (max 5MB).');
    }

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!isset($allowedMime[$mime])) {
        throw new RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
    }

    // Double check it's really an image (blocks disguised PHP files etc.)
    if (@getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('The uploaded file is not a valid image.');
    }

    $ext = $allowedMime[$mime];
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;

    $targetDir = UPLOAD_PATH . '/' . $subfolder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to save the uploaded file.');
    }

    return $filename;
}

function image_url(?string $filename, string $subfolder): string
{
    if (empty($filename)) {
        return base_url('assets/images/placeholder.svg');
    }
    return base_url('uploads/' . $subfolder . '/' . $filename);
}

function delete_uploaded_image(?string $filename, string $subfolder): void
{
    if (!empty($filename)) {
        $path = UPLOAD_PATH . '/' . $subfolder . '/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

/* ----------------------------------------------------------------------
 * URL helpers
 * -------------------------------------------------------------------- */
function base_url(string $path = ''): string
{
    // Detect the app's base folder automatically (works under any subfolder name).
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // If we're inside /admin/, go one level up.
    if (basename($scriptDir) === 'admin') {
        $scriptDir = dirname($scriptDir);
    }
    $scriptDir = rtrim($scriptDir, '/');
    return $scriptDir . '/' . ltrim($path, '/');
}

function admin_url(string $path = ''): string
{
    return rtrim(base_url('admin'), '/') . '/' . ltrim($path, '/');
}

/* ----------------------------------------------------------------------
 * Site settings (key/value)
 * -------------------------------------------------------------------- */
function get_setting(string $key, string $default = ''): string
{
    static $cache = null;
    $pdo = getPDO();

    if ($cache === null) {
        $cache = [];
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
        foreach ($stmt->fetchAll() as $row) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }

    return $cache[$key] ?? $default;
}

function set_setting(string $key, string $value): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        "INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
         ON DUPLICATE KEY UPDATE setting_value = :v2"
    );
    $stmt->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
}

/* ----------------------------------------------------------------------
 * Text helpers
 * -------------------------------------------------------------------- */
function truncate(string $text, int $length = 120): string
{
    $text = trim(strip_tags($text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '…';
}

function format_date(?string $date, string $lang = 'ja'): string
{
    if (empty($date)) {
        return '';
    }
    $timestamp = strtotime($date);
    if (!$timestamp) {
        return $date;
    }
    return $lang === 'ja' ? date('Y.m.d', $timestamp) : date('M j, Y', $timestamp);
}

/* ----------------------------------------------------------------------
 * Simple honeypot anti-spam check
 * -------------------------------------------------------------------- */
function honeypot_triggered(): bool
{
    return !empty($_POST['website_url']); // hidden field, humans leave it blank
}
