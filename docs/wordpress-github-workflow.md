# WordPress and GitHub Workflow

This repository stores the custom site files plus exported WordPress page content for `rivubasu.com`.

## Two Safe Editing Paths

Use WordPress for no-code edits. Use GitHub for Codex/code edits. The GitHub deploy workflow only publishes page files changed in that commit, so a GitHub edit to one page should not overwrite unrelated pages you edited manually in WordPress.

## A. Edit Manually in WordPress

1. Go to `https://rivubasu.com/wp-admin/`.
2. Open **Pages** and choose the page you want to edit.
3. Edit normal WordPress blocks visually: headings, paragraphs, groups, buttons, and links.
4. Avoid editing the small CSS/HTML style blocks unless you specifically want to change the design.
5. Click **Save** or **Update**.

After a WordPress edit, the live site is already updated. To bring those manual edits back into GitHub later, run `npm run wp:export` locally and commit the exported files.

## B. Edit Through GitHub/Codex

1. Open a page file in `wordpress/pages/`.
2. Edit the content.
3. Commit the change to `main`.
4. GitHub Actions runs `Deploy WordPress pages` and publishes only the changed page file back to WordPress.

When Codex edits the site through GitHub, the safest sequence is:

1. Export the latest WordPress pages first with `npm run wp:export`.
2. Make the code or HTML change in Git.
3. Commit and push.
4. Let GitHub Actions deploy the changed page file.

## Sync WordPress Edits Back to GitHub

You can still edit pages in the WordPress visual editor. After making WordPress edits, run this locally to sync the repository copy:

```sh
WP_BASE_URL="https://rivubasu.com" \
WP_USERNAME="your-wordpress-username" \
WP_APP_PASSWORD="your-application-password" \
WP_ALLOW_INSECURE_TLS="true" \
npm run wp:export
```

Then commit and push the exported changes.

## Manual GitHub Deployment

From the GitHub **Actions** tab, you can run `Deploy WordPress pages` manually. Provide specific page files, for example:

```txt
wordpress/pages/calculator.html
wordpress/pages/ewe.html
```

Leaving the manual input blank deploys nothing. This prevents accidental full-site overwrites.

## GitHub Secrets

The deployment workflow requires these repository secrets:

- `WP_BASE_URL`: `https://rivubasu.com`
- `WP_USERNAME`: WordPress application-password username
- `WP_APP_PASSWORD`: WordPress application password
- `WP_ALLOW_INSECURE_TLS`: `true` if the server certificate chain is not accepted by GitHub Actions

Do not commit live passwords or `config/app.php` files.
