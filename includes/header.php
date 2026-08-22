<?php
/**
 * Shared header include.
 * Expects the calling page to optionally set, before include:
 *   $pageTitle        (string)
 *   $pageDescription  (string)
 *   $activePage        (string) one of: home, about, technology, products, facility, projects, news, contact
 *   $bodyClass         (string) optional extra <body> class (e.g. for transparent hero header)
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/language.php';

$activePage       = $activePage ?? '';
$pageTitle        = $pageTitle ?? t('site_name');
$pageDescription  = $pageDescription ?? get_setting('meta_description_' . $CURRENT_LANG, get_setting('meta_description_ja'));
$companyNameFull  = get_setting('company_name_' . $CURRENT_LANG, t('site_name'));
?>
<!DOCTYPE html>
<html lang="<?= e($CURRENT_LANG) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e($companyNameFull) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<meta name="robots" content="index, follow">
<meta property="og:title" content="<?= e($pageTitle) ?> | <?= e($companyNameFull) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:type" content="website">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><rect width=%22100%22 height=%22100%22 fill=%22%230E0F11%22/><rect x=%2230%22 y=%2230%22 width=%2240%22 height=%2240%22 fill=%22%23F5C400%22/></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+JP:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
</head>
<body class="<?= e($bodyClass ?? '') ?>">

<header class="site-header" id="site-header">
    <div class="container header-inner">
        <a href="<?= e(base_url('index.php')) ?>" class="brand">
            <span class="brand-mark">YAMATO<span>.</span></span>
            <span class="brand-sub">WELDING INDUSTRIES</span>
        </a>

        <nav class="main-nav">
            <ul>
                <li><a href="<?= e(base_url('index.php')) ?>" class="<?= $activePage === 'home' ? 'is-active' : '' ?>"><?= e(t('nav_home')) ?></a></li>
                <li><a href="<?= e(base_url('about.php')) ?>" class="<?= $activePage === 'about' ? 'is-active' : '' ?>"><?= e(t('nav_about')) ?></a></li>
                <li><a href="<?= e(base_url('technology.php')) ?>" class="<?= $activePage === 'technology' ? 'is-active' : '' ?>"><?= e(t('nav_technology')) ?></a></li>
                <li><a href="<?= e(base_url('products.php')) ?>" class="<?= $activePage === 'products' ? 'is-active' : '' ?>"><?= e(t('nav_products')) ?></a></li>
                <li><a href="<?= e(base_url('projects.php')) ?>" class="<?= $activePage === 'projects' ? 'is-active' : '' ?>"><?= e(t('nav_projects')) ?></a></li>
                <li><a href="<?= e(base_url('news.php')) ?>" class="<?= $activePage === 'news' ? 'is-active' : '' ?>"><?= e(t('nav_news')) ?></a></li>
            </ul>

            <div class="lang-switch">
                <a href="<?= e(langSwitchUrl('ja')) ?>" class="<?= $CURRENT_LANG === 'ja' ? 'is-active' : '' ?>"><?= e(t('lang_jp')) ?></a>
                <span class="lang-divider">|</span>
                <a href="<?= e(langSwitchUrl('en')) ?>" class="<?= $CURRENT_LANG === 'en' ? 'is-active' : '' ?>"><?= e(t('lang_en')) ?></a>
            </div>

            <a href="<?= e(base_url('contact.php')) ?>" class="btn header-cta">
                <?= e(t('nav_contact')) ?>
            </a>
        </nav>

        <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<div class="mobile-nav" id="mobile-nav">
    <ul>
        <li><a href="<?= e(base_url('index.php')) ?>"><?= e(t('nav_home')) ?></a></li>
        <li><a href="<?= e(base_url('about.php')) ?>"><?= e(t('nav_about')) ?></a></li>
        <li><a href="<?= e(base_url('technology.php')) ?>"><?= e(t('nav_technology')) ?></a></li>
        <li><a href="<?= e(base_url('products.php')) ?>"><?= e(t('nav_products')) ?></a></li>
        <li><a href="<?= e(base_url('projects.php')) ?>"><?= e(t('nav_projects')) ?></a></li>
        <li><a href="<?= e(base_url('news.php')) ?>"><?= e(t('nav_news')) ?></a></li>
        <li><a href="<?= e(base_url('contact.php')) ?>" class="mobile-contact-link"><?= e(t('nav_contact')) ?> <span aria-hidden="true">&#8594;</span></a></li>
    </ul>
    <div class="mobile-nav-footer">
        <div class="lang-switch">
            <a href="<?= e(langSwitchUrl('ja')) ?>" class="<?= $CURRENT_LANG === 'ja' ? 'is-active' : '' ?>" style="color:<?= $CURRENT_LANG === 'ja' ? '#F5C400' : '#fff' ?>"><?= e(t('lang_jp')) ?></a>
            <span class="lang-divider">|</span>
            <a href="<?= e(langSwitchUrl('en')) ?>" class="<?= $CURRENT_LANG === 'en' ? 'is-active' : '' ?>" style="color:<?= $CURRENT_LANG === 'en' ? '#F5C400' : '#fff' ?>"><?= e(t('lang_en')) ?></a>
        </div>
    </div>
</div>
