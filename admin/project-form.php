<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$pdo = getPDO();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$project = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $project = $stmt->fetch();
    if (!$project) { redirect(admin_url('projects.php')); }
}

$errors = [];
$old = $project ?: [
    'name_ja' => '', 'name_en' => '', 'industry_ja' => '', 'industry_en' => '', 'year' => '',
    'location_ja' => '', 'location_en' => '', 'description_ja' => '', 'description_en' => '',
    'challenge_ja' => '', 'challenge_en' => '', 'solution_ja' => '', 'solution_en' => '',
    'technology_ja' => '', 'technology_en' => '', 'result_ja' => '', 'result_en' => '',
    'status' => 1, 'image' => null, 'slug' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        foreach (['name_ja','name_en','industry_ja','industry_en','year','location_ja','location_en',
                  'description_ja','description_en','challenge_ja','challenge_en','solution_ja','solution_en',
                  'technology_ja','technology_en','result_ja','result_en'] as $field) {
            $old[$field] = clean($_POST[$field] ?? '');
        }
        $old['status'] = isset($_POST['status']) ? 1 : 0;

        if ($old['name_ja'] === '' || $old['name_en'] === '') {
            $errors[] = 'Name (Japanese) and Name (English) are both required.';
        }

        $newImage = null;
        try {
            $newImage = handle_image_upload('image', 'projects');
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }

        if (empty($errors)) {
            $imageToSave = $newImage ?? $old['image'];
            $slug = unique_slug($pdo, 'projects', $old['name_en'], $id ?: null);

            if ($id) {
                $stmt = $pdo->prepare(
                    "UPDATE projects SET name_ja=:name_ja, name_en=:name_en, industry_ja=:industry_ja, industry_en=:industry_en,
                     year=:year, location_ja=:location_ja, location_en=:location_en, description_ja=:description_ja, description_en=:description_en,
                     challenge_ja=:challenge_ja, challenge_en=:challenge_en, solution_ja=:solution_ja, solution_en=:solution_en,
                     technology_ja=:technology_ja, technology_en=:technology_en, result_ja=:result_ja, result_en=:result_en,
                     image=:image, status=:status, slug=:slug WHERE id=:id"
                );
                $stmt->execute([
                    ':name_ja' => $old['name_ja'], ':name_en' => $old['name_en'], ':industry_ja' => $old['industry_ja'], ':industry_en' => $old['industry_en'],
                    ':year' => $old['year'], ':location_ja' => $old['location_ja'], ':location_en' => $old['location_en'],
                    ':description_ja' => $old['description_ja'], ':description_en' => $old['description_en'],
                    ':challenge_ja' => $old['challenge_ja'], ':challenge_en' => $old['challenge_en'],
                    ':solution_ja' => $old['solution_ja'], ':solution_en' => $old['solution_en'],
                    ':technology_ja' => $old['technology_ja'], ':technology_en' => $old['technology_en'],
                    ':result_ja' => $old['result_ja'], ':result_en' => $old['result_en'],
                    ':image' => $imageToSave, ':status' => $old['status'], ':slug' => $slug, ':id' => $id,
                ]);
                if ($newImage) { delete_uploaded_image($project['image'] ?? null, 'projects'); }
                set_flash('success', 'Project updated.');
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO projects (name_ja, name_en, industry_ja, industry_en, year, location_ja, location_en,
                     description_ja, description_en, challenge_ja, challenge_en, solution_ja, solution_en,
                     technology_ja, technology_en, result_ja, result_en, image, status, slug)
                     VALUES (:name_ja, :name_en, :industry_ja, :industry_en, :year, :location_ja, :location_en,
                     :description_ja, :description_en, :challenge_ja, :challenge_en, :solution_ja, :solution_en,
                     :technology_ja, :technology_en, :result_ja, :result_en, :image, :status, :slug)"
                );
                $stmt->execute([
                    ':name_ja' => $old['name_ja'], ':name_en' => $old['name_en'], ':industry_ja' => $old['industry_ja'], ':industry_en' => $old['industry_en'],
                    ':year' => $old['year'], ':location_ja' => $old['location_ja'], ':location_en' => $old['location_en'],
                    ':description_ja' => $old['description_ja'], ':description_en' => $old['description_en'],
                    ':challenge_ja' => $old['challenge_ja'], ':challenge_en' => $old['challenge_en'],
                    ':solution_ja' => $old['solution_ja'], ':solution_en' => $old['solution_en'],
                    ':technology_ja' => $old['technology_ja'], ':technology_en' => $old['technology_en'],
                    ':result_ja' => $old['result_ja'], ':result_en' => $old['result_en'],
                    ':image' => $imageToSave, ':status' => $old['status'], ':slug' => $slug,
                ]);
                set_flash('success', 'Project created.');
            }
            redirect(admin_url('projects.php'));
        }
    }
}

