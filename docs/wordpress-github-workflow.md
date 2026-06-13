# WordPress and GitHub Workflow

This repository stores the custom site files plus exported WordPress page content for `rivubasu.com`.

## Edit in GitHub

1. Open a page file in `wordpress/pages/`.
2. Edit the content.
3. Commit the change to `main`.
4. GitHub Actions runs `Deploy WordPress pages` and publishes the changed page content back to WordPress.

## Edit in WordPress

You can still edit pages in the WordPress visual editor. After making WordPress edits, run this locally to sync the repository copy:

```sh
WP_BASE_URL="https://rivubasu.com" \
WP_USERNAME="your-wordpress-username" \
WP_APP_PASSWORD="your-application-password" \
WP_ALLOW_INSECURE_TLS="true" \
npm run wp:export
```

Then commit and push the exported changes.

## GitHub Secrets

The deployment workflow requires these repository secrets:

- `WP_BASE_URL`: `https://rivubasu.com`
- `WP_USERNAME`: WordPress application-password username
- `WP_APP_PASSWORD`: WordPress application password
- `WP_ALLOW_INSECURE_TLS`: `true` if the server certificate chain is not accepted by GitHub Actions

Do not commit live passwords or `config/app.php` files.
