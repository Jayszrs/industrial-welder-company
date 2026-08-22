<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pageTitle       = t('privacy_page_title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = '';
$pageHeaderImage = page_header_image_url(null, '', 2);

include __DIR__ . '/includes/header.php';

$companyName = get_setting('company_name_' . $CURRENT_LANG);
?>

<section class="page-header" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
    <div class="container">
        <div class="eyebrow">LEGAL</div>
        <h1 class="page-title"><?= e(t('privacy_page_title')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <span><?= e(t('privacy_page_title')) ?></span>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="article-body">
            <?php if ($CURRENT_LANG === 'ja'): ?>
                <p><?= e($companyName) ?>（以下「当社」といいます）は、お客様の個人情報の重要性を認識し、以下のプライバシーポリシーに基づき、適切に取り扱います。</p>
                <p><strong>1. 取得する情報</strong><br>お問い合わせフォームを通じて、会社名、氏名、メールアドレス、電話番号、お問い合わせ内容をお預かりします。</p>
                <p><strong>2. 利用目的</strong><br>お預かりした情報は、お問い合わせへの対応、資料送付、製品・サービスに関するご案内の目的にのみ利用いたします。</p>
                <p><strong>3. 第三者提供</strong><br>法令に基づく場合を除き、ご本人の同意なく個人情報を第三者に提供することはありません。</p>
                <p><strong>4. お問い合わせ</strong><br>個人情報の取り扱いに関するお問い合わせは、お問い合わせフォームよりご連絡ください。</p>
                <p style="color:var(--c-gray); font-size:13px;">※ 本ページはサンプルテキストです。実際の運用にあたっては、内容を貴社の実情に合わせて修正してください。</p>
            <?php else: ?>
                <p><?= e($companyName) ?> ("we", "our", "the Company") recognizes the importance of protecting your personal information and handles it appropriately in accordance with this Privacy Policy.</p>
                <p><strong>1. Information We Collect</strong><br>Through our contact form, we collect your company name, name, email address, phone number, and inquiry details.</p>
                <p><strong>2. Purpose of Use</strong><br>The information collected is used solely to respond to your inquiry, provide requested materials, and share information about our products and services.</p>
                <p><strong>3. Disclosure to Third Parties</strong><br>We do not disclose your personal information to third parties without your consent, except where required by law.</p>
                <p><strong>4. Contact</strong><br>For questions regarding how we handle personal information, please contact us via our contact form.</p>
                <p style="color:var(--c-gray); font-size:13px;">* This page contains sample text. Please review and adapt it to your organization's actual practices before publishing.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
