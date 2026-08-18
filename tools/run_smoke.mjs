#!/usr/bin/env node
/* Run tests/smoke.html in headless Chrome and exit non-zero if anything failed.

   Zero dependencies, on purpose. This project ships no build step and no npm
   footprint, and a CI harness is a poor reason to introduce one — so instead of
   Playwright or Puppeteer this drives Chrome directly over the DevTools
   Protocol using Node's own fetch and WebSocket (both built in since Node 22).

   Usage:  node tools/run_smoke.mjs [--url http://localhost:8517] [--keep-open]
   Chrome: taken from $CHROME_BIN, else the first of the usual paths that exists.
*/
import { spawn } from "node:child_process";
import { mkdtemp, rm } from "node:fs/promises";
import { existsSync } from "node:fs";
import { tmpdir } from "node:os";
import { join } from "node:path";

const args = process.argv.slice(2);
const arg = (name, fallback) => {
  const i = args.indexOf(name);
  return i >= 0 && args[i + 1] ? args[i + 1] : fallback;
};
const BASE = arg("--url", "http://localhost:8517").replace(/\/$/, "");
const TIMEOUT_MS = Number(arg("--timeout", "180000"));

const CANDIDATES = [
  process.env.CHROME_BIN,
  "/usr/bin/google-chrome-stable", "/usr/bin/google-chrome", "/usr/bin/chromium-browser",
  "/usr/bin/chromium", "/snap/bin/chromium",
  "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome",
  "/Applications/Chromium.app/Contents/MacOS/Chromium",
].filter(Boolean);

const chrome = CANDIDATES.find(p => existsSync(p));
if (!chrome) {
  console.error("No Chrome found. Set CHROME_BIN, or install Chrome/Chromium.");
  console.error("Looked in:\n  " + CANDIDATES.join("\n  "));
  process.exit(127);
}

const profile = await mkdtemp(join(tmpdir(), "smoke-profile-"));
const proc = spawn(chrome, [
  "--headless=new", "--disable-gpu", "--no-sandbox", "--no-first-run",
  "--disable-dev-shm-usage",           /* small /dev/shm on CI runners crashes tabs */
  "--remote-debugging-port=0",         /* let the OS pick, so parallel runs don't collide */
  `--user-data-dir=${profile}`,
  "about:blank",
], { stdio: ["ignore", "pipe", "pipe"] });

/* Chrome prints the chosen port on stderr as "DevTools listening on ws://..." */
const wsBrowser = await new Promise((resolve, reject) => {
  const t = setTimeout(() => reject(new Error("Chrome did not report a DevTools endpoint in 30s")), 30000);
  let buf = "";
  proc.stderr.on("data", d => {
    buf += d;
    const m = buf.match(/DevTools listening on (ws:\/\/\S+)/);
    if (m) { clearTimeout(t); resolve(m[1]); }
  });
  proc.on("exit", c => { clearTimeout(t); reject(new Error(`Chrome exited early (code ${c})`)); });
});

const origin = new URL(wsBrowser).host;

/* Minimal CDP client: send(method, params) -> result, over one socket. */
function connect(url){
  return new Promise((resolve, reject) => {
    const ws = new WebSocket(url);
    const pending = new Map();
    let id = 0;
    ws.addEventListener("message", ev => {
      const msg = JSON.parse(ev.data);
      const p = pending.get(msg.id);
      if (!p) return;
      pending.delete(msg.id);
      msg.error ? p.reject(new Error(msg.error.message)) : p.resolve(msg.result);
    });
    ws.addEventListener("error", () => reject(new Error(`CDP socket failed: ${url}`)));
    ws.addEventListener("open", () => resolve({
      send: (method, params = {}) => new Promise((res, rej) => {
        const n = ++id;
        pending.set(n, { resolve: res, reject: rej });
        ws.send(JSON.stringify({ id: n, method, params }));
      }),
      close: () => ws.close(),
    }));
  });
}

let failed = true;
try {
  /* open a tab on the smoke page and attach to it */
  const target = await (await fetch(
    `http://${origin}/json/new?${encodeURIComponent(`${BASE}/tests/smoke.html?auto=1`)}`,
    { method: "PUT" })).json();
  const page = await connect(target.webSocketDebuggerUrl);
  await page.send("Runtime.enable");

  /* surface page console errors, which otherwise vanish in CI */
  const consoleErrors = [];
  await page.send("Log.enable").catch(() => {});

  const started = Date.now();
  let result = null;
  while (Date.now() - started < TIMEOUT_MS) {
    const r = await page.send("Runtime.evaluate", {
      expression: "JSON.stringify(window.__SMOKE_RESULT || null)",
      returnByValue: true,
    });
    result = JSON.parse(r.result.value ?? "null");
    if (result) break;
    await new Promise(s => setTimeout(s, 500));
  }

  if (!result) throw new Error(`Smoke test did not finish within ${TIMEOUT_MS / 1000}s`);

  for (const p of result.pages) {
    console.log(`${p.ok ? "  pass" : "  FAIL"}  ${p.page}`);
    for (const f of p.failed) console.log(`          ✗ ${f}`);
    for (const n of p.notes)  console.log(`          ! ${n}`);
  }
  console.log(result.fail
    ? `\n${result.fail} page(s) failed, ${result.pass} passed.`
    : `\nAll ${result.pass} pages passed.`);
  for (const e of consoleErrors) console.log(`  console: ${e}`);
  failed = result.fail > 0;

  if (!args.includes("--keep-open")) page.close();
} catch (err) {
  console.error("smoke runner error:", err.message);
} finally {
  proc.kill();
  await rm(profile, { recursive: true, force: true }).catch(() => {});
}
process.exit(failed ? 1 : 0);
