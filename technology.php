<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$services     = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();
$technologies = $pdo->query("SELECT * FROM technologies WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();
$projects     = $pdo->query("SELECT * FROM projects WHERE status = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
$facilities   = $pdo->query("SELECT * FROM facilities WHERE status = 1 ORDER BY sort_order ASC LIMIT 6")->fetchAll();

$technologySampleImages = [
    base_url('assets/images/hero-welding-v2.jpg'),
    base_url('assets/images/hero-robotic-v2.jpg'),
    base_url('assets/images/hero-machining-v2.jpg'),
];
$contentImageUrl = static function (?string $filename, string $folder, int $index = 0) use ($technologySampleImages): string {
    $extension = strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
    if (!empty($filename) && in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        return image_url($filename, $folder);
    }
    return $technologySampleImages[$index % count($technologySampleImages)];
};

$technologyHeroImage = $technologySampleImages[1];
foreach ($technologies as $technology) {
    $extension = strtolower(pathinfo((string) ($technology['image'] ?? ''), PATHINFO_EXTENSION));
    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $technologyHeroImage = image_url($technology['image'], 'technologies');
        break;
    }
}

$pageTitle       = t('nav_technology');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'technology';

include __DIR__ . '/includes/header.php';
?>

<section class="technology-page-hero" style="--technology-hero-bg:url('<?= e($technologyHeroImage) ?>');">
    <div class="technology-page-hero__overlay"></div>
    <div class="container technology-page-hero__inner">
        <div class="eyebrow"><?= e(t('tech_eyebrow')) ?></div>
        <h1><?= e(t('nav_technology')) ?></h1>
        <p><?= e(t('tech_subtitle')) ?></p>
        <div class="technology-page-hero__meta" aria-label="Core capabilities">
            <span>Engineering</span><span>Fabrication</span><span>Automation</span>
        </div>
    </div>
</section>

<nav class="technology-local-nav" aria-label="Technology page sections">
    <div class="container">
        <a href="#feature"><?= $CURRENT_LANG === 'ja' ? '特長' : 'Feature' ?></a>
        <a href="#processing"><?= $CURRENT_LANG === 'ja' ? '加工技術' : 'Processing Technology' ?></a>
        <a href="#applications"><?= $CURRENT_LANG === 'ja' ? '加工事例' : 'Application Examples' ?></a>
        <a href="#facility"><?= $CURRENT_LANG === 'ja' ? '主要設備' : 'Facility' ?></a>
        <a href="<?= e(base_url('about.php')) ?>"><?= $CURRENT_LANG === 'ja' ? '会社情報' : 'Company' ?></a>
        <a href="<?= e(base_url('contact.php#location')) ?>"><?= $CURRENT_LANG === 'ja' ? '所在地' : 'Location' ?></a>
    </div>
</nav>

<section class="section section--white technology-feature" id="feature">
    <div class="container">
        <div class="technology-intro reveal">
            <div>
                <div class="eyebrow"><?= e(t('services_eyebrow')) ?></div>
                <h2 class="section-title"><?= e(t('services_title')) ?></h2>
            </div>
            <p><?= e(t('tech_subtitle')) ?></p>
        </div>

        <div class="technology-feature-grid">
            <?php foreach ($services as $i => $svc): ?>
            <a href="#processing" class="technology-feature-card reveal" aria-label="<?= e(tf($svc, 'title')) ?>">
                <div class="technology-feature-card__media">
                    <img src="<?= e($contentImageUrl($svc['image'], 'services', $i)) ?>" alt="<?= e(tf($svc, 'title')) ?>" loading="lazy">
                    <span><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="technology-feature-card__body">
                    <h3><?= e(tf($svc, 'title')) ?></h3>
                    <div class="technology-feature-card__label"><?= e($svc['title_en']) ?></div>
                    <p><?= e(tf($svc, 'description')) ?></p>
                    <span class="technology-feature-card__arrow" aria-hidden="true">&#8594;</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--off technology-processing" id="processing">
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('tech_eyebrow')) ?></div>
            <h2 class="section-title"><?= $CURRENT_LANG === 'ja' ? '加工技術' : 'Processing Technology' ?></h2>
            <p class="section-subtitle"><?= e(t('tech_subtitle')) ?></p>
        </div>

        <div class="technology-process-list">
            <?php foreach ($technologies as $i => $tech): ?>
            <article class="technology-process reveal" id="technology-<?= (int) $tech['id'] ?>">
                <div class="technology-process__media">
                    <img src="<?= e($contentImageUrl($tech['image'], 'technologies', $i)) ?>" alt="<?= e(tf($tech, 'name')) ?>" loading="lazy">
                    <span class="technology-process__number"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                </div>
                <div class="technology-process__content">
                    <div class="technology-process__eyebrow"><?= e($tech['name_en']) ?></div>
                    <h3><?= e(tf($tech, 'name')) ?></h3>
                    <p><?= nl2br(e(tf($tech, 'description'))) ?></p>
                    <div class="technology-process__tags">
                        <span><?= $CURRENT_LANG === 'ja' ? '高精度' : 'High Precision' ?></span>
                        <span><?= $CURRENT_LANG === 'ja' ? '品質管理' : 'Quality Control' ?></span>
                        <span><?= $CURRENT_LANG === 'ja' ? '量産対応' : 'Production Ready' ?></span>
                    </div>
                    <a href="<?= e(base_url('contact.php?inquiry_type=general')) ?>" class="text-link">
                        <?= e(t('hero_cta_contact')) ?> <span class="arrow">&#8594;</span>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if (!empty($projects)): ?>
