<?php
declare(strict_types=1);

/* PHP's built-in server is local development only. Apache/FPM never uses this
   SAPI, so production requests always continue through the WordPress check. */
if (PHP_SAPI === 'cli-server') {
    return;
}

$configFile = __DIR__ . '/auth-config.php';
$config = is_file($configFile) ? require $configFile : [];
if (!is_array($config)) {
    $config = [];
}

$wpLoadPath = getenv('BRAVO_WP_LOAD_PATH') ?: ($config['wp_load_path'] ?? '');
$loginUrl = $config['login_url'] ?? 'https://bravofleet.com/wp-login.php';

if (!$wpLoadPath || !is_file($wpLoadPath)) {
    error_log('Graphics Builder: BRAVO_WP_LOAD_PATH is not configured or is invalid.');
    http_response_code(500);
    exit('Graphics Builder authentication is not configured.');
}

if (!defined('WP_USE_THEMES')) {
    define('WP_USE_THEMES', false);
}
require_once $wpLoadPath;

if (!function_exists('is_user_logged_in')) {
    error_log('Graphics Builder: WordPress loaded without is_user_logged_in().');
    http_response_code(500);
    exit('Graphics Builder authentication is unavailable.');
}

if (is_user_logged_in()) {
    return;
}

http_response_code(401);
header('Content-Type: text/html; charset=UTF-8');
$safeLoginUrl = htmlspecialchars((string) $loginUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Required — Bravo Fleet Graphics Builder</title>
<link rel="stylesheet" href="shared/chrome.css?v=d62425d5">
<style>
  /* chrome.css supplies the palette, the page background and the mono stack;
     this is only the card, which no tool page has an equivalent of. It stays
     inline because it is one block used by one page -- the same test the rest
     of the suite applies before promoting anything into shared/. */
  body{display:grid;place-items:center;padding:24px}
  .auth-card{width:min(100%,520px);padding:36px;border:1px solid var(--line-bright);
             background:var(--panel);box-shadow:0 24px 70px rgb(0 0 0 / 35%);text-align:center}
  .auth-card .eyebrow{color:var(--bolt-gold);font-size:11px;letter-spacing:.24em;text-transform:uppercase}
  .auth-card h1{margin:8px 0 12px;font-size:24px;letter-spacing:.08em;text-transform:uppercase}
  .auth-card .actions{display:flex;justify-content:center;gap:10px;margin-top:24px;flex-wrap:wrap}
  .auth-card .hint{color:var(--ink-dim);font-size:11px;margin-top:18px}
  .auth-card .button{padding:9px 14px;border:1px solid var(--line-bright);color:var(--ink);
             background:var(--panel2);text-decoration:none;text-transform:uppercase;
             font-size:11px;letter-spacing:.08em}
  .auth-card .button:hover,.auth-card .button.primary{border-color:var(--bolt-gold);color:var(--bolt-gold)}
  .auth-card .button.primary:hover{background:var(--bolt-gold);color:#14100a}
</style>
</head>
<body>
<main class="auth-card">
  <p class="eyebrow">Bravo Fleet</p>
  <h1>Login required</h1>
  <p>You must be logged in on Bravo Fleet to use the Graphics Builder.</p>
  <div class="actions">
    <a class="button primary" href="<?= $safeLoginUrl ?>" target="_blank" rel="noopener">Log in to Bravo Fleet</a>
    <a class="button" href="">I’ve logged in — try again</a>
  </div>
  <p class="hint">The login page opens in a new tab. Return here afterward and try again.</p>
</main>
</body>
</html>
<?php exit; ?>
