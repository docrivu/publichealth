# Direct Site Editing

`rivubasu.com` is no longer intended to publish WordPress page changes through GitHub Actions.

## Current Rule

Make live site changes directly in WordPress, Hostinger, or the live server file manager/FTP. Do not rely on GitHub commits to publish WordPress pages.

## WordPress Pages

1. Go to `https://rivubasu.com/wp-admin/`.
2. Open **Pages**.
3. Edit the page directly in WordPress.
4. Click **Update**.

Those changes are live immediately.

## Server Files

For files under `public_html`, make changes directly through Hostinger file manager, FTP, or the live server upload path.

## Local Repository

This repository can still be used as a reference or backup, but pushing to GitHub should not be treated as a deployment step for the website.

The old GitHub Actions WordPress deployment workflow has been removed.
