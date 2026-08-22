<?php
/**
 * Admin authentication helpers.
 * Include this at the top of every protected admin page (after functions.php).
 */

require_once __DIR__ . '/../../includes/functions.php';

function admin_attempt_login(string $username, string $password): bool
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_name']     = $user['full_name'];

        $upd = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = :id");
        $upd->execute([':id' => $user['id']]);

        return true;
    }

    return false;
}

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        redirect(admin_url('login.php'));
    }
}

function admin_logout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_name']);
    session_regenerate_id(true);
}
