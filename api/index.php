<?php
/**
 * Vercel entry point. Vercel's `functions` config only matches files inside
 * api/, so this thin proxy lives here and hands off to the real front
 * controller at the project root, which stays there unmoved because local
 * XAMPP dev (_preview-router.php) and the VPS deploy (.htaccess) both expect
 * index.php at the root.
 */
require __DIR__ . '/../index.php';
