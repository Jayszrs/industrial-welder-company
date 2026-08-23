<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
$slug = clean($_GET['slug'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM products WHERE slug = :slug AND status = 1");
$stmt->execute([':slug' => $slug]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $pageTitle = t('error_404_title');
    $activePage = 'products';
    include __DIR__ . '/includes/header.php';
    echo '<div class="empty-state container"><div class="code">404</div><h2 class="section-title" style="margin-top:20px;">' . e(t('error_404_title')) . '</h2><p style="margin-top:12px;">' . e(t('error_404_text')) . '</p><a href="' . e(base_url('products.php')) . '" class="btn btn--outline-dark" style="margin-top:32px;">' . e(t('products_back')) . '</a></div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$category = null;
if (!empty($product['category_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM product_categories WHERE id = :id");
    $stmt->execute([':id' => $product['category_id']]);
    $category = $stmt->fetch();
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE status = 1 AND id != :id " . ($product['category_id'] ? "AND category_id = :cid " : "") . "ORDER BY created_at DESC LIMIT 3");
$params = [':id' => $product['id']];
if ($product['category_id']) { $params[':cid'] = $product['category_id']; }
$stmt->execute($params);
$related = $stmt->fetchAll();

$specRows = [
    [t('product_detail_model'), $product['model']],
    ['Power Supply / ' . ($CURRENT_LANG === 'ja' ? '電源' : 'Power Supply'), $product['spec_power']],
    ['Rated Output / ' . ($CURRENT_LANG === 'ja' ? '定格出力' : 'Rated Output'), $product['spec_output']],
    ['Current Range / ' . ($CURRENT_LANG === 'ja' ? '電流範囲' : 'Current Range'), $product['spec_current_range']],
    ['Dimensions / ' . ($CURRENT_LANG === 'ja' ? '外形寸法' : 'Dimensions'), $product['spec_dimensions']],
    ['Weight / ' . ($CURRENT_LANG === 'ja' ? '質量' : 'Weight'), $product['spec_weight']],
];

$features = array_filter(array_map('trim', explode("\n", tf($product, 'features'))));

$pageTitle       = tf($product, 'name');
$pageDescription = truncate(tf($product, 'short_description'), 150);
$activePage      = 'products';
$productImage    = image_url($product['image'], 'products');
$productInquiryUrl = base_url('contact.php?inquiry_type=product&product=' . urlencode(tf($product, 'name')));
$heroFacts = array_filter([
    [t('product_detail_model'), $product['model']],
    [$CURRENT_LANG === 'ja' ? '定格出力' : 'Rated Output', $product['spec_output']],
    [$CURRENT_LANG === 'ja' ? '質量' : 'Weight', $product['spec_weight']],
], static fn(array $fact): bool => trim((string) $fact[1]) !== '');

include __DIR__ . '/includes/header.php';
?>

<section class="product-detail-hero" style="--product-hero-bg:url('<?= e($productImage) ?>');" role="img" aria-label="<?= e(tf($product, 'name')) ?>">
    <div class="container product-detail-hero__container">
        <div class="breadcrumb product-detail-hero__breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <a href="<?= e(base_url('products.php')) ?>"><?= e(t('nav_products')) ?></a>
            <span class="sep">/</span>
            <span><?= e(tf($product, 'name')) ?></span>
        </div>
        <div class="product-detail-hero__content">
            <div class="eyebrow"><?= e($category ? tf($category, 'name') : t('products_eyebrow')) ?></div>
            <h1><?= e(tf($product, 'name')) ?></h1>
            <?php if (!empty(tf($product, 'short_description'))): ?>
            <p class="product-detail-hero__lead"><?= e(tf($product, 'short_description')) ?></p>
            <?php endif; ?>

            <?php if (!empty($heroFacts)): ?>
            <dl class="product-detail-hero__facts">
                <?php foreach ($heroFacts as [$label, $value]): ?>
                <div>
                    <dt><?= e($label) ?></dt>
                    <dd><?= e($value) ?></dd>
                </div>
                <?php endforeach; ?>
            </dl>
            <?php endif; ?>

            <div class="product-detail-hero__actions">
                <a href="<?= e($productInquiryUrl) ?>" class="btn btn--primary">
                    <?= e(t('product_detail_contact_cta')) ?> <span class="btn-arrow">&#8594;</span>
                </a>
                <a href="<?= e(base_url('products.php')) ?>" class="btn btn--outline-light">
                    <span class="btn-arrow">&#8592;</span> <?= e(t('products_back')) ?>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section section--white product-detail-content">
    <div class="container">
        <div class="detail-grid">
            <div>
                <div class="detail-block reveal">
                    <h3><?= $CURRENT_LANG === 'ja' ? '製品概要' : 'Product Overview' ?></h3>
                    <p><?= nl2br(e(tf($product, 'description'))) ?></p>
                </div>

                <?php if (!empty($features)): ?>
                <div class="detail-block reveal">
                    <h3><?= e(t('product_detail_features')) ?></h3>
                    <ul class="feature-list">
                        <?php foreach ($features as $f): ?>
                        <li><?= e($f) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty(tf($product, 'application'))): ?>
                <div class="detail-block reveal">
                    <h3><?= e(t('product_detail_application')) ?></h3>
                    <p><?= nl2br(e(tf($product, 'application'))) ?></p>
                </div>
                <?php endif; ?>

                <div class="detail-block reveal">
                    <h3><?= e(t('product_detail_specification')) ?></h3>
                    <table class="spec-table">
                        <?php foreach ($specRows as [$label, $value]): if (empty($value)) continue; ?>
                        <tr><th><?= e($label) ?></th><td><?= e($value) ?></td></tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <div class="detail-sidebar reveal">
                <h4><?= e(tf($product, 'name')) ?></h4>
                <p class="detail-sidebar-copy"><?= $CURRENT_LANG === 'ja' ? '仕様確認、導入相談、お見積もりについて技術担当者がご案内します。' : 'Talk with our engineering team about specifications, implementation, or a tailored quotation.' ?></p>
                <div class="detail-sidebar-row"><span class="k"><?= e(t('product_detail_category')) ?></span><span class="v"><?= e($category ? tf($category, 'name') : '—') ?></span></div>
                <div class="detail-sidebar-row"><span class="k"><?= e(t('product_detail_model')) ?></span><span class="v"><?= e($product['model']) ?></span></div>
                <div class="detail-sidebar-row"><span class="k"><?= e(t('product_detail_manufacturer')) ?></span><span class="v"><?= e($product['manufacturer']) ?></span></div>
                <a href="<?= e($productInquiryUrl) ?>" class="btn btn--primary btn--block">
                    <?= e(t('product_detail_contact_cta')) ?>
                </a>
            </div>
        </div>

        <?php if (!empty($related)): ?>
        <div class="section-head reveal" style="margin-top:100px;">
            <div class="eyebrow">RELATED</div>
            <h2 class="section-title"><?= e(t('product_detail_related')) ?></h2>
        </div>
        <div class="grid-cards">
            <?php foreach ($related as $r): ?>
            <div class="card reveal">
                <a href="<?= e(base_url('product-detail.php?slug=' . urlencode($r['slug']))) ?>" class="card-media">
                    <img src="<?= e(image_url($r['image'], 'products')) ?>" alt="<?= e(tf($r, 'name')) ?>" loading="lazy">
                </a>
                <div class="card-body">
                    <div class="card-meta"><?= e($r['manufacturer']) ?></div>
                    <h3 class="card-title"><?= e(tf($r, 'name')) ?></h3>
                    <div class="card-foot">
                        <a href="<?= e(base_url('product-detail.php?slug=' . urlencode($r['slug']))) ?>" class="text-link">
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
