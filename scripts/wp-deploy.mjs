import { readFile } from "node:fs/promises";
import { getConfig, wpRequest } from "./wp-client.mjs";

const manifestUrl = new URL("../wordpress/manifest.json", import.meta.url);
const wordpressDir = new URL("../wordpress/", import.meta.url);

function getSelectedFiles() {
  const raw = process.env.WP_DEPLOY_FILES || "";
  const files = raw
    .split(/[\n,:]+/)
    .map((item) => item.trim())
    .filter(Boolean);

  if (files.length === 0) return null;
  return new Set(files.map((file) => file.replace(/^wordpress\//, "")));
}

async function main() {
  const config = await getConfig();
  const manifest = JSON.parse(await readFile(manifestUrl, "utf8"));
  const selectedFiles = getSelectedFiles();
  const pages = selectedFiles
    ? manifest.filter((page) => selectedFiles.has(page.file))
    : manifest;

  if (pages.length === 0) {
    console.log("No matching WordPress pages to deploy.");
    return;
  }

  for (const page of pages) {
    const content = await readFile(new URL(page.file, wordpressDir), "utf8");
    await wpRequest(config, `/pages/${page.id}`, {
      method: "POST",
      body: JSON.stringify({ content, title: page.title }),
    });
    console.log(`Updated ${page.slug} (${page.id})`);
  }
}

main().catch((error) => {
  console.error(error.message);
  process.exit(1);
});
