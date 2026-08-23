<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$slug = clean($_GET['slug'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = :slug AND status = 1");
$stmt->execute([':slug' => $slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    $pageTitle = t('error_404_title');
    $activePage = 'projects';
    include __DIR__ . '/includes/header.php';
    echo '<div class="empty-state container"><div class="code">404</div><h2 class="section-title" style="margin-top:20px;">' . e(t('error_404_title')) . '</h2><p style="margin-top:12px;">' . e(t('error_404_text')) . '</p><a href="' . e(base_url('projects.php')) . '" class="btn btn--outline-dark" style="margin-top:32px;">' . e(t('project_back')) . '</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM projects WHERE status = 1 AND id != :id ORDER BY created_at DESC LIMIT 3");
$stmt->execute([':id' => $project['id']]);
$related = $stmt->fetchAll();

$pageTitle       = tf($project, 'name');
$pageDescription = truncate(tf($project, 'description'), 150);
$activePage      = 'projects';
$projectImage    = image_url($project['image'], 'projects');
$projectFacts = array_filter([
    [t('project_detail_industry'), tf($project, 'industry')],
    [t('project_detail_year'), $project['year']],
    [t('project_detail_location'), tf($project, 'location')],
], static fn(array $fact): bool => trim((string) $fact[1]) !== '');

include __DIR__ . '/includes/header.php';
?>

<section class="product-detail-hero project-detail-hero" style="--product-hero-bg:url('<?= e($projectImage) ?>');" role="img" aria-label="<?= e(tf($project, 'name')) ?>">
    <div class="container product-detail-hero__container">
        <div class="breadcrumb product-detail-hero__breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <a href="<?= e(base_url('projects.php')) ?>"><?= e(t('nav_projects')) ?></a>
            <span class="sep">/</span>
            <span><?= e(tf($project, 'name')) ?></span>
        </div>
        <div class="product-detail-hero__content">
            <div class="eyebrow"><?= e(tf($project, 'industry')) ?> · <?= e($project['year']) ?></div>
            <h1><?= e(tf($project, 'name')) ?></h1>
            <p class="product-detail-hero__lead"><?= e(truncate(tf($project, 'description'), 170)) ?></p>

            <?php if (!empty($projectFacts)): ?>
            <dl class="product-detail-hero__facts">
                <?php foreach ($projectFacts as [$label, $value]): ?>
                <div><dt><?= e($label) ?></dt><dd><?= e($value) ?></dd></div>
                <?php endforeach; ?>
            </dl>
            <?php endif; ?>

            <div class="product-detail-hero__actions">
                <a href="<?= e(base_url('contact.php')) ?>" class="btn btn--primary">
                    <?= e(t('hero_cta_contact')) ?> <span class="btn-arrow">&#8594;</span>
                </a>
                <a href="<?= e(base_url('projects.php')) ?>" class="btn btn--outline-light">
                    <span class="btn-arrow">&#8592;</span> <?= e(t('project_back')) ?>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section section--white product-detail-content project-detail-content">
    <div class="container">
        <div class="detail-grid">
            <div>
                <div class="detail-block reveal">
                    <h3><?= $CURRENT_LANG === 'ja' ? 'プロジェクト概要' : 'Project Overview' ?></h3>
                    <p><?= nl2br(e(tf($project, 'description'))) ?></p>
                </div>
                <div class="detail-block reveal">
                    <h3><?= e(t('project_detail_challenge')) ?></h3>
                    <p><?= nl2br(e(tf($project, 'challenge'))) ?></p>
                </div>
                <div class="detail-block reveal">
                    <h3><?= e(t('project_detail_solution')) ?></h3>
                    <p><?= nl2br(e(tf($project, 'solution'))) ?></p>
                </div>
                <div class="detail-block reveal">
                    <h3><?= e(t('project_detail_result')) ?></h3>
                    <p><?= nl2br(e(tf($project, 'result'))) ?></p>
                </div>
            </div>

            <div class="detail-sidebar reveal">
                <h4><?= e(tf($project, 'name')) ?></h4>
                <p class="detail-sidebar-copy"><?= $CURRENT_LANG === 'ja' ? '同様の自動化・製造課題について、技術担当者にご相談ください。' : 'Discuss a similar automation or manufacturing challenge with our engineering team.' ?></p>
                <div class="detail-sidebar-row"><span class="k"><?= e(t('project_detail_industry')) ?></span><span class="v"><?= e(tf($project, 'industry')) ?></span></div>
                <div class="detail-sidebar-row"><span class="k"><?= e(t('project_detail_year')) ?></span><span class="v"><?= e($project['year']) ?></span></div>
                <div class="detail-sidebar-row"><span class="k"><?= e(t('project_detail_location')) ?></span><span class="v"><?= e(tf($project, 'location')) ?></span></div>
                <div class="detail-sidebar-row"><span class="k"><?= e(t('project_detail_technology')) ?></span><span class="v"><?= e(tf($project, 'technology')) ?></span></div>
                <a href="<?= e(base_url('contact.php')) ?>" class="btn btn--primary btn--block">
                    <?= e(t('hero_cta_contact')) ?>
                </a>
            </div>
        </div>

        <?php if (!empty($related)): ?>
        <div class="section-head reveal" style="margin-top:100px;">
            <div class="eyebrow">RELATED</div>
            <h2 class="section-title"><?= e(t('project_detail_related')) ?></h2>
        </div>
        <div class="grid-cards">
            <?php foreach ($related as $r): ?>
            <div class="card reveal">
                <a href="<?= e(base_url('project-detail.php?slug=' . urlencode($r['slug']))) ?>" class="card-media">
                    <img src="<?= e(image_url($r['image'], 'projects')) ?>" alt="<?= e(tf($r, 'name')) ?>" loading="lazy">
                    <span class="card-tag"><?= e($r['year']) ?></span>
                </a>
                <div class="card-body">
                    <div class="card-meta"><?= e(tf($r, 'industry')) ?></div>
                    <h3 class="card-title"><?= e(tf($r, 'name')) ?></h3>
                    <div class="card-foot">
                        <a href="<?= e(base_url('project-detail.php?slug=' . urlencode($r['slug']))) ?>" class="text-link">
                            <?= e(t('project_view_details')) ?> <span class="arrow">&#8594;</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
