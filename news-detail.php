<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$slug = clean($_GET['slug'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM news WHERE slug = :slug AND status = 1");
$stmt->execute([':slug' => $slug]);
$article = $stmt->fetch();

if (!$article) {
    http_response_code(404);
    $pageTitle = t('error_404_title');
    $activePage = 'news';
    include __DIR__ . '/includes/header.php';
    echo '<div class="empty-state container"><div class="code">404</div><h2 class="section-title" style="margin-top:20px;">' . e(t('error_404_title')) . '</h2><p style="margin-top:12px;">' . e(t('error_404_text')) . '</p><a href="' . e(base_url('news.php')) . '" class="btn btn--outline-dark" style="margin-top:32px;">' . e(t('news_back')) . '</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE status = 1 AND id != :id ORDER BY publish_date DESC LIMIT 3");
$stmt->execute([':id' => $article['id']]);
$related = $stmt->fetchAll();

$pageTitle       = tf($article, 'title');
$pageDescription = truncate(tf($article, 'content'), 150);
$activePage      = 'news';
$pageHeaderImage = page_header_image_url($article['image'] ?? null, 'news', 0);

include __DIR__ . '/includes/header.php';
?>

<section class="page-header" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
    <div class="container">
        <div class="eyebrow"><?= e(tf($article, 'category')) ?></div>
        <h1 class="page-title"><?= e(tf($article, 'title')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <a href="<?= e(base_url('news.php')) ?>"><?= e(t('nav_news')) ?></a>
            <span class="sep">/</span>
            <span><?= e(tf($article, 'title')) ?></span>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="article-body">
            <div class="article-meta">
                <span><?= e(t('news_published_on')) ?>: <?= e(format_date($article['publish_date'], $CURRENT_LANG)) ?></span>
                <span class="news-cat"><?= e(tf($article, 'category')) ?></span>
            </div>
            <img src="<?= e(image_url($article['image'], 'news')) ?>" alt="<?= e(tf($article, 'title')) ?>">
            <p><?= nl2br(e(tf($article, 'content'))) ?></p>

            <a href="<?= e(base_url('news.php')) ?>" class="text-link">
                &#8592; <?= e(t('news_back')) ?>
            </a>
        </div>

        <?php if (!empty($related)): ?>
        <div class="section-head reveal" style="margin-top:100px;">
            <div class="eyebrow">RELATED</div>
            <h2 class="section-title"><?= e(t('news_title')) ?></h2>
        </div>
        <div class="reveal">
            <?php foreach ($related as $r): ?>
            <a href="<?= e(base_url('news-detail.php?slug=' . urlencode($r['slug']))) ?>" class="news-row">
                <div class="news-date"><?= e(format_date($r['publish_date'], $CURRENT_LANG)) ?></div>
                <div class="news-cat"><?= e(tf($r, 'category')) ?></div>
                <div class="news-title"><?= e(tf($r, 'title')) ?></div>
                <div class="news-arrow">&#8594;</div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
