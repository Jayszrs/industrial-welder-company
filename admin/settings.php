<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();
$errors = [];
$passwordErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'meta') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        set_setting('meta_description_ja', clean($_POST['meta_description_ja'] ?? ''));
        set_setting('meta_description_en', clean($_POST['meta_description_en'] ?? ''));
        set_flash('success', 'SEO settings updated.');
        redirect(admin_url('settings.php'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'password') {
    if (!csrf_verify()) {
        $passwordErrors[] = 'Invalid session, please try again.';
    } else {
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['admin_id']]);
        $adminUser = $stmt->fetch();

        if (!$adminUser || !password_verify($current, $adminUser['password'])) {
            $passwordErrors[] = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $passwordErrors[] = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $passwordErrors[] = 'New password and confirmation do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE admin_users SET password = :p WHERE id = :id")
                ->execute([':p' => $hash, ':id' => $adminUser['id']]);
            set_flash('success', 'Password updated. Please use your new password next time you log in.');
            redirect(admin_url('settings.php'));
        }
    }
}

$metaJa = get_setting('meta_description_ja');
$metaEn = get_setting('meta_description_en');
$flash = get_flash();

$adminTitle  = 'Site Settings';
$adminActive = 'settings';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head"><h2>SEO / Meta Description</h2></div>
    <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="meta">
        <?php if (!empty($errors)): ?><div class="admin-alert admin-alert--error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Meta Description (Japanese)</label>
                <textarea name="meta_description_ja" class="aform-control"><?= e($metaJa) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Meta Description (English)</label>
                <textarea name="meta_description_en" class="aform-control"><?= e($metaEn) ?></textarea>
            </div>
        </div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Save SEO Settings</button></div>
    </form>
</div>

<div class="admin-panel">
    <div class="admin-panel-head"><h2>Change Admin Password</h2></div>
    <form method="post" class="admin-form" style="max-width:480px;">
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="password">
        <?php if (!empty($passwordErrors)): ?><div class="admin-alert admin-alert--error"><?php foreach ($passwordErrors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div><?php endif; ?>
        <div class="aform-row">
            <label class="aform-label">Current Password</label>
            <input type="password" name="current_password" class="aform-control" required>
        </div>
        <div class="aform-row">
            <label class="aform-label">New Password</label>
            <input type="password" name="new_password" class="aform-control" minlength="8" required>
            <div class="aform-hint">At least 8 characters.</div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="aform-control" minlength="8" required>
        </div>
        <div class="aform-actions"><button type="submit" class="abtn abtn--primary">Update Password</button></div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
