<?php
require_once __DIR__ . '/includes/auth.php';
admin_require_login();

$fields = [
    'company_name_ja', 'company_name_en',
    'company_tagline_ja', 'company_tagline_en',
    'representative_ja', 'representative_en',
    'established',
    'business_activities_ja', 'business_activities_en',
    'address_ja', 'address_en',
    'phone', 'email', 'website',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session, please try again.';
    } else {
        foreach ($fields as $f) {
            set_setting($f, clean($_POST[$f] ?? ''));
        }
        set_flash('success', 'Company profile updated.');
        redirect(admin_url('company-profile.php'));
    }
}

$values = [];
foreach ($fields as $f) { $values[$f] = get_setting($f, ''); }
$flash = get_flash();

$adminTitle  = 'Company Profile';
$adminActive = 'company-profile';
include __DIR__ . '/includes/admin-header.php';
?>

<?php if ($flash): ?>
<div class="admin-alert admin-alert--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="admin-alert admin-alert--error"><?php foreach ($errors as $er): ?><div><?= e($er) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<div class="admin-panel">
    <div class="admin-panel-head"><h2>Company Profile</h2></div>
    <form method="post" class="admin-form">
        <?= csrf_field() ?>

        <div class="aform-section-title">Company Identity</div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Company Name (Japanese)</label>
                <input type="text" name="company_name_ja" class="aform-control" value="<?= e($values['company_name_ja']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Company Name (English)</label>
                <input type="text" name="company_name_en" class="aform-control" value="<?= e($values['company_name_en']) ?>">
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Tagline (Japanese)</label>
                <input type="text" name="company_tagline_ja" class="aform-control" value="<?= e($values['company_tagline_ja']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Tagline (English)</label>
                <input type="text" name="company_tagline_en" class="aform-control" value="<?= e($values['company_tagline_en']) ?>">
            </div>
        </div>

        <div class="aform-section-title">Company Profile Table</div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Representative (Japanese)</label>
                <input type="text" name="representative_ja" class="aform-control" value="<?= e($values['representative_ja']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Representative (English)</label>
                <input type="text" name="representative_en" class="aform-control" value="<?= e($values['representative_en']) ?>">
            </div>
        </div>
        <div class="aform-row">
            <label class="aform-label">Established</label>
            <input type="text" name="established" class="aform-control" value="<?= e($values['established']) ?>">
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Business Activities (Japanese)</label>
                <textarea name="business_activities_ja" class="aform-control"><?= e($values['business_activities_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Business Activities (English)</label>
                <textarea name="business_activities_en" class="aform-control"><?= e($values['business_activities_en']) ?></textarea>
            </div>
        </div>
        <div class="aform-row--split2">
            <div class="aform-row">
                <label class="aform-label">Head Office Address (Japanese)</label>
                <textarea name="address_ja" class="aform-control"><?= e($values['address_ja']) ?></textarea>
            </div>
            <div class="aform-row">
                <label class="aform-label">Head Office Address (English)</label>
                <textarea name="address_en" class="aform-control"><?= e($values['address_en']) ?></textarea>
            </div>
        </div>

        <div class="aform-section-title">Contact Details</div>
        <div class="aform-row--split3">
            <div class="aform-row">
                <label class="aform-label">Telephone</label>
                <input type="text" name="phone" class="aform-control" value="<?= e($values['phone']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Email</label>
                <input type="email" name="email" class="aform-control" value="<?= e($values['email']) ?>">
            </div>
            <div class="aform-row">
                <label class="aform-label">Website</label>
                <input type="text" name="website" class="aform-control" value="<?= e($values['website']) ?>">
            </div>
        </div>

        <div class="aform-actions">
            <button type="submit" class="abtn abtn--primary">Save Company Profile</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
