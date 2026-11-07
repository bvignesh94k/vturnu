// AI Crawler Checker tests: PHP 8.5 (WebAssembly) runs tests/aic/aic-test.php
// against includes/ai-crawlers.php. No network, no database.
//
// Same runtime as tests/crm/run.mjs. From Git Bash at the repo root:
//   PHP_WASM_DIR="$TEMP/php-wasm" MSYS_NO_PATHCONV=1 node tests/aic/run.mjs
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '../..');
const base = (process.env.PHP_WASM_DIR ?? '').replace(/^\/([a-z])\//i, (_, d) => `${d.toUpperCase()}:/`);
if (!base) {
  console.error('Set PHP_WASM_DIR to the folder where @php-wasm/node is installed (see tests/crm/run.mjs).');
  process.exit(2);
}
const mod = (name) => import(pathToFileURL(path.join(base, 'node_modules', name, 'index.js')).href);
const { PHP, SupportedPHPVersions } = await mod('@php-wasm/universal');
const { loadNodeRuntime, createNodeFsMountHandler } = await mod('@php-wasm/node');
const php = new PHP(await loadNodeRuntime(SupportedPHPVersions.includes('8.5') ? '8.5' : '8.4', { emscriptenOptions: { processId: 1 } }));
php.mkdir('/site');
await php.mount('/site', createNodeFsMountHandler(root));
php.mkdir('/t');
await php.mount('/t', createNodeFsMountHandler(here));

const r = await php.run({ code: `<?php require '/t/aic-test.php';` });
process.stdout.write(r.text);
const ok = !r.errors && / 0 failed/.test(r.text) && !/Fatal error|Warning:|Deprecated:|Notice:/.test(r.text);
if (r.errors) console.log(String(r.errors));
console.log(ok ? 'All AI crawler checker tests passed.' : 'AI crawler checker tests FAILED.');
process.exit(ok ? 0 : 1);
