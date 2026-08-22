<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();

$hero      = $pdo->query("SELECT * FROM homepage_content WHERE section_key = 'hero'")->fetch();
$aboutHome = $pdo->query("SELECT * FROM homepage_content WHERE section_key = 'about'")->fetch();
$strength  = $pdo->query("SELECT * FROM homepage_content WHERE section_key = 'strength'")->fetch();
$ctaBlock  = $pdo->query("SELECT * FROM homepage_content WHERE section_key = 'cta'")->fetch();

$services     = $pdo->query("SELECT * FROM services WHERE status = 1 ORDER BY sort_order ASC LIMIT 6")->fetchAll();
$technologies = $pdo->query("SELECT * FROM technologies WHERE status = 1 ORDER BY sort_order ASC LIMIT 6")->fetchAll();
$products     = $pdo->query("SELECT * FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();
$facilities   = $pdo->query("SELECT * FROM facilities WHERE status = 1 ORDER BY sort_order ASC LIMIT 4")->fetchAll();
$industries   = $pdo->query("SELECT * FROM industries ORDER BY sort_order ASC")->fetchAll();
$stats        = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC")->fetchAll();
$projects     = $pdo->query("SELECT * FROM projects WHERE status = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();
$newsItems    = $pdo->query("SELECT * FROM news WHERE status = 1 ORDER BY publish_date DESC LIMIT 3")->fetchAll();

$pageTitle       = tf(['title_ja' => get_setting('company_tagline_ja'), 'title_en' => get_setting('company_tagline_en')], 'title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, get_setting('meta_description_ja'));
$activePage      = 'home';
$bodyClass       = 'has-transparent-header';

include __DIR__ . '/includes/header.php';
?>

<!-- ============================== HERO ============================== -->
<section class="hero">
    <div class="hero-media" style="background-image:url('<?= e(image_url($hero['image'], 'homepage')) ?>');"></div>
    <div class="hero-overlay"></div>
    <div class="hero-inner">
        <div class="hero-eyebrow"><?= e(t('hero_eyebrow')) ?></div>
        <h1 class="hero-title"><?php echo nl2br(e(tf($hero, 'title'))); ?></h1>
        <p class="hero-subtitle"><?= e(tf($hero, 'content')) ?></p>
        <div class="hero-actions">
            <a href="<?= e(base_url('technology.php')) ?>" class="btn btn--primary">
                <?= e(t('hero_cta_technology')) ?> <span class="btn-arrow">&#8594;</span>
            </a>
            <a href="<?= e(base_url('contact.php')) ?>" class="btn btn--outline-light">
                <?= e(t('hero_cta_contact')) ?>
            </a>
        </div>
    </div>
    <div class="hero-scroll">
        <span><?= e(t('hero_scroll')) ?></span>
        <span class="hero-scroll-line"></span>
    </div>
</section>

<!-- ============================== ABOUT ============================== -->
<section class="section section--white">
    <div class="container">
        <div class="about-grid">
            <div class="about-visual reveal">
                <img src="<?= e(image_url($aboutHome['image'], 'homepage')) ?>" alt="<?= e(tf($aboutHome, 'title')) ?>">
                <div class="about-number">01 / SINCE <?= e(substr(get_setting('established'), 0, 4)) ?></div>
            </div>
            <div class="about-text-block reveal">
                <div class="eyebrow"><?= e(t('about_eyebrow')) ?></div>
                <h2 class="section-title" style="margin-bottom:28px;"><?= e(tf($aboutHome, 'title')) ?></h2>
                <p class="lead"><?= e(mb_substr(tf($aboutHome, 'content'), 0, 80)) ?>…</p>
                <p><?= e(tf($aboutHome, 'content')) ?></p>
                <a href="<?= e(base_url('about.php')) ?>" class="text-link">
                    <?= e(t('about_cta')) ?> <span class="arrow">&#8594;</span>
                </a>

                <div class="about-meta">
                    <?php foreach (array_slice($stats, 0, 3) as $s): ?>
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

<!-- ============================== SERVICES ============================== -->
<section class="section section--off">
    <div class="container">
        <div class="section-head section-head--split reveal">
            <div>
                <div class="eyebrow"><?= e(t('services_eyebrow')) ?></div>
                <h2 class="section-title"><?= e(t('services_title')) ?></h2>
            </div>
            <a href="<?= e(base_url('technology.php')) ?>" class="text-link">
                <?= e(t('services_view_all')) ?> <span class="arrow">&#8594;</span>
            </a>
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
            <p class="service-desc"><?= e(truncate(tf($svc, 'description'), 70)) ?></p>
            <div class="service-arrow">&#8594;</div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============================== WELDING TECHNOLOGY ============================== -->
<section class="section section--dark">
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
                <div class="tech-desc"><?= e(truncate(tf($tech, 'description'), 90)) ?></div>
                <div class="tech-thumb"><img src="<?= e(image_url($tech['image'], 'technologies')) ?>" alt="" loading="lazy"></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:48px;">
            <a href="<?= e(base_url('technology.php')) ?>" class="btn btn--outline-light">
                <?= e(t('services_view_all')) ?> <span class="btn-arrow">&#8594;</span>
            </a>
        </div>
    </div>
</section>

<!-- ============================== PRODUCTS ============================== -->
<section class="section section--white">
    <div class="container">
        <div class="section-head section-head--split reveal">
            <div>
                <div class="eyebrow"><?= e(t('products_eyebrow')) ?></div>
                <h2 class="section-title"><?= e(t('products_title')) ?></h2>
            </div>
            <a href="<?= e(base_url('products.php')) ?>" class="text-link">
                <?= e(t('services_view_all')) ?> <span class="arrow">&#8594;</span>
            </a>
        </div>

        <div class="grid-cards">
            <?php foreach ($products as $p): ?>
            <div class="card reveal">
                <a href="<?= e(base_url('product-detail.php?slug=' . urlencode($p['slug']))) ?>" class="card-media">
                    <img src="<?= e(image_url($p['image'], 'products')) ?>" alt="<?= e(tf($p, 'name')) ?>" loading="lazy">
                    <?php if (!empty($p['model'])): ?><span class="card-tag"><?= e($p['model']) ?></span><?php endif; ?>
                </a>
                <div class="card-body">
                    <div class="card-meta"><?= e($p['manufacturer']) ?></div>
                    <h3 class="card-title"><?= e(tf($p, 'name')) ?></h3>
                    <p class="card-text"><?= e(truncate(tf($p, 'short_description'), 90)) ?></p>
                    <div class="card-foot">
                        <a href="<?= e(base_url('product-detail.php?slug=' . urlencode($p['slug']))) ?>" class="text-link">
                            <?= e(t('products_view_details')) ?> <span class="arrow">&#8594;</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================== FACILITY ============================== -->
<section class="section section--gray">
    <div class="container">
        <div class="section-head section-head--split reveal">
            <div>
                <div class="eyebrow"><?= e(t('facility_eyebrow')) ?></div>
                <h2 class="section-title"><?= e(t('facility_title')) ?></h2>
                <p class="section-subtitle"><?= e(t('facility_subtitle')) ?></p>
            </div>
            <a href="<?= e(base_url('facility.php')) ?>" class="text-link">
                <?= e(t('services_view_all')) ?> <span class="arrow">&#8594;</span>
            </a>
        </div>

        <div class="grid-cards">
            <?php foreach ($facilities as $f): ?>
            <div class="card reveal">
                <div class="card-media">
                    <img src="<?= e(image_url($f['image'], 'facilities')) ?>" alt="<?= e(tf($f, 'machine_name')) ?>" loading="lazy">
                </div>
                <div class="card-body">
                    <div class="card-meta"><?= e($f['manufacturer']) ?> — <?= e($f['model']) ?></div>
                    <h3 class="card-title"><?= e(tf($f, 'machine_name')) ?></h3>
                    <p class="card-text"><?= e(truncate(tf($f, 'description'), 80)) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================== INDUSTRIES ============================== -->
<section class="section section--white section--tight">
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

<!-- ============================== STRENGTH / STATS ============================== -->
<section class="section section--dark">
    <div class="container">
        <div class="section-head reveal" style="max-width:640px;">
            <div class="eyebrow"><?= e(t('strength_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(tf($strength, 'title')) ?></h2>
            <p class="section-subtitle"><?= e(t('strength_subtitle')) ?></p>
        </div>

        <div class="stats-grid">
            <?php foreach ($stats as $s): ?>
            <div class="stat-item reveal">
                <div class="stat-num" data-counter="<?= e($s['number_value']) ?>">0</div>
                <div class="stat-label"><?= e(tf($s, 'label')) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================== PROJECTS ============================== -->
<section class="section section--white">
    <div class="container">
        <div class="section-head section-head--split reveal">
            <div>
                <div class="eyebrow"><?= e(t('projects_eyebrow')) ?></div>
                <h2 class="section-title"><?= e(t('projects_title')) ?></h2>
            </div>
            <a href="<?= e(base_url('projects.php')) ?>" class="text-link">
                <?= e(t('services_view_all')) ?> <span class="arrow">&#8594;</span>
            </a>
        </div>

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
                    <p class="card-text"><?= e(truncate(tf($proj, 'description'), 80)) ?></p>
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

<!-- ============================== NEWS ============================== -->
<section class="section section--off">
    <div class="container">
        <div class="section-head section-head--split reveal">
            <div>
                <div class="eyebrow"><?= e(t('news_eyebrow')) ?></div>
                <h2 class="section-title"><?= e(t('news_title')) ?></h2>
            </div>
            <a href="<?= e(base_url('news.php')) ?>" class="text-link">
                <?= e(t('news_view_all')) ?> <span class="arrow">&#8594;</span>
            </a>
        </div>

        <?php if (empty($newsItems)): ?>
            <p style="color:var(--c-gray);"><?= e(t('news_no_news')) ?></p>
        <?php else: ?>
        <div class="reveal">
            <?php foreach ($newsItems as $n): ?>
            <a href="<?= e(base_url('news-detail.php?slug=' . urlencode($n['slug']))) ?>" class="news-row">
                <div class="news-date"><?= e(format_date($n['publish_date'], $CURRENT_LANG)) ?></div>
                <div class="news-cat"><?= e(tf($n, 'category')) ?></div>
                <div class="news-title"><?= e(tf($n, 'title')) ?></div>
                <div class="news-arrow">&#8594;</div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================== CONTACT CTA ============================== -->
<section class="cta-band">
    <div class="container">
        <h2 class="section-title"><?= e(tf($ctaBlock, 'title')) ?></h2>
        <p class="section-subtitle" style="margin:18px auto 0; color:rgba(14,15,17,0.7);"><?= e(tf($ctaBlock, 'subtitle')) ?></p>
        <div class="cta-band-actions">
            <a href="<?= e(base_url('contact.php')) ?>" class="btn btn--outline-dark">
                <?= e(t('hero_cta_contact')) ?> <span class="btn-arrow">&#8594;</span>
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
