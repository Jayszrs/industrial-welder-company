<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();
ensure_inquiry_order_fields($pdo);
$contactHeaderSource = $pdo->query("SELECT image FROM homepage_content WHERE section_key = 'cta'")->fetchColumn();
$pageHeaderImage = page_header_image_url($contactHeaderSource ?: null, 'homepage', 1);

$errors = [];
$allowedInquiryTypes = ['general', 'product', 'quote', 'order', 'support', 'other'];
$requestedInquiryType = clean($_GET['inquiry_type'] ?? 'general');
if (!in_array($requestedInquiryType, $allowedInquiryTypes, true)) {
    $requestedInquiryType = 'general';
}
$old = [
    'inquiry_type'  => $requestedInquiryType,
    'company_name'  => '',
    'name'          => '',
    'email'         => '',
    'phone'         => '',
    'subject'       => !empty($_GET['product']) ? (($CURRENT_LANG === 'ja' ? '製品について：' : 'About product: ') . clean($_GET['product'])) : '',
    'product_interest' => clean($_GET['product'] ?? ''),
    'quantity'      => '',
    'budget_range'  => '',
    'desired_timeline' => '',
    'message'       => '',
];
$successMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['inquiry_type'] = clean($_POST['inquiry_type'] ?? '');
    $old['company_name'] = clean($_POST['company_name'] ?? '');
    $old['name']         = clean($_POST['name'] ?? '');
    $old['email']        = clean($_POST['email'] ?? '');
    $old['phone']        = clean($_POST['phone'] ?? '');
    $old['subject']      = clean($_POST['subject'] ?? '');
    $old['product_interest'] = clean($_POST['product_interest'] ?? '');
    $old['quantity']     = clean($_POST['quantity'] ?? '');
    $old['budget_range'] = clean($_POST['budget_range'] ?? '');
    $old['desired_timeline'] = clean($_POST['desired_timeline'] ?? '');
    $old['message']      = clean($_POST['message'] ?? '');

    if (!csrf_verify()) {
        $errors[] = t('contact_error');
    } elseif (honeypot_triggered()) {
        // Silently pretend success to the bot, do not insert anything.
        $successMessage = t('contact_success');
        $old = array_fill_keys(array_keys($old), '');
    } else {
        if ($old['name'] === '') { $errors['name'] = true; }
        if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) { $errors['email'] = true; }
        if ($old['message'] === '') { $errors['message'] = true; }
        if (!in_array($old['inquiry_type'], $allowedInquiryTypes, true)) { $errors['inquiry_type'] = true; }
        if ($old['inquiry_type'] === 'order' && $old['product_interest'] === '') { $errors['product_interest'] = true; }
        if ($old['inquiry_type'] === 'order' && ($old['quantity'] === '' || !ctype_digit($old['quantity']) || (int) $old['quantity'] < 1)) { $errors['quantity'] = true; }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "INSERT INTO inquiries (inquiry_type, company_name, name, email, phone, subject, product_interest, quantity, budget_range, desired_timeline, message)
                 VALUES (:inquiry_type, :company_name, :name, :email, :phone, :subject, :product_interest, :quantity, :budget_range, :desired_timeline, :message)"
            );
            $stmt->execute([
                ':inquiry_type' => $old['inquiry_type'],
                ':company_name' => $old['company_name'],
                ':name'         => $old['name'],
                ':email'        => $old['email'],
                ':phone'        => $old['phone'],
                ':subject'      => $old['subject'],
                ':product_interest' => $old['product_interest'],
                ':quantity'     => $old['quantity'],
                ':budget_range' => $old['budget_range'],
                ':desired_timeline' => $old['desired_timeline'],
                ':message'      => $old['message'],
            ]);

            $successMessage = t('contact_success');
            $old = array_fill_keys(array_keys($old), '');
            $old['inquiry_type'] = 'general';
        }
    }
}

$pageTitle       = t('contact_title');
$pageDescription = get_setting('meta_description_' . $CURRENT_LANG, '');
$activePage      = 'contact';
$locationAddress = get_setting('address_' . $CURRENT_LANG, get_setting('address_en'));
$mapsQuery       = rawurlencode(str_replace(["\r", "\n"], ' ', $locationAddress));
$mapsUrl         = 'https://www.google.com/maps/search/?api=1&query=' . $mapsQuery;
$mapsEmbedUrl    = 'https://www.google.com/maps?q=' . $mapsQuery . '&output=embed';