<section class="section section--dark technology-applications" id="applications">
    <div class="container">
        <div class="section-head section-head--split reveal">
            <div>
                <div class="eyebrow"><?= e(t('projects_eyebrow')) ?></div>
                <h2 class="section-title"><?= $CURRENT_LANG === 'ja' ? '加工・導入事例' : 'Application Examples' ?></h2>
            </div>
            <a href="<?= e(base_url('projects.php')) ?>" class="text-link"><?= e(t('services_view_all')) ?> <span class="arrow">&#8594;</span></a>
        </div>
        <div class="technology-application-grid">
            <?php foreach ($projects as $i => $project): ?>
            <a href="<?= e(base_url('project-detail.php?slug=' . urlencode($project['slug']))) ?>" class="technology-application-card reveal">
                <img src="<?= e($contentImageUrl($project['image'], 'projects', $i)) ?>" alt="<?= e(tf($project, 'name')) ?>" loading="lazy">
                <div class="technology-application-card__overlay"></div>
                <div class="technology-application-card__content">
                    <span><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?> / <?= e($project['year']) ?></span>
                    <h3><?= e(tf($project, 'name')) ?></h3>
                    <p><?= e(tf($project, 'industry')) ?> · <?= e(tf($project, 'location')) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($facilities)): ?>
<section class="section section--white technology-facility" id="facility">
    <div class="container">
        <div class="section-head section-head--split reveal">
            <div>
                <div class="eyebrow"><?= e(t('facility_eyebrow')) ?></div>
                <h2 class="section-title"><?= e(t('facility_title')) ?></h2>
                <p class="section-subtitle"><?= e(t('facility_subtitle')) ?></p>
            </div>
            <a href="<?= e(base_url('facility.php')) ?>" class="text-link"><?= e(t('services_view_all')) ?> <span class="arrow">&#8594;</span></a>
        </div>
        <div class="technology-facility-grid">
            <?php foreach ($facilities as $i => $facility): ?>
            <a href="<?= e(base_url('facility.php#facility-' . $facility['id'])) ?>" class="technology-facility-card reveal" aria-label="<?= e(tf($facility, 'machine_name')) ?>">
                <div class="technology-facility-card__media">
                    <img src="<?= e($contentImageUrl($facility['image'], 'facilities', $i)) ?>" alt="<?= e(tf($facility, 'machine_name')) ?>" loading="lazy">
                    <span class="technology-facility-card__arrow" aria-hidden="true">&#8594;</span>
                </div>
                <div class="technology-facility-card__body">
                    <span><?= e($facility['manufacturer']) ?> / <?= e($facility['model']) ?></span>
                    <h3><?= e(tf($facility, 'machine_name')) ?></h3>
                    <p><?= e(tf($facility, 'description')) ?></p>
                    <span class="technology-facility-card__link"><?= $CURRENT_LANG === 'ja' ? '設備詳細を見る' : 'View equipment details' ?> <b aria-hidden="true">&#8594;</b></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="cta-band">
    <div class="container">
        <h2 class="section-title"><?= e(t('about_cta_title')) ?></h2>
        <div class="cta-band-actions">
            <a href="<?= e(base_url('contact.php')) ?>" class="btn btn--outline-dark">
                <?= e(t('about_cta_button')) ?> <span class="btn-arrow">&#8594;</span>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
