import { createInterface } from "node:readline/promises";
import { stdin as input, stdout as output } from "node:process";

const requiredEnv = (name) => {
  const value = process.env[name];
  if (!value) {
    throw new Error(`Missing required environment variable: ${name}`);
  }
  return value;
};

async function askForMissingConfig() {
  const values = {
    WP_BASE_URL: process.env.WP_BASE_URL,
    WP_USERNAME: process.env.WP_USERNAME,
    WP_APP_PASSWORD: process.env.WP_APP_PASSWORD,
    WP_ALLOW_INSECURE_TLS: process.env.WP_ALLOW_INSECURE_TLS,
  };

  const missing = Object.entries(values).filter(([, value]) => !value);
  if (missing.length === 0) return values;

  if (!input.isTTY && !process.env.WP_CONFIG_STDIN) {
    const stdinText = await new Promise((resolve, reject) => {
      let text = "";
      input.setEncoding("utf8");
      input.on("data", (chunk) => {
        text += chunk;
      });
      input.on("end", () => resolve(text));
      input.on("error", reject);
    });

    const parsed = JSON.parse(stdinText);
    return {
      ...values,
      ...parsed,
    };
  }

  const rl = createInterface({ input, output });
  try {
    for (const [name] of missing) {
      values[name] = await rl.question(`${name}: `);
    }
  } finally {
    rl.close();
  }

  return values;
}

export async function getConfig() {
  const values = await askForMissingConfig();
  const baseUrl = (values.WP_BASE_URL || requiredEnv("WP_BASE_URL")).replace(/\/+$/, "");
  const username = values.WP_USERNAME || requiredEnv("WP_USERNAME");
  const appPassword = values.WP_APP_PASSWORD || requiredEnv("WP_APP_PASSWORD");
  const allowInsecureTls = values.WP_ALLOW_INSECURE_TLS === "true";

  if (allowInsecureTls) {
    process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";
  }

  return {
    apiBase: `${baseUrl}/wp-json/wp/v2`,
    auth: `Basic ${Buffer.from(`${username}:${appPassword}`).toString("base64")}`,
  };
}

export async function wpRequest(config, path, options = {}) {
  const response = await fetch(`${config.apiBase}${path}`, {
    ...options,
    headers: {
      Authorization: config.auth,
      ...(options.body ? { "Content-Type": "application/json" } : {}),
      ...options.headers,
    },
  });

  const text = await response.text();
  const data = text ? JSON.parse(text) : null;

  if (!response.ok) {
    const message = data?.message || response.statusText;
    throw new Error(`${options.method || "GET"} ${path} failed: ${response.status} ${message}`);
  }

  return { data, response };
}
