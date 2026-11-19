import { readFile } from "node:fs/promises";
import { existsSync } from "node:fs";
import { getConfig, wpRequest } from "./wp-client.mjs";

const manifestUrl = new URL("../wordpress/manifest.json", import.meta.url);
const wordpressDir = new URL("../wordpress/", import.meta.url);

/*
 * Environment:
 *   WP_DEPLOY_FILES   pages to publish, e.g. "wordpress/pages/calculator.html" (comma/newline separated).
 *                     Empty = publish nothing (never a blind full-site overwrite).
 *   WP_PREVIOUS_DIR   folder holding the previous Git version of each page (same relative paths).
 *                     When set, a page is only published if the live WordPress content still equals
 *                     that previous version — i.e. nobody edited it in wp-admin since the last sync.
 *   WP_FORCE          "true" to skip that safety check.
 */

function getSelectedFiles() {
  const raw = process.env.WP_DEPLOY_FILES || "";
  const files = raw
    .split(/[\n,]+/)
    .map((item) => item.trim())
    .filter(Boolean);

  if (files.length === 0) return null;
  return new Set(files.map((file) => file.replace(/^wordpress\//, "")));
}

const normalise = (text) => (text || "").replace(/\r\n/g, "\n").trim();

async function main() {
  const selectedFiles = getSelectedFiles();
  if (!selectedFiles) {
    console.log("No WordPress pages selected; nothing to deploy.");
    return;
  }

  const config = await getConfig();
  const manifest = JSON.parse(await readFile(manifestUrl, "utf8"));
  const pages = manifest.filter((page) => selectedFiles.has(page.file));

  const unknown = [...selectedFiles].filter((file) => !manifest.some((page) => page.file === file));
  for (const file of unknown) {
    console.warn(`Skipping ${file}: not in wordpress/manifest.json (create the page in WordPress first, then sync).`);
  }

  const previousDir = process.env.WP_PREVIOUS_DIR;
  const force = process.env.WP_FORCE === "true";
  const conflicts = [];

  for (const page of pages) {
    const content = await readFile(new URL(page.file, wordpressDir), "utf8");

    if (previousDir && !force) {
      const previousPath = `${previousDir.replace(/\/+$/, "")}/wordpress/${page.file}`;
      if (existsSync(previousPath)) {
        const previous = await readFile(previousPath, "utf8");
        const { data: live } = await wpRequest(config, `/pages/${page.id}?context=edit`);
        if (normalise(live?.content?.raw) !== normalise(previous)) {
          conflicts.push(page);
          console.error(
            `CONFLICT ${page.slug}: the live page was edited in WordPress after the last sync. ` +
              `Not overwriting it. Run "Sync from WordPress", redo your change on top, or re-run with force.`
          );
          continue;
        }
      }
    }

    await wpRequest(config, `/pages/${page.id}`, {
      method: "POST",
      body: JSON.stringify({ content, title: page.title }),
    });
    console.log(`Updated ${page.slug} (${page.id})`);
  }

  if (conflicts.length) {
    throw new Error(`${conflicts.length} page(s) not deployed because of conflicts: ${conflicts.map((p) => p.slug).join(", ")}`);
  }
}

main().catch((error) => {
  console.error(error.message);
  process.exit(1);
});