include __DIR__ . '/includes/header.php';
?>

<section class="page-header" style="--page-header-bg:url('<?= e($pageHeaderImage) ?>');">
    <div class="container">
        <div class="eyebrow"><?= e(t('contact_eyebrow')) ?></div>
        <h1 class="page-title"><?= e(t('contact_title')) ?></h1>
        <div class="breadcrumb">
            <a href="<?= e(base_url('index.php')) ?>"><?= e(t('breadcrumb_home')) ?></a>
            <span class="sep">/</span>
            <span><?= e(t('nav_contact')) ?></span>
        </div>
    </div>
</section>

<section class="section section--white">
    <div class="container">
        <div class="contact-grid">
            <div class="reveal">
                <div class="eyebrow"><?= e(t('contact_info_title')) ?></div>
                <h2 class="section-title" style="margin-bottom:32px;"><?= e(t('contact_subtitle')) ?></h2>

                <div class="contact-info-item">
                    <div class="contact-info-icon">TEL</div>
                    <div>
                        <div class="contact-info-label"><?= e(t('about_profile_tel')) ?></div>
                        <div class="contact-info-value"><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', get_setting('phone'))) ?>"><?= e(get_setting('phone')) ?></a></div>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon">@</div>
                    <div>
                        <div class="contact-info-label"><?= e(t('about_profile_email')) ?></div>
                        <div class="contact-info-value"><a href="mailto:<?= e(get_setting('email')) ?>"><?= e(get_setting('email')) ?></a></div>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon">HQ</div>
                    <div>
                        <div class="contact-info-label"><?= e(t('about_profile_office')) ?></div>
                        <div class="contact-info-value"><?= nl2br(e(get_setting('address_' . $CURRENT_LANG))) ?></div>
                    </div>
                </div>
                <a href="<?= e(base_url('contact.php?inquiry_type=order#contact-form')) ?>" class="btn btn--outline-dark contact-order-shortcut">
                    <?= $CURRENT_LANG === 'ja' ? '注文について相談する' : 'Start an Order Request' ?> <span class="btn-arrow">&#8594;</span>
                </a>
            </div>

            <div class="reveal contact-form-card">
                <?php if ($successMessage): ?>
                    <div class="alert alert--success"><?= e($successMessage) ?></div>
                <?php elseif (!empty($errors)): ?>
                    <div class="alert alert--error"><?= e(t('contact_error')) ?></div>
                <?php endif; ?>

                <form method="post" action="<?= e(base_url('contact.php')) ?>#contact-form" id="contact-form" novalidate>
                    <?= csrf_field() ?>
                    <input type="text" name="website_url" class="form-hp" tabindex="-1" autocomplete="off">

                    <div class="form-row">
                        <label class="form-label"><?= e(t('contact_form_inquiry_type')) ?><span class="req">*</span></label>
                        <select name="inquiry_type" class="form-control" required>
                            <?php
                            $inquiryOptions = [
                                'general' => t('contact_inquiry_general'),
                                'product' => t('contact_inquiry_product'),
                                'quote'   => t('contact_inquiry_quote'),
                                'order'   => $CURRENT_LANG === 'ja' ? '注文のご相談' : 'Order Request',
                                'support' => t('contact_inquiry_support'),
                                'other'   => t('contact_inquiry_other'),
                            ];
                            foreach ($inquiryOptions as $val => $label): ?>
                                <option value="<?= e($val) ?>" <?= $old['inquiry_type'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php $showOrderFields = in_array($old['inquiry_type'], ['product', 'quote', 'order'], true); ?>
                    <fieldset class="order-fields<?= $showOrderFields ? ' is-visible' : '' ?>" data-order-fields<?= $showOrderFields ? '' : ' hidden' ?>>
                        <legend><?= $CURRENT_LANG === 'ja' ? '製品・注文の詳細' : 'Product &amp; order details' ?></legend>
                        <p class="order-fields-note"><?= $CURRENT_LANG === 'ja' ? '見積もりや注文に必要な情報をご入力ください。未定の項目は空欄でも構いません。' : 'Add what you already know. For quote and product questions, these details are optional.' ?></p>
                        <div class="form-row">
                            <label class="form-label"><?= $CURRENT_LANG === 'ja' ? 'ご希望の製品・サービス' : 'Product or service needed' ?><span class="order-required req">*</span></label>
                            <input type="text" name="product_interest" class="form-control" value="<?= e($old['product_interest']) ?>" placeholder="<?= $CURRENT_LANG === 'ja' ? '例：ロボット溶接セル' : 'e.g. Robotic welding cell' ?>" data-order-required>
                            <?php if (!empty($errors['product_interest'])): ?><div class="field-error"><?= e(t('contact_form_required')) ?></div><?php endif; ?>
                        </div>
                        <div class="form-row form-row--split">
                            <div>
                                <label class="form-label"><?= $CURRENT_LANG === 'ja' ? '数量' : 'Estimated quantity' ?><span class="order-required req">*</span></label>
                                <input type="number" min="1" inputmode="numeric" name="quantity" class="form-control" value="<?= e($old['quantity']) ?>" placeholder="1" data-order-required>
                                <?php if (!empty($errors['quantity'])): ?><div class="field-error"><?= e(t('contact_form_required')) ?></div><?php endif; ?>
                            </div>
                            <div>
                                <label class="form-label"><?= $CURRENT_LANG === 'ja' ? 'ご予算' : 'Budget range' ?></label>
                                <select name="budget_range" class="form-control">
                                    <?php
                                    $budgetOptions = [
                                        '' => $CURRENT_LANG === 'ja' ? '未定' : 'Not decided yet',
                                        'under-10k' => 'Under USD 10,000',
                                        '10k-50k' => 'USD 10,000 - 50,000',
                                        '50k-100k' => 'USD 50,000 - 100,000',
                                        'over-100k' => 'Above USD 100,000',
                                    ];
                                    foreach ($budgetOptions as $value => $label): ?>
                                        <option value="<?= e($value) ?>" <?= $old['budget_range'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <label class="form-label"><?= $CURRENT_LANG === 'ja' ? '希望納期・スケジュール' : 'Desired delivery or timeline' ?></label>
                            <input type="text" name="desired_timeline" class="form-control" value="<?= e($old['desired_timeline']) ?>" placeholder="<?= $CURRENT_LANG === 'ja' ? '例：2026年12月まで' : 'e.g. Before December 2026' ?>">
                        </div>
                    </fieldset>

                    <div class="form-row form-row--split">
                        <div>
                            <label class="form-label"><?= e(t('contact_form_company')) ?></label>
                            <input type="text" name="company_name" class="form-control" value="<?= e($old['company_name']) ?>">
                        </div>
                        <div>
                            <label class="form-label"><?= e(t('contact_form_name')) ?><span class="req">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= e($old['name']) ?>" required>
                            <?php if (!empty($errors['name'])): ?><div class="field-error"><?= e(t('contact_form_required')) ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-row form-row--split">
                        <div>
                            <label class="form-label"><?= e(t('contact_form_email')) ?><span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required>
                            <?php if (!empty($errors['email'])): ?><div class="field-error"><?= e(t('contact_form_required')) ?></div><?php endif; ?>
                        </div>
                        <div>
                            <label class="form-label"><?= e(t('contact_form_phone')) ?></label>
                            <input type="text" name="phone" class="form-control" value="<?= e($old['phone']) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label"><?= e(t('contact_form_subject')) ?></label>
                        <input type="text" name="subject" class="form-control" value="<?= e($old['subject']) ?>">
                    </div>

                    <div class="form-row">
                        <label class="form-label"><?= e(t('contact_form_message')) ?><span class="req">*</span></label>
                        <textarea name="message" class="form-control" required><?= e($old['message']) ?></textarea>
                        <?php if (!empty($errors['message'])): ?><div class="field-error"><?= e(t('contact_form_required')) ?></div><?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn--primary btn--block">
                        <?= e(t('contact_form_submit')) ?> <span class="btn-arrow">&#8594;</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="section section--off location-section" id="location">
    <div class="container location-grid">
        <div class="location-copy reveal">
            <div class="eyebrow"><?= $CURRENT_LANG === 'ja' ? 'アクセス' : 'Location' ?></div>
            <h2 class="section-title"><?= $CURRENT_LANG === 'ja' ? '事業所へのアクセス' : 'Find Our Facility' ?></h2>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
