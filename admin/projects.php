<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid session. Please try again.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT image FROM projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_uploaded_image($row['image'], 'projects');
            $pdo->prepare("DELETE FROM projects WHERE id = :id")->execute([':id' => $id]);
            set_flash('success', 'Project deleted.');
        }
    }
    redirect(admin_url('projects.php'));
}

$projects = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC")->fetchAll();
$flash = get_flash();

$adminTitle  = 'Projects';
$adminActive = 'projects';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2>Projects (<?= count($projects) ?>)</h2>
        <a href="<?= e(admin_url('project-form.php')) ?>" class="abtn abtn--accent">+ Add Project</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Image</th><th>Name (EN)</th><th>Industry</th><th>Year</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($projects)): ?>
                    <tr><td colspan="6" class="empty-row">No projects yet.</td></tr>
                <?php else: foreach ($projects as $p): ?>
                    <tr>
                        <td><img class="thumb" src="<?= e(image_url($p['image'], 'projects')) ?>" alt=""></td>
                        <td><?= e($p['name_en']) ?></td>
                        <td><?= e($p['industry_en']) ?></td>
                        <td><?= e($p['year']) ?></td>
                        <td><span class="status-badge <?= $p['status'] ? 'on' : 'off' ?>"><?= $p['status'] ? 'Active' : 'Hidden' ?></span></td>
                        <td class="row-actions">
                            <a href="<?= e(admin_url('project-form.php?id=' . $p['id'])) ?>" class="abtn abtn--outline abtn--sm">Edit</a>
                            <form method="post" class="confirm-delete" data-confirm="Delete this project?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">
                                <button type="submit" class="abtn abtn--danger abtn--sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
