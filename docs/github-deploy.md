# Editing rivubasu.com through GitHub

GitHub is the main copy of the site's content. Change a file on `main`, and
GitHub Actions publishes **only that change** to the live site within about a minute.

| What you change in GitHub | Where it goes |
|---|---|
| `wordpress/pages/<slug>.html` | That WordPress page (via the WordPress REST API) |
| `public_html/...` (tools, games, PHP apps) | The same path under `public_html/` on Hostinger (via FTPS) |

What is **not** in GitHub: WordPress itself (core, theme, plugins), the media
library, the database, and server secrets such as `config/app.php`. Keep using
Hostinger's automatic backups for those.

## One-time setup

### 1. WordPress application password
1. In `https://rivubasu.com/wp-admin/` go to **Users → Profile**.
2. Scroll to **Application Passwords**, type the name `GitHub`, and click **Add New Application Password**.
3. Copy the password it shows. It appears only once.

### 2. FTP account
In Hostinger **hPanel → Files → FTP Accounts**, note the FTP host, the username,
and the password. You can create a separate FTP account just for GitHub.

### 3. Add the secrets to GitHub
On GitHub, open the repo's **Settings → Secrets and variables → Actions → New repository secret** and add:

| Secret | Value |
|---|---|
| `WP_BASE_URL` | `https://rivubasu.com` |
| `WP_USERNAME` | your WordPress username |
| `WP_APP_PASSWORD` | the application password from step 1 |
| `FTP_HOST` | FTP host from hPanel (e.g. `ftp.rivubasu.com` or the IP shown) |
| `FTP_USER` | FTP username |
| `FTP_PASSWORD` | FTP password |

Optional ones, only if something doesn't work:

| Secret | When to add it |
|---|---|
| `WP_ALLOW_INSECURE_TLS` = `true` | Page publishing fails with a certificate error |
| `FTP_REMOTE_ROOT` = `.` | Upload fails with "No such file or directory" for `public_html` (your FTP login already opens inside `public_html`) |
| `FTP_SECURE` = `false` | Upload fails with a TLS error and Hostinger only offers plain FTP |
| `FTP_PORT` | Hostinger shows a port other than 21 |

### 4. Bring GitHub up to date first
Open **Actions → Sync from WordPress → Run workflow**. This copies the current
live pages into GitHub, so later deploys start from what's actually live.

## Everyday use

**Edit on GitHub (or have Claude do it):** change a file, commit to `main`
(directly or by merging a pull request), then watch it in the **Actions** tab.

**Edit in wp-admin:** still fine. The nightly **Sync from WordPress** copies the
change into GitHub, or you can run it right away from the Actions tab.

**Safety checks:**
- Only files changed in that commit are published. There is never a full-site overwrite.
- If someone edited a page in wp-admin after the last sync, GitHub **refuses to overwrite it**
  and the run fails with `CONFLICT`. Run **Sync from WordPress**, redo the change on top,
  and commit again. (Or run **Deploy site** manually with *force* ticked.)
- Deleting a file in Git does **not** delete it on the server. The run log lists such files.
- `config/app.php`, `.env`, and `storage/` folders are never uploaded.

**Publish something by hand:** **Actions → Deploy site → Run workflow**, then list the
page or server files (for example `wordpress/pages/games-2.html` or
`public_html/tools/posh-game.html`).

## New WordPress pages
Create the page in wp-admin first (it can be blank), then run **Sync from
WordPress**. It then appears in `wordpress/pages/` and `wordpress/manifest.json`,
and you can edit it from GitHub.
