<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_verify()) {
        set_flash('error', 'Invalid session. Please try again.');
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT image FROM news WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            delete_uploaded_image($row['image'], 'news');
            $pdo->prepare("DELETE FROM news WHERE id = :id")->execute([':id' => $id]);
            set_flash('success', 'News article deleted.');
        }
    }
    redirect(admin_url('news.php'));
}

$newsItems = $pdo->query("SELECT * FROM news ORDER BY publish_date DESC")->fetchAll();
$flash = get_flash();

$adminTitle  = 'News';
$adminActive = 'news';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2>News (<?= count($newsItems) ?>)</h2>
        <a href="<?= e(admin_url('news-form.php')) ?>" class="abtn abtn--accent">+ Add News</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Image</th><th>Date</th><th>Category</th><th>Title (EN)</th><th>Status</th><th></th></tr></thead>
            <tbody>
                <?php if (empty($newsItems)): ?>
                    <tr><td colspan="6" class="empty-row">No news yet.</td></tr>
                <?php else: foreach ($newsItems as $n): ?>
                    <tr>
                        <td><img class="thumb" src="<?= e(image_url($n['image'], 'news')) ?>" alt=""></td>
                        <td><?= e($n['publish_date']) ?></td>
                        <td><?= e($n['category_en']) ?></td>
                        <td><?= e($n['title_en']) ?></td>
                        <td><span class="status-badge <?= $n['status'] ? 'on' : 'off' ?>"><?= $n['status'] ? 'Published' : 'Hidden' ?></span></td>
                        <td class="row-actions">
                            <a href="<?= e(admin_url('news-form.php?id=' . $n['id'])) ?>" class="abtn abtn--outline abtn--sm">Edit</a>
                            <form method="post" class="confirm-delete" data-confirm="Delete this news article?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= e($n['id']) ?>">
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
