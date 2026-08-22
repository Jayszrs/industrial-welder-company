<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$aboutHome = $pdo->query("SELECT * FROM homepage_content WHERE section_key = 'about'")->fetch();
$stats     = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC")->fetchAll();
$industries = $pdo->query("SELECT * FROM industries ORDER BY sort_order ASC")->fetchAll();
$pageHeaderImage = page_header_image_url($aboutHome['image'] ?? null, 'homepage', 0);

$pageTitle       = t('about_page_title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'about';

include __DIR__ . '/includes/header.php';
?>

<section class="page-header" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
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

<!-- Company introduction -->
<section class="section section--white">
    <div class="container">
        <div class="about-grid">
            <div class="about-visual reveal">
                <img src="<?= e(image_url($aboutHome['image'], 'homepage')) ?>" alt="">
            </div>
            <div class="about-text-block reveal">
                <div class="eyebrow">01 / <?= e(t('about_intro_title')) ?></div>
                <h2 class="section-title" style="margin-bottom:28px;"><?= e(tf($aboutHome, 'title')) ?></h2>
                <p><?= nl2br(e(tf($aboutHome, 'content'))) ?></p>

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
<section class="section section--dark">
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('about_philosophy_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('about_philosophy_title')) ?></h2>
        </div>

        <div class="grid-3" style="margin-top:8px;">
            <div class="service-card reveal">
                <div class="service-num">01</div>
                <div class="service-title-jp"><?= e(t('about_mission_title')) ?></div>
                <div class="service-title-en">MISSION</div>
                <p class="service-desc" style="margin-top:16px;">
                    <?= $CURRENT_LANG === 'ja'
                        ? '確かな技術と誠実なものづくりを通じて、お客様の生産現場に信頼できる価値を提供し続けます。'
                        : 'To continually deliver dependable value to our clients\' production floors through solid technology and honest craftsmanship.' ?>
                </p>
            </div>
            <div class="service-card reveal reveal-delay-1">
                <div class="service-num">02</div>
                <div class="service-title-jp"><?= e(t('about_vision_title')) ?></div>
                <div class="service-title-en">VISION</div>
                <p class="service-desc" style="margin-top:16px;">
                    <?= $CURRENT_LANG === 'ja'
                        ? '溶接技術と産業機械ソリューションの分野で、日本のものづくりを支える存在であり続けること。'
                        : 'To remain a company that supports Japanese manufacturing through welding technology and industrial machinery solutions.' ?>
                </p>
            </div>
            <div class="service-card reveal reveal-delay-2">
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

<!-- Company profile table -->
<section class="section section--white">
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('about_profile_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('about_profile_title')) ?></h2>
        </div>

        <table class="spec-table reveal" style="max-width:820px;">
            <tr><th><?= e(t('about_profile_company_name')) ?></th><td><?= e(get_setting('company_name_' . $CURRENT_LANG)) ?></td></tr>
            <tr><th><?= e(t('about_profile_representative')) ?></th><td><?= e(get_setting('representative_' . $CURRENT_LANG)) ?></td></tr>
            <tr><th><?= e(t('about_profile_established')) ?></th><td><?= e(get_setting('established')) ?></td></tr>
            <tr><th><?= e(t('about_profile_business')) ?></th><td><?= e(get_setting('business_activities_' . $CURRENT_LANG)) ?></td></tr>
            <tr><th><?= e(t('about_profile_office')) ?></th><td><?= e(get_setting('address_' . $CURRENT_LANG)) ?></td></tr>
            <tr><th><?= e(t('about_profile_tel')) ?></th><td><?= e(get_setting('phone')) ?></td></tr>
            <tr><th><?= e(t('about_profile_email')) ?></th><td><?= e(get_setting('email')) ?></td></tr>
            <tr><th><?= e(t('about_profile_website')) ?></th><td><?= e(get_setting('website')) ?></td></tr>
        </table>

        <p style="margin-top:24px; font-size:12.5px; color:var(--c-gray);">
            <?= $CURRENT_LANG === 'ja' ? '※ 上記は' : '*' ?> <?= e(get_setting('sample_data_notice')) ?>
        </p>
    </div>
</section>

<!-- Industries -->
<section class="section section--off section--tight">
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('industries_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('industries_title')) ?></h2>
        </div>
    </div>
    <div class="industry-grid">
        <?php foreach ($industries as $ind): ?>
        <div class="industry-item reveal">
            <div class="industry-icon"><?= e($ind['icon_label']) ?></div>
            <div class="industry-name"><?= e(tf($ind, 'name')) ?></div>
        </div>
        <?php endforeach; ?>
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
