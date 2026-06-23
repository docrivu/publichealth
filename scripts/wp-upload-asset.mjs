import { readFile } from "node:fs/promises";
import { basename } from "node:path";
import { getConfig } from "./wp-client.mjs";

const filePath = process.argv[2];
const mimeType = process.argv[3] || "application/octet-stream";

if (!filePath) {
  throw new Error("Usage: node scripts/wp-upload-asset.mjs FILE [MIME_TYPE]");
}

const config = await getConfig();
const fileName = basename(filePath);
const body = await readFile(filePath);
const response = await fetch(`${config.apiBase}/media`, {
  method: "POST",
  headers: {
    Authorization: config.auth,
    "Content-Disposition": `attachment; filename="${fileName}"`,
    "Content-Type": mimeType,
  },
  body,
});

const text = await response.text();
const result = text ? JSON.parse(text) : null;

if (!response.ok) {
  throw new Error(
    `Upload failed: ${response.status} ${result?.message || response.statusText}`
  );
}

console.log(
  JSON.stringify(
    {
      id: result.id,
      sourceUrl: result.source_url,
      mimeType: result.mime_type,
    },
    null,
    2
  )
);
