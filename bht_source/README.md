# CGA Elderly Care Portal

Self-contained PHP/MySQL portal for Annexure II Comprehensive Geriatric Assessment collection.

## Upload

1. Upload the `cga-portal` folder into `public_html`.
2. Create a MySQL database in your hosting panel.
3. Edit `config/app.php` with database credentials and optional `base_url`.
4. Open `/cga-portal/public/install.php` or `/cga-portal/install.php`.
5. Create the first super admin account from the installer.

## Roles

- Super admin: create collector users, see all records, export summary and module-wise CSV.
- Collector: create, edit, view, and print their own CGA records.

Every record gets a unique `CGA-YYYYMMDD-XXXXXXXX` identifier. The same UID is included in all module CSV exports so sections can be joined later.

## PDF

The PDF action opens a print-optimized page and calls the browser print dialog. Choose “Save as PDF” from the browser print screen.
