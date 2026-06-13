# Community Diagnosis Survey App

This is a lightweight PHP + MySQL web app for collecting data from the provided Community Diagnosis and NCD survey schedules.

## What it includes

- Super admin login
- Collector logins created by the super admin
- SQL-backed survey storage
- Combined survey entry form covering:
  - household identification
  - household characteristics
  - household members
  - live births and deaths
  - immunization
  - morbidity and disability
  - eligible couples
  - NCD risk-factor schedule
  - cancer screening for age 50+

## Hostinger deployment using `public_html`

1. Create a MySQL database in Hostinger hPanel.
2. Open `config/app.php`.
3. Fill in your Hostinger MySQL host, database name, username, and password.
4. Upload the entire contents of this folder directly into `public_html`.
5. Keep the included `.htaccess` file in place so the app blocks direct web access to `config/`, `src/`, `sql/`, and `storage/`.
6. Open `https://your-domain/install.php`.
7. Run the installer and create the first super admin account. The installer is prefilled with:
   - username: `rivu`
   - password: set a strong password before deploying
8. Open `https://your-domain/` for the portal home or `https://your-domain/frontpage.html` for the standalone HTML front page.
9. Log in as super admin and create collector accounts.

## Important note

To keep deployment simple for shared hosting, the detailed survey sections are stored as JSON inside the `surveys.payload_json` column, while user accounts and survey metadata are stored in standard SQL columns.

## Web root entry files

These top-level files are included so the package works directly from `public_html`:

- `index.php`
- `login.php`
- `logout.php`
- `install.php`
- `admin_collectors.php`
- `survey_create.php`
- `survey_view.php`
- `frontpage.html`

They load the actual app pages from the `public/` folder while keeping the package structure organized.

## Recent questionnaire changes

- Module 1 now captures village-linked geo coordinates and up to 4 field photos
- collectors can open an individual survey in a print-friendly page and save it as PDF from the phone/browser
- super admin full export is now CSV instead of JSON
- key household characteristic responses now use choice-based inputs for field use
- relation to mother is only active for under-5 members in joint families
- antenatal TT and child immunization fields now use Taken/Given style responses
- acute morbidity uses Yes/No and numeric day input where appropriate
- super admin can view full responses and download the full dataset as JSON

## Recommended next improvements

- add edit/update survey support
- add CSV/Excel export for flat summary tables
- add validation rules for coded responses
- split the long form into step-by-step pages
- add offline sync if you later move to a mobile-first setup
