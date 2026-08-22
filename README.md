# Yamato Welding Industries — Company Profile Website

A full company profile website for an industrial welding / metal
fabrication / engineering solutions company, in the style of a premium
Japanese industrial manufacturer. Built with plain PHP + MySQL — no
framework, no build step, no `npm install`. Everything runs directly
on XAMPP.

> **Design reference:** the layout language (large industrial headings,
> generous whitespace, dark/light section rhythm, numbered technical
> labels) takes inspiration from Japanese industrial-manufacturer
> corporate sites such as crest-grp.com. No code, markup, images, or
> copy were copied — this is an original implementation with its own
> visual identity (Black / Graphite / Gray / Off-White palette with an
> industrial-yellow accent).

---

## 1. Tech Stack

- PHP 8+ (native, no framework)
- MySQL / MariaDB (PDO, prepared statements everywhere)
- HTML5 / CSS3 (hand-written, CSS variables, no Tailwind/Bootstrap)
- Vanilla JavaScript (IntersectionObserver for scroll reveals, no jQuery)
- PHP Sessions (language preference, admin auth, CSRF tokens)

No Laravel, no Node, no build pipeline. Copy the folder into
`htdocs`, import one SQL file, done.

---

## 2. Folder Structure

```
industrial-welder-company/
├── index.php                 Homepage
├── about.php                 Company profile page
├── technology.php            Services + welding technology
├── products.php               Product catalog (with category filter)
├── product-detail.php        Product detail + specs
├── facility.php               Facility / equipment page
├── projects.php               Case studies list
├── project-detail.php        Case study detail
├── news.php / news-detail.php News list / article
├── contact.php                Contact form (CSRF + honeypot + validation)
├── privacy.php                 Privacy policy
│
├── admin/                     Admin panel (session-protected)
│   ├── login.php / logout.php
│   ├── index.php               Dashboard
│   ├── services.php / service-form.php
│   ├── technologies.php / technology-form.php
│   ├── products.php / product-form.php
│   ├── facilities.php / facility-form.php
│   ├── projects.php / project-form.php
│   ├── news.php / news-form.php
│   ├── industries.php          Industries served (list + inline form)
│   ├── stats.php               Homepage "strength" numbers
│   ├── inquiries.php           Contact form submissions
│   ├── homepage.php            Hero / About / Strength / CTA text blocks
│   ├── company-profile.php     Company profile table fields
│   ├── settings.php            SEO meta + admin password change
│   └── includes/                auth.php, admin-header.php, admin-sidebar nav, admin-footer.php
│
├── config/
│   └── database.php            PDO connection (XAMPP defaults)
│
├── includes/
│   ├── header.php / footer.php
│   ├── functions.php           CSRF, uploads, sanitization, helpers
│   ├── language.php             Session-based JA/EN switching + tf()/t() helpers
│   └── lang/ja.php, lang/en.php Static UI translation strings
│
├── assets/
│   ├── css/style.css           Public site design system
│   ├── css/admin.css           Admin panel styles
│   ├── js/main.js              Nav scroll, reveal animation, counters, mobile menu
│   ├── js/admin.js             Delete confirmation
│   └── images/                 placeholder.svg + original hero/about SVG art
│
├── uploads/                    Admin-uploaded images (products, services,
│                                 technologies, facilities, projects, news,
│                                 homepage) — each folder has a .htaccess
│                                 that blocks script execution
│
├── database.sql                Full schema + demo content (utf8mb4)
└── README.md
```

---

## 3. Installation (XAMPP)

1. Copy the whole `industrial-welder-company` folder into:
   ```
   C:/xampp/htdocs/
   ```
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
4. Click **Import**, choose `database.sql`, and run it.
   This creates the `industrial_company` database (utf8mb4) with the
   full schema and demo content already filled in.
5. Visit:
   ```
   http://localhost/industrial-welder-company/
   ```
6. Admin panel:
   ```
   http://localhost/industrial-welder-company/admin/
   ```

The site auto-detects its own folder name via `base_url()` in
`includes/functions.php`, so it works whether you keep the folder
name as-is or rename it.

---

## 4. Database Configuration

Edit `config/database.php` if your MySQL credentials differ from the
XAMPP defaults:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'industrial_company');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## 5. Admin Login

```
URL:      /admin/login.php
Username: admin
Password: admin123
```

