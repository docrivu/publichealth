# Direct Site Editing

GitHub now publishes changes to `rivubasu.com`. See [github-deploy.md](github-deploy.md).

Editing directly in WordPress (`https://rivubasu.com/wp-admin/`) or Hostinger
still works:

- **WordPress page edits** are copied back into GitHub every night by the
  *Sync from WordPress* workflow, or right away if you run it from the Actions tab.
- **Server files changed in Hostinger's file manager** are not synced back
  automatically. Make the same change in `public_html/` in GitHub, or the next
  GitHub deploy of that file will overwrite it.