$adminTitle  = $id ? 'Edit Project' : 'Add Project';
$adminActive = 'projects';
include __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-panel">
    <div class="admin-panel-head">
        <h2><?= e($adminTitle) ?></h2>
        <a href="<?= e(admin_url('projects.php')) ?>" class="abtn abtn--outline abtn--sm">&larr; Back to List</a>
    </div>

    <?php if (!empty($errors)): ?>
    <div style="padding:20px 28px 0;">
        <div class="admin-alert admin-alert--error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>

        <div class="aform-section-title">Basic Info</div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Project Name (Japanese)<span class="req">*</span></label>
                <input type="text" name="name_ja" class="aform-control" value="<?= e($old['name_ja']) ?>" required>
            </div>
            <div class="aform-row">
                <label class="aform-label">Project Name (English)<span class="req">*</span></label>
                <input type="text" name="name_en" class="aform-control" value="<?= e($old['name_en']) ?>" required>
            </div>
        </div>

        <div class="aform-row--split3">
            <div class="aform-row">
                <label class="aform-label">Industry (Japanese)</label>
                <input type="text" name="industry_ja" class="aform-control" value="<?= e($old['industry_ja']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Industry (English)</label>
                <input type="text" name="industry_en" class="aform-control" value="<?= e($old['industry_en']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Year</label>
                <input type="text" name="year" class="aform-control" value="<?= e($old['year']) ?>">
            </div>
        </div>

        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Location (Japanese)</label>
                <input type="text" name="location_ja" class="aform-control" value="<?= e($old['location_ja']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Location (English)</label>
                <input type="text" name="location_en" class="aform-control" value="<?= e($old['location_en']) ?>">
            </div>
        </div>

        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Overview (Japanese)</label>
                <textarea name="description_ja" class="aform-control"><?= e($old['description_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Overview (English)</label>
                <textarea name="description_en" class="aform-control"><?= e($old['description_en']) ?></textarea>
            </div>
        </div>

        <div class="aform-section-title">Case Study</div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Challenge (Japanese)</label>
                <textarea name="challenge_ja" class="aform-control"><?= e($old['challenge_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Challenge (English)</label>
                <textarea name="challenge_en" class="aform-control"><?= e($old['challenge_en']) ?></textarea>
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Solution (Japanese)</label>
                <textarea name="solution_ja" class="aform-control"><?= e($old['solution_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Solution (English)</label>
                <textarea name="solution_en" class="aform-control"><?= e($old['solution_en']) ?></textarea>
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Technology Used (Japanese)</label>
                <input type="text" name="technology_ja" class="aform-control" value="<?= e($old['technology_ja']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Technology Used (English)</label>
                <input type="text" name="technology_en" class="aform-control" value="<?= e($old['technology_en']) ?>">
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Result (Japanese)</label>
                <textarea name="result_ja" class="aform-control"><?= e($old['result_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Result (English)</label>
                <textarea name="result_en" class="aform-control"><?= e($old['result_en']) ?></textarea>
            </div>
        </div>

        <div class="aform-section-title">Image &amp; Status</div>
        <div class="aform-row">
            <label class="aform-label">Image</label>
            <?php if (!empty($old['image'])): ?>
            <div class="aform-current-image">
                <img src="<?= e(image_url($old['image'], 'projects')) ?>" alt="">
                <span class="aform-hint">Current image. Upload a new file below to replace it.</span>
            </div>
            <?php endif; ?>
            <input type="file" name="image" class="aform-control" accept=".jpg,.jpeg,.png,.webp">
            <div class="aform-hint">JPG, PNG, or WEBP. Max 5MB.</div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Status</label>
            <select name="status" class="aform-control">
                <option value="1" <?= $old['status'] ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= !$old['status'] ? 'selected' : '' ?>>Hidden</option>
            </select>
        </div>

        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary">Save Project</button>
            <a href="<?= e(admin_url('projects.php')) ?>" class="abtn abtn--outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
