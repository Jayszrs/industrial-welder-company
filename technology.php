<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$services     = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();
$technologies = $pdo->query("SELECT * FROM technologies WHERE status = 1 ORDER BY sort_order ASC")->fetchAll();

$pageTitle       = t('nav_technology');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'technology';

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="eyebrow"><?= e(t('services_eyebrow')) ?></div>
        <h1 class="page-title"><?= e(t('nav_technology')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <span><?= e(t('nav_technology')) ?></span>
        </div>
    </div>
</section>

<!-- Services grid -->
<section class="section section--off" id="services">
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('services_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('services_title')) ?></h2>
        </div>
    </div>
    <div class="grid-3">
        <?php foreach ($services as $i => $svc): ?>
        <div class="service-card reveal">
            <div class="service-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
            <div class="service-media">
                <img src="<?= e(image_url($svc['image'], 'services')) ?>" alt="<?= e(tf($svc, 'title')) ?>" loading="lazy">
            </div>
            <div class="service-title-jp"><?= e(tf($svc, 'title')) ?></div>
            <div class="service-title-en"><?= e($svc['title_en']) ?></div>
            <p class="service-desc"><?= e(tf($svc, 'description')) ?></p>
            <div class="service-arrow">&#8594;</div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Welding technology list -->
<section class="section section--dark" id="welding-technology">
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('tech_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('tech_title')) ?></h2>
            <p class="section-subtitle"><?= e(t('tech_subtitle')) ?></p>
        </div>

        <div class="tech-list">
            <?php foreach ($technologies as $i => $tech): ?>
            <div class="tech-row reveal">
                <div class="tech-index"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
                <div>
                    <div class="tech-name-jp"><?= e(tf($tech, 'name')) ?></div>
                    <div class="tech-name-en"><?= e($tech['name_en']) ?></div>
                </div>
                <div class="tech-desc"><?= e(tf($tech, 'description')) ?></div>
                <div class="tech-thumb"><img src="<?= e(image_url($tech['image'], 'technologies')) ?>" alt="" loading="lazy"></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

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
