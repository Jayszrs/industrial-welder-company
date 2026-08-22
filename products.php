<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$categories = $pdo->query("SELECT * FROM product_categories ORDER BY id ASC")->fetchAll();

$categorySlug = isset($_GET['category']) ? clean($_GET['category']) : '';
$activeCategory = null;

if ($categorySlug !== '') {
    foreach ($categories as $c) {
        if ($c['slug'] === $categorySlug) { $activeCategory = $c; break; }
    }
}

if ($activeCategory) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE status = 1 AND category_id = :cid ORDER BY created_at DESC");
    $stmt->execute([':cid' => $activeCategory['id']]);
} else {
    $stmt = $pdo->query("SELECT * FROM products WHERE status = 1 ORDER BY created_at DESC");
}
$products = $stmt->fetchAll();
$pageHeaderImage = page_header_image_url($products[0]['image'] ?? null, 'products', 2);

$pageTitle       = t('products_title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'products';

include __DIR__ . '/includes/header.php';
?>

<section class="page-header" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
    <div class="container">
        <div class="eyebrow"><?= e(t('products_eyebrow')) ?></div>
        <h1 class="page-title"><?= e(t('products_title')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <span><?= e(t('nav_products')) ?></span>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="filter-bar reveal">
            <a href="<?= e(base_url('products.php')) ?>" class="<?= !$activeCategory ? 'is-active' : '' ?>">
                <?= e(t('products_all_categories')) ?>
            </a>
            <?php foreach ($categories as $c): ?>
            <a href="<?= e(base_url('products.php?category=' . urlencode($c['slug']))) ?>" class="<?= ($activeCategory && $activeCategory['id'] === $c['id']) ? 'is-active' : '' ?>">
                <?= e(tf($c, 'name')) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
            <p style="color:var(--c-gray);"><?= e(t('products_no_products')) ?></p>
        <?php else: ?>
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
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
