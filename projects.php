<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$projects = $pdo->query("SELECT * FROM projects WHERE status = 1 ORDER BY created_at DESC")->fetchAll();
$pageHeaderImage = page_header_image_url($projects[0]['image'] ?? null, 'projects', 1);

$pageTitle       = t('projects_title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'projects';

include __DIR__ . '/includes/header.php';
?>

<section class="page-header" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
    <div class="container">
        <div class="eyebrow"><?= e(t('projects_eyebrow')) ?></div>
        <h1 class="page-title"><?= e(t('projects_title')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <span><?= e(t('nav_projects')) ?></span>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="grid-cards">
            <?php foreach ($projects as $proj): ?>
            <div class="card reveal">
                <a href="<?= e(base_url('project-detail.php?slug=' . urlencode($proj['slug']))) ?>" class="card-media">
                    <img src="<?= e(image_url($proj['image'], 'projects')) ?>" alt="<?= e(tf($proj, 'name')) ?>" loading="lazy">
                    <span class="card-tag"><?= e($proj['year']) ?></span>
                </a>
                <div class="card-body">
                    <div class="card-meta"><?= e(tf($proj, 'industry')) ?> · <?= e(tf($proj, 'location')) ?></div>
                    <h3 class="card-title"><?= e(tf($proj, 'name')) ?></h3>
                    <p class="card-text"><?= e(truncate(tf($proj, 'description'), 90)) ?></p>
                    <div class="card-foot">
                        <a href="<?= e(base_url('project-detail.php?slug=' . urlencode($proj['slug']))) ?>" class="text-link">
                            <?= e(t('project_view_details')) ?> <span class="arrow">&#8594;</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
