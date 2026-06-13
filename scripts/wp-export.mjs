import { mkdir, writeFile } from "node:fs/promises";
import { getConfig, wpRequest } from "./wp-client.mjs";

const outputDir = new URL("../wordpress/pages/", import.meta.url);
const manifestUrl = new URL("../wordpress/manifest.json", import.meta.url);

async function main() {
  const config = await getConfig();
  const pages = [];
  let page = 1;

  while (true) {
    const { data, response } = await wpRequest(
      config,
      `/pages?context=edit&per_page=100&page=${page}&orderby=menu_order&order=asc`
    );

    pages.push(...data);

    const totalPages = Number(response.headers.get("x-wp-totalpages") || "1");
    if (page >= totalPages) break;
    page += 1;
  }

  await mkdir(outputDir, { recursive: true });

  const manifest = pages.map((item) => {
    const slug = item.slug || `page-${item.id}`;
    return {
      id: item.id,
      slug,
      title: item.title?.raw || item.title?.rendered || "",
      link: item.link,
      status: item.status,
      modified: item.modified_gmt,
      file: `pages/${slug}.html`,
    };
  });

  for (const item of pages) {
    const slug = item.slug || `page-${item.id}`;
    const fileUrl = new URL(`${slug}.html`, outputDir);
    await writeFile(fileUrl, item.content?.raw || "", "utf8");
  }

  await writeFile(manifestUrl, `${JSON.stringify(manifest, null, 2)}\n`, "utf8");
  console.log(`Exported ${pages.length} WordPress pages to wordpress/pages`);
}

main().catch((error) => {
  console.error(error.message);
  process.exit(1);
});
