<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$aboutHome = $pdo->query("SELECT * FROM homepage_content WHERE section_key = 'about'")->fetch();
$strengthBlock = $pdo->query("SELECT * FROM homepage_content WHERE section_key = 'strength'")->fetch();
$ctaBlock = $pdo->query("SELECT * FROM homepage_content WHERE section_key = 'cta'")->fetch();
$stats     = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC")->fetchAll();
$stats     = array_values(array_filter($stats, static fn(array $stat): bool => (int) ($stat['id'] ?? 0) !== 2 && stripos((string) ($stat['label_en'] ?? ''), 'client') === false));
$industries = $pdo->query("SELECT * FROM industries ORDER BY sort_order ASC")->fetchAll();
$pageHeaderImage = page_header_image_url($aboutHome['image'] ?? null, 'homepage', 0);
$qualityImage = !empty($strengthBlock['image'])
    ? image_url($strengthBlock['image'], 'homepage')
    : image_url($aboutHome['image'] ?? null, 'homepage');
$ctaBackgroundImage = !empty($ctaBlock['image'])
    ? image_url($ctaBlock['image'], 'homepage')
    : base_url('assets/images/hero-welding-v2.jpg');

$pageTitle       = t('about_page_title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'about';

include __DIR__ . '/includes/header.php';
?>

<section class="page-header page-header--about" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
    <div class="container">
        <div class="eyebrow"><?= e(t('about_page_eyebrow')) ?></div>
        <h1 class="page-title"><?= e(t('about_page_title')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <span><?= e(t('nav_about')) ?></span>
        </div>
    </div>
</section>

<main class="about-page">

<!-- Company introduction -->
<section class="section section--white about-page-intro">
    <div class="container">
        <div class="about-grid">
            <div class="about-visual reveal">
                <img src="<?= e(image_url($aboutHome['image'], 'homepage')) ?>" alt="<?= e(tf($aboutHome, 'title')) ?>">
                <div class="about-visual-caption"><span>01</span> <?= e(t('about_intro_title')) ?></div>
            </div>
            <div class="about-text-block reveal">
                <div class="eyebrow">01 / <?= e(t('about_intro_title')) ?></div>
                <h2 class="section-title"><?= e(tf($aboutHome, 'title')) ?></h2>
                <p class="about-intro-copy"><?= nl2br(e(tf($aboutHome, 'content'))) ?></p>

                <div class="about-meta">
                    <?php foreach ($stats as $s): ?>
                    <div class="about-meta-item">
                        <div class="num"><?= e($s['number_value']) ?></div>
                        <div class="label"><?= e(tf($s, 'label')) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Philosophy / Mission / Vision -->
<section class="section about-values-section">
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('about_philosophy_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('about_philosophy_title')) ?></h2>
        </div>

        <div class="about-values-grid">
            <div class="about-value-card reveal">
                <div class="service-num">01</div>
                <div class="service-title-jp"><?= e(t('about_mission_title')) ?></div>
                <div class="service-title-en">MISSION</div>
                <p class="service-desc" style="margin-top:16px;">
                    <?= $CURRENT_LANG === 'ja'
                        ? '確かな技術と誠実なものづくりを通じて、お客様の生産現場に信頼できる価値を提供し続けます。'
                        : 'To continually deliver dependable value to our clients\' production floors through solid technology and honest craftsmanship.' ?>
                </p>
            </div>
            <div class="about-value-card reveal reveal-delay-1">
                <div class="service-num">02</div>
                <div class="service-title-jp"><?= e(t('about_vision_title')) ?></div>
                <div class="service-title-en">VISION</div>
                <p class="service-desc" style="margin-top:16px;">
                    <?= $CURRENT_LANG === 'ja'
                        ? '溶接技術と産業機械ソリューションの分野で、日本のものづくりを支える存在であり続けること。'
                        : 'To remain a company that supports Japanese manufacturing through welding technology and industrial machinery solutions.' ?>
                </p>
            </div>
            <div class="about-value-card reveal reveal-delay-2">
                <div class="service-num">03</div>
                <div class="service-title-jp"><?= e(t('about_quality_title')) ?></div>
                <div class="service-title-en">QUALITY</div>
                <p class="service-desc" style="margin-top:16px;">
                    <?= $CURRENT_LANG === 'ja'
                        ? '厳格な品質管理体制と継続的な技術改善により、お客様に安心してご利用いただける製品とサービスを追求します。'
                        : 'Through strict quality control and continuous technical improvement, we pursue products and services our clients can rely on with confidence.' ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Quality commitment -->
<section class="about-quality-section" id="quality">
    <div class="container">
        <div class="about-quality-shell reveal">
            <div class="about-quality-media">
                <img src="<?= e($qualityImage) ?>" alt="<?= e(tf($strengthBlock, 'title')) ?>">
            </div>
            <div class="about-quality-content">
                <div class="eyebrow"><?= e(t('about_quality_eyebrow')) ?></div>
                <h2><?= e(tf($strengthBlock, 'title')) ?></h2>
                <p><?= e(t('strength_subtitle')) ?></p>
                <a href="<?= e(base_url('technology.php')) ?>" class="text-link">
                    <?= e(t('nav_technology')) ?> <span class="arrow">&#8594;</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Company profile table -->
<section class="section section--white about-profile-section" id="company-profile">
    <div class="container">
        <div class="about-profile-layout">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('about_profile_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('about_profile_title')) ?></h2>
        </div>

        <table class="spec-table about-profile-table reveal">
            <tr><th><?= e(t('about_profile_company_name')) ?></th><td><?= e(get_setting('company_name_' . $CURRENT_LANG)) ?></td></tr>
            <tr><th><?= e(t('about_profile_representative')) ?></th><td><?= e(get_setting('representative_' . $CURRENT_LANG)) ?></td></tr>
            <tr><th><?= e(t('about_profile_established')) ?></th><td><?= e(get_setting('established')) ?></td></tr>
            <tr><th><?= e(t('about_profile_business')) ?></th><td><?= e(get_setting('business_activities_' . $CURRENT_LANG)) ?></td></tr>
            <tr><th><?= e(t('about_profile_office')) ?></th><td><?= e(get_setting('address_' . $CURRENT_LANG)) ?></td></tr>
            <tr><th><?= e(t('about_profile_tel')) ?></th><td><?= e(get_setting('phone')) ?></td></tr>
            <tr><th><?= e(t('about_profile_email')) ?></th><td><?= e(get_setting('email')) ?></td></tr>
            <tr><th><?= e(t('about_profile_website')) ?></th><td><?= e(get_setting('website')) ?></td></tr>
        </table>

        <p class="about-profile-note">
            <?= $CURRENT_LANG === 'ja' ? '※ 上記は' : '*' ?> <?= e(get_setting('sample_data_notice')) ?>
        </p>
        </div>
    </div>
</section>

<!-- Industries -->
<section class="section section--off about-industries-section" id="industries">
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('industries_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('industries_title')) ?></h2>
        </div>
    </div>
    <div class="industry-grid about-industry-grid container">
        <?php foreach ($industries as $ind): ?>
        <div class="industry-item reveal">
            <div class="industry-icon"><?= e($ind['icon_label']) ?></div>
            <div class="industry-name"><?= e(tf($ind, 'name')) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA -->
<section class="cta-band cta-band--image about-page-cta" style="background-image:url('<?= e($ctaBackgroundImage) ?>');">
    <div class="container">
        <div class="eyebrow"><?= e(t('nav_contact')) ?></div>
        <h2 class="section-title"><?= e(tf($ctaBlock, 'title')) ?></h2>
        <p class="section-subtitle"><?= e(tf($ctaBlock, 'subtitle')) ?></p>
        <div class="cta-band-actions">
            <a href="<?= e(base_url('contact.php')) ?>" class="btn btn--outline-light">
                <?= e(t('about_cta_button')) ?> <span class="btn-arrow">&#8594;</span>
            </a>
        </div>
    </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
