<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
ensure_hero_slides_table($pdo);

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

$heroSlideRows = $pdo->query("SELECT * FROM hero_slides WHERE status = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
$heroSlides = [];
foreach ($heroSlideRows as $i => $slideRow) {
    $heroSlides[] = [
        'src' => image_url($slideRow['image'], 'hero-slides'),
        'label' => 'Hero slide ' . ($i + 1),
    ];
}

// First-run examples. As soon as admin slides exist, the database becomes the only source.
if (empty($heroSlides)) {
    $heroSlides = [
        ['src' => base_url('assets/images/hero-welding-v2.jpg'), 'label' => 'Manual precision welding'],
        ['src' => base_url('assets/images/hero-robotic-v2.jpg'), 'label' => 'Robotic welding automation'],
        ['src' => base_url('assets/images/hero-machining-v2.jpg'), 'label' => 'Precision industrial machining'],
    ];

    if (!empty($hero['image']) && $hero['image'] !== 'hero-bg.svg') {
        array_unshift($heroSlides, [
            'src' => image_url($hero['image'], 'homepage'),
            'label' => tf($hero, 'title'),
        ]);
    }
}

$locationAddress = get_setting('address_' . $CURRENT_LANG, get_setting('address_en'));
$mapsQuery       = rawurlencode(str_replace(["\r", "\n"], ' ', $locationAddress));
$mapsUrl         = 'https://www.google.com/maps/search/?api=1&query=' . $mapsQuery;
$mapsEmbedUrl    = 'https://www.google.com/maps?q=' . $mapsQuery . '&output=embed';

include __DIR__ . '/includes/header.php';
?>

<!-- ============================== HERO ============================== -->
<section class="hero" data-hero-slider data-interval="3000" aria-label="Company introduction">
    <div class="hero-slides" aria-hidden="true">
        <?php foreach ($heroSlides as $i => $slide): ?>
        <div class="hero-media hero-slide<?= $i === 0 ? ' is-active' : '' ?>"
             <?= $i === 0
                 ? 'style="background-image:url(\'' . e($slide['src']) . '\');"'
                 : 'data-hero-bg="' . e($slide['src']) . '"' ?>></div>
        <?php endforeach; ?>
    </div>
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
        <a href="<?= e(base_url('technology.php#services')) ?>" class="service-card reveal" aria-label="<?= e(tf($svc, 'title')) ?>">
            <div class="service-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></div>
            <div class="service-media">
                <img src="<?= e(image_url($svc['image'], 'services')) ?>" alt="<?= e(tf($svc, 'title')) ?>" loading="lazy">
            </div>
            <div class="service-title-jp"><?= e(tf($svc, 'title')) ?></div>
            <div class="service-title-en"><?= e($svc['title_en']) ?></div>
            <p class="service-desc"><?= e(truncate(tf($svc, 'description'), 70)) ?></p>
            <div class="service-arrow">&#8594;</div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============================== WELDING TECHNOLOGY ============================== -->
<section class="section section--dark<?= !empty($strength['image']) ? ' section--content-bg' : '' ?>"<?= !empty($strength['image']) ? ' style="background-image:url(\'' . e(image_url($strength['image'], 'homepage')) . '\');"' : '' ?>>
    <div class="container">
        <div class="section-head reveal">
            <div class="eyebrow"><?= e(t('tech_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(t('tech_title')) ?></h2>
            <p class="section-subtitle"><?= e(t('tech_subtitle')) ?></p>
        </div>

        <div class="tech-list" data-tech-accordion>
            <?php foreach ($technologies as $i => $tech): ?>
            <div class="tech-item reveal" id="technology-<?= (int) $tech['id'] ?>">
                <button class="tech-row" type="button" data-tech-toggle aria-expanded="false" aria-controls="technology-detail-<?= (int) $tech['id'] ?>">
                    <span class="tech-index"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                    <span>
                        <span class="tech-name-jp"><?= e(tf($tech, 'name')) ?></span>
                        <span class="tech-name-en"><?= e($tech['name_en']) ?></span>
                    </span>
                    <span class="tech-desc"><?= e(truncate(tf($tech, 'description'), 90)) ?></span>
                    <span class="tech-thumb"><img src="<?= e(image_url($tech['image'], 'technologies')) ?>" alt="" loading="lazy"></span>
                    <span class="tech-toggle-icon" aria-hidden="true"></span>
                </button>
                <div class="tech-detail" id="technology-detail-<?= (int) $tech['id'] ?>" hidden>
                    <div class="tech-detail-inner">
                        <p><?= e(tf($tech, 'description')) ?></p>
                        <a href="<?= e(base_url('contact.php?inquiry_type=general')) ?>" class="text-link">
                            <?= e(t('hero_cta_contact')) ?> <span class="arrow">&#8594;</span>
                        </a>
                    </div>
                </div>
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

<!-- ============================== LOCATION ============================== -->
<section class="section section--white location-section" id="location">
    <div class="container location-grid">
        <div class="location-copy reveal">
            <div class="eyebrow"><?= $CURRENT_LANG === 'ja' ? 'アクセス' : 'Location' ?></div>
            <h2 class="section-title"><?= $CURRENT_LANG === 'ja' ? '私たちの拠点' : 'Visit Our Facility' ?></h2>
            <p class="section-subtitle"><?= e($locationAddress) ?></p>
            <a href="<?= e($mapsUrl) ?>" class="btn btn--outline-dark" target="_blank" rel="noopener noreferrer">
                <?= $CURRENT_LANG === 'ja' ? 'Google マップで見る' : 'Open in Google Maps' ?> <span class="btn-arrow">&#8599;</span>
            </a>
        </div>
        <div class="location-map reveal">
            <iframe src="<?= e($mapsEmbedUrl) ?>" title="Google Maps preview for <?= e($locationAddress) ?>"
                    loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
        </div>
    </div>
</section>

<!-- ============================== CONTACT CTA ============================== -->
<section class="cta-band<?= !empty($ctaBlock['image']) ? ' cta-band--image' : '' ?>"<?= !empty($ctaBlock['image']) ? ' style="background-image:url(\'' . e(image_url($ctaBlock['image'], 'homepage')) . '\');"' : '' ?>>
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
