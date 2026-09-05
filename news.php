<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

// News is currently disabled in the public website; keep its data intact.
header('Location: ' . base_url('index.php'), true, 302);
exit;

$pdo = getPDO();
$newsItems = $pdo->query("SELECT * FROM news WHERE status = 1 ORDER BY publish_date DESC")->fetchAll();
$pageHeaderImage = page_header_image_url($newsItems[0]['image'] ?? null, 'news', 0);

$pageTitle       = t('news_title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'news';

include __DIR__ . '/includes/header.php';
?>

<section class="page-header" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
    <div class="container">
        <div class="eyebrow"><?= e(t('news_eyebrow')) ?></div>
        <h1 class="page-title"><?= e(t('news_title')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <span><?= e(t('nav_news')) ?></span>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
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

<?php include __DIR__ . '/includes/footer.php'; ?>
