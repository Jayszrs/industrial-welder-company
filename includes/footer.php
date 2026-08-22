<?php
$footCompanyName = get_setting('company_name_' . $CURRENT_LANG, t('site_name'));
$footTagline     = t('footer_tagline');
$footAddress     = get_setting('address_' . $CURRENT_LANG, '');
$footPhone       = get_setting('phone', '');
$footEmail       = get_setting('email', '');
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= e(base_url('index.php')) ?>" class="brand">
                    <span class="brand-mark">YAMATO<span>.</span></span>
                    <span class="brand-sub">WELDING INDUSTRIES</span>
                </a>
                <p class="footer-tagline"><?= e($footTagline) ?></p>
                <?php render_social_links('footer-social'); ?>
            </div>

            <div>
                <div class="footer-heading"><?= e(t('footer_services')) ?></div>
                <ul class="footer-links">
                    <li><a href="<?= e(base_url('technology.php')) ?>"><?= e(t('nav_technology')) ?></a></li>
                    <li><a href="<?= e(base_url('products.php')) ?>"><?= e(t('nav_products')) ?></a></li>
                    <li><a href="<?= e(base_url('facility.php')) ?>"><?= e(t('nav_facility')) ?></a></li>
                    <li><a href="<?= e(base_url('projects.php')) ?>"><?= e(t('nav_projects')) ?></a></li>
                </ul>
            </div>

            <div>
                <div class="footer-heading"><?= e(t('footer_company')) ?></div>
                <ul class="footer-links">
                    <li><a href="<?= e(base_url('about.php')) ?>"><?= e(t('nav_about')) ?></a></li>
                    <li><a href="<?= e(base_url('news.php')) ?>"><?= e(t('nav_news')) ?></a></li>
                    <li><a href="<?= e(base_url('contact.php')) ?>"><?= e(t('nav_contact')) ?></a></li>
                </ul>
            </div>

            <div>
                <div class="footer-heading"><?= e(t('footer_contact')) ?></div>
                <div class="footer-contact-item"><?= nl2br(e($footAddress)) ?></div>
                <div class="footer-contact-item">TEL: <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $footPhone)) ?>"><?= e($footPhone) ?></a></div>
                <div class="footer-contact-item"><a href="mailto:<?= e($footEmail) ?>"><?= e($footEmail) ?></a></div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>&copy; <?= date('Y') ?> <?= e($footCompanyName) ?>. <?= e(t('footer_rights')) ?></div>
            <div class="footer-bottom-links">
                <a href="<?= e(base_url('privacy.php')) ?>"><?= e(t('footer_privacy')) ?></a>
            </div>
        </div>
    </div>
</footer>

<script src="<?= e(asset_url('assets/js/main.js')) ?>"></script>
</body>
</html>
