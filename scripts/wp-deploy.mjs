import { readFile } from "node:fs/promises";
import { getConfig, wpRequest } from "./wp-client.mjs";

const manifestUrl = new URL("../wordpress/manifest.json", import.meta.url);
const wordpressDir = new URL("../wordpress/", import.meta.url);

async function main() {
  const config = await getConfig();
  const manifest = JSON.parse(await readFile(manifestUrl, "utf8"));

  for (const page of manifest) {
    const content = await readFile(new URL(page.file, wordpressDir), "utf8");
    await wpRequest(config, `/pages/${page.id}`, {
      method: "POST",
      body: JSON.stringify({ content }),
    });
    console.log(`Updated ${page.slug} (${page.id})`);
  }
}

main().catch((error) => {
  console.error(error.message);
  process.exit(1);
});