**Change this password immediately** after import, from
**Admin → Site Settings → Change Admin Password**. The password is
stored as a genuine bcrypt hash (`password_hash()` / `password_verify()`).

---

## 6. Bilingual System (日本語 / English)

- Default language: **Japanese**.
- Switch via the `JP | EN` links in the navbar, which set
  `?lang=ja` / `?lang=en`. The choice is stored in
  `$_SESSION['site_lang']` (see `includes/language.php`), so it
  persists across pages without needing the parameter every time.
- Static UI text (buttons, labels, nav, form fields, error/success
  messages) lives in `includes/lang/ja.php` and `includes/lang/en.php`
  as plain PHP arrays, accessed with `t('key')`.
- Database content (products, services, projects, news, etc.) is
  stored in **paired columns** — `title_ja` / `title_en`,
  `description_ja` / `description_en`, and so on — never machine
  translated. The helper `tf($row, 'title')` picks the column for the
  active language and **falls back to the other language** if the
  active one is empty, so a page never renders blank text.
- Every admin content form has **separate Japanese and English input
  fields** side by side — nothing is auto-translated. This is
  intentional: the client controls both language versions directly.

---

## 7. Editing Company Data

Everything demo/sample is clearly editable from the admin panel —
none of it is hard-coded in the templates:

| What                                   | Where in Admin              |
|-----------------------------------------|------------------------------|
| Company name, tagline, address, contact | Company Profile              |
| Hero headline, About text, CTA band     | Homepage                     |
| Services (6 cards)                      | Services                     |
| Welding technology list                 | Welding Technology           |
| Products & machines + specs             | Products                     |
| Facility / equipment                    | Facilities                   |
| Case studies                            | Projects                     |
| News articles                           | News                         |
| Industries served                       | Industries                   |
| "Our Strength" numbers (25+ years etc.) | Strength / Stats             |
| Contact form submissions                | Contact Messages             |
| SEO meta description, admin password    | Site Settings                |

All demo content (company name, address, stats, products, etc.) is
**sample data** meant to be replaced — it is not a real company.

---

## 8. Security Notes

- All SQL uses **PDO prepared statements** — no string-concatenated
  queries anywhere.
- All output is escaped with `htmlspecialchars()` via the `e()` helper.
- Every state-changing form (admin create/update/delete, contact form)
  is protected by a **CSRF token** (`csrf_field()` / `csrf_verify()`).
- Admin passwords use `password_hash()` / `password_verify()` (bcrypt).
- Admin pages require an active session (`admin_require_login()`).
- Image uploads are validated by real MIME sniffing (`finfo`) and
  `getimagesize()`, renamed to random filenames, and capped at 5MB.
  PHP/script execution is disabled inside every `uploads/` subfolder
  via `.htaccess`.
- The contact form has a hidden **honeypot** field
  (`website_url`) — bots that fill it in are silently ignored.
- This is a portfolio/demo build. Before real production use: change
  the demo admin password, set a real `DB_PASS`, run the site over
  HTTPS, and review the checklist below.

---

## 9. Production Checklist

- [ ] Change the admin password (Site Settings)
- [ ] Replace all demo company info (Company Profile)
- [ ] Replace demo products, services, technologies, facilities,
      projects, and news with real content and real photos
- [ ] Set a strong `DB_PASS` in `config/database.php` and a
      dedicated (non-root) MySQL user in production
- [ ] Serve the site over HTTPS
- [ ] Set `display_errors = Off` in `php.ini` for production
- [ ] Double-check `uploads/*/.htaccess` files are present and Apache
      has `AllowOverride All` so they take effect
- [ ] Set a real `sample_data_notice` / remove the "demo data" note
      once real content is in place
- [ ] Back up the database regularly (`mysqldump`)

---

## 10. Notes

- All images shipped in `/uploads/*` and `/assets/images/` are
  **original, generated SVG illustrations** created for this project
  (abstract industrial/welding motifs) — not stock photography — so
  the demo has no copyright dependency. Replace them with real product
  and facility photography via the admin panel's image upload.
- The homepage flow (dark hero → light about → white services → dark
  technology → white products → light-gray facility → white
  industries → dark strength/stats → white projects → light news →
  yellow CTA → black footer) intentionally alternates section
  backgrounds for visual rhythm, per the brief.
