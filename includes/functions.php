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

/**
 * Save every image submitted by a multiple file input.
 * Returns the generated filenames in the same order selected by the user.
 */
function handle_multiple_image_uploads(string $fieldName, string $subfolder): array
{
    if (empty($_FILES[$fieldName])) {
        return [];
    }

    $batch = $_FILES[$fieldName];
    $names = is_array($batch['name'] ?? null) ? $batch['name'] : [$batch['name'] ?? ''];
    $types = is_array($batch['type'] ?? null) ? $batch['type'] : [$batch['type'] ?? ''];
    $tmpNames = is_array($batch['tmp_name'] ?? null) ? $batch['tmp_name'] : [$batch['tmp_name'] ?? ''];
    $errors = is_array($batch['error'] ?? null) ? $batch['error'] : [$batch['error'] ?? UPLOAD_ERR_NO_FILE];
    $sizes = is_array($batch['size'] ?? null) ? $batch['size'] : [$batch['size'] ?? 0];
    $saved = [];

    foreach ($names as $index => $name) {
        $error = (int) ($errors[$index] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $_FILES['__batch_image'] = [
            'name' => $name,
            'type' => $types[$index] ?? '',
            'tmp_name' => $tmpNames[$index] ?? '',
            'error' => $error,
            'size' => $sizes[$index] ?? 0,
        ];

        try {
            $filename = handle_image_upload('__batch_image', $subfolder);
            if ($filename !== null) {
                $saved[] = $filename;
            }
        } catch (Throwable $exception) {
            foreach ($saved as $savedFilename) {
                delete_uploaded_image($savedFilename, $subfolder);
            }
            unset($_FILES['__batch_image']);
            throw $exception;
        }
    }

    unset($_FILES['__batch_image']);
    return $saved;
}

/**
 * Lightweight migration for installations that already imported database.sql.
 */
function ensure_hero_slides_table(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS hero_slides (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image VARCHAR(255) NOT NULL,
            sort_order INT DEFAULT 0,
            status TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_hero_slides_display (status, sort_order, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

/**
 * Add optional quotation/order fields without requiring existing installations
 * to re-import the whole database.
 */
function ensure_inquiry_order_fields(PDO $pdo): void
{
    $columns = [
        'product_interest' => "VARCHAR(255) NULL AFTER subject",
        'quantity' => "VARCHAR(50) NULL AFTER product_interest",
        'budget_range' => "VARCHAR(100) NULL AFTER quantity",
        'desired_timeline' => "VARCHAR(100) NULL AFTER budget_range",
    ];

    $existingColumns = array_column($pdo->query("SHOW COLUMNS FROM inquiries")->fetchAll(), 'Field');
    foreach ($columns as $column => $definition) {
        if (!in_array($column, $existingColumns, true)) {
            $pdo->exec("ALTER TABLE inquiries ADD COLUMN {$column} {$definition}");
        }
    }
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

function asset_url(string $path): string
{
    $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
    $url = base_url($normalizedPath);
    $file = BASE_PATH . '/' . $normalizedPath;
    return is_file($file) ? $url . '?v=' . filemtime($file) : $url;
}

/**
 * Use an uploaded content photo as a page banner, with local sample photos
 * as a fallback while the seeded SVG placeholders have not been replaced.
 */
function page_header_image_url(?string $filename = null, string $subfolder = '', int $fallbackIndex = 0): string
{
    $extension = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
    if (!empty($filename) && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        return image_url($filename, $subfolder);
    }

    $fallbacks = [
        'assets/images/hero-welding-v2.jpg',
        'assets/images/hero-robotic-v2.jpg',
        'assets/images/hero-machining-v2.jpg',
    ];
    $index = abs($fallbackIndex) % count($fallbacks);
    return base_url($fallbacks[$index]);
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

/**
 * Public contact destination. Previous social profile values stay preserved
 * in the database, while the current public design exposes email only.
 */
function site_social_links(): array
{
    $email = get_setting('email', '');
    $links = [
        'email' => ['label' => 'Email', 'href' => $email !== '' ? 'mailto:' . $email : ''],
    ];

    return array_filter($links, static function (array $link): bool {
        return str_starts_with($link['href'], 'mailto:') || preg_match('#^https?://#i', $link['href']) === 1;
    });
}

function social_icon_svg(string $network): string
{
    $icons = [
        'email' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5.5h18v13H3v-13Zm1.8 1.7L12 12.4l7.2-5.2H4.8Zm14.4 9.6V9.4L12 14.6 4.8 9.4v7.4h14.4Z"/></svg>',
    ];

    return $icons[$network] ?? '';
}

function render_social_links(string $className = 'social-links'): void
{
    echo '<div class="' . e($className) . '" aria-label="Email contact">';
    foreach (site_social_links() as $network => $link) {
        $external = !str_starts_with($link['href'], 'mailto:');
        echo '<a href="' . e($link['href']) . '" aria-label="' . e($link['label']) . '" title="' . e($link['label']) . '"';
        if ($external) {
            echo ' target="_blank" rel="noopener noreferrer"';
        }
        echo '>' . social_icon_svg($network) . '<span class="sr-only">' . e($link['label']) . '</span></a>';
    }
    echo '</div>';
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
