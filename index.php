<?php
/**
 * Local XAMPP dev and the VPS deploy (.htaccess) both route every request
 * to this file. The real front controller lives at api/index.php instead,
 * because Vercel only executes files inside api/ — a file at this exact
 * root path gets served as a static download there. This file is excluded
 * from the Vercel deployment (.vercelignore) so nothing sits at the root
 * for Vercel to serve statically; on Vercel, requests go straight to
 * api/index.php per vercel.json.
 */
require __DIR__ . '/api/index.php';
