<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/language.php';

$pdo = getPDO();

$errors = [];
$old = [
    'inquiry_type'  => clean($_GET['inquiry_type'] ?? 'general'),
    'company_name'  => '',
    'name'          => '',
    'email'         => '',
    'phone'         => '',
    'subject'       => !empty($_GET['product']) ? (($CURRENT_LANG === 'ja' ? '製品について：' : 'About product: ') . clean($_GET['product'])) : '',
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
        if ($old['inquiry_type'] === '') { $errors['inquiry_type'] = true; }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                "INSERT INTO inquiries (inquiry_type, company_name, name, email, phone, subject, message)
                 VALUES (:inquiry_type, :company_name, :name, :email, :phone, :subject, :message)"
            );
            $stmt->execute([
                ':inquiry_type' => $old['inquiry_type'],
                ':company_name' => $old['company_name'],
                ':name'         => $old['name'],
                ':email'        => $old['email'],
                ':phone'        => $old['phone'],
                ':subject'      => $old['subject'],
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

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
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
                        <div class="contact-info-value"><?= e(get_setting('phone')) ?></div>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon">@</div>
                    <div>
                        <div class="contact-info-label"><?= e(t('about_profile_email')) ?></div>
                        <div class="contact-info-value"><?= e(get_setting('email')) ?></div>
                    </div>
                </div>
                <div class="contact-info-item">
                    <div class="contact-info-icon">HQ</div>
                    <div>
                        <div class="contact-info-label"><?= e(t('about_profile_office')) ?></div>
                        <div class="contact-info-value"><?= nl2br(e(get_setting('address_' . $CURRENT_LANG))) ?></div>
                    </div>
                </div>
            </div>

            <div class="reveal">
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
                                'support' => t('contact_inquiry_support'),
                                'other'   => t('contact_inquiry_other'),
                            ];
                            foreach ($inquiryOptions as $val => $label): ?>
                                <option value="<?= e($val) ?>" <?= $old['inquiry_type'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

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

<?php include __DIR__ . '/includes/footer.php'; ?>
