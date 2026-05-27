/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */

<?php
/**
 * RestoRoot Bootstrapper Installer
 * Automatically downloads, secures, and configures files.
 * App code owned by Po2432.
 */
session_start();

// Handle self-deletion and redirecting
if (isset($_GET['clean_and_launch'])) {
    if (file_exists(__FILE__)) {
        @unlink(__FILE__);
    }
    header("Location: install.php");
    exit;
}

// Default Configuration
$defaultRepo   = "Po2432/RestoRoot";
$defaultBranch = "main";
$defaultPath   = "src/latest";

$repo   = trim($_POST['repo'] ?? $defaultRepo);
$branch = trim($_POST['branch'] ?? $defaultBranch);
$path   = trim($_POST['path'] ?? $defaultPath);

// Target file manifest to download (Includes EULA and License)
$files = [
    'index.php',
    'install.php',
    'login.php',
    'logout.php',
    'admin.php',
    'share.php',
    'feedback.php',
    'privacy.php',
    'error.php',
    'maintenance.php',
    'style.css',
    'EULA.md',
    'LICENSE.md',
    'includes/core.php',
    'includes/header.php',
    'includes/footer.php'
];

$downloadLogs = [];
$downloadError = false;

// Robust download handler
function downloadUrl($url, $saveTo) {
    $dir = dirname($saveTo);
    if (!file_exists($dir)) {
        @mkdir($dir, 0775, true);
    }

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 RestoRoot Bootstrapper');
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $data !== false) {
            return file_put_contents($saveTo, $data) !== false;
        }
    } elseif (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'header'  => "User-Agent: RestoRoot Bootstrapper\r\n"
            ]
        ]);
        $data = @file_get_contents($url, false, $context);
        if ($data !== false) {
            return file_put_contents($saveTo, $data) !== false;
        }
    }
    return false;
}

// Trigger installation sequence
$installing = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_install']));
if ($installing) {
    // Force agreement verification on backend
    if (!isset($_POST['agree_terms'])) {
        $downloadError = true;
        $downloadLogs[] = "❌ Error: You must read and accept the EULA/License terms to run this installer.";
    } else {
        @mkdir('data', 0775, true);
        @mkdir('uploads', 0775, true);

        foreach ($files as $file) {
            $rawUrl = "https://raw.githubusercontent.com/{$repo}/{$branch}/{$path}/{$file}";
            $localPath = __DIR__ . '/' . $file;

            if (downloadUrl($rawUrl, $localPath)) {
                $downloadLogs[] = "✓ Successfully fetched: {$file}";
            } else {
                $downloadLogs[] = "❌ Failed to fetch: {$file} (Tried URL: {$rawUrl})";
                $downloadError = true;
            }
        }

        if (!$downloadError) {
            // Write EULA agreement flag upon successful configuration
            @file_put_contents(__DIR__ . '/eula.flag', "ACCEPTED DURING BOOTSTRAP: " . date('Y-m-d H:i:s'));
            $downloadLogs[] = "✓ Generated license verification flag (eula.flag)";

            $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
            $isApache = (stripos($serverSoftware, 'Apache') !== false || stripos($serverSoftware, 'LiteSpeed') !== false);

            if ($isApache) {
                $htaccessContent = "Require all denied";
                @file_put_contents(__DIR__ . '/data/.htaccess', $htaccessContent);
                
                if (!file_exists(__DIR__ . '/.htaccess')) {
                    $rootHtaccess = "ErrorDocument 404 /error.php?status=404\n";
                    $rootHtaccess .= "ErrorDocument 403 /error.php?status=403\n";
                    $rootHtaccess .= "ErrorDocument 500 /error.php?status=500\n";
                    @file_put_contents(__DIR__ . '/.htaccess', $rootHtaccess);
                }
                $downloadLogs[] = "✓ Configured local Apache rewrite protections (.htaccess)";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RestoRoot Bootstrapper</title>
    <style>
        :root {
            --bg-color: #121824;
            --card-bg: #1e293b;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
            --primary: #e74c3c;
            --success: #10b981;
            --border: #334155;
        }
        body { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; background: var(--bg-color); color: var(--text-color); padding: 2rem 1rem; line-height: 1.5; }
        .wrapper { max-width: 650px; margin: 0 auto; }
        .card { background: var(--card-bg); border: 1px solid var(--border); padding: 2rem; border-radius: 8px; margin-bottom: 1.5rem; }
        h1 { font-size: 1.5rem; margin-bottom: 0.5rem; color: #fff; }
        p { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; }
        .status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; background: var(--border); }
        .status-ok { background: rgba(16, 185, 129, 0.2); color: var(--success); }
        .status-bad { background: rgba(231, 76, 60, 0.2); color: var(--primary); }
        .form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
        label { font-size: 0.85rem; font-weight: bold; color: var(--text-muted); }
        input[type="text"] { background: var(--bg-color); border: 1px solid var(--border); padding: 0.6rem; border-radius: 4px; color: #fff; font-family: inherit; }
        button { background: var(--primary); color: #fff; border: none; padding: 0.8rem 1.5rem; font-family: inherit; font-weight: bold; border-radius: 4px; cursor: pointer; transition: opacity 0.2s; }
        button:hover { opacity: 0.9; }
        .btn-launch { background: var(--success); display: inline-block; text-decoration: none; color: white; padding: 0.8rem 1.5rem; border-radius: 4px; font-weight: bold; text-align: center; }
        .log-box { background: #0f172a; border: 1px solid var(--border); padding: 1rem; border-radius: 4px; font-size: 0.85rem; max-height: 250px; overflow-y: auto; margin-bottom: 1.5rem; }
        .log-entry { margin-bottom: 0.3rem; }
        .checkbox-row { display: flex; align-items: flex-start; gap: 0.5rem; background: #0f172a; border: 1px solid var(--border); padding: 1rem; border-radius: 4px; margin: 1.5rem 0; }
        .checkbox-row input { margin-top: 0.2rem; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <h1>RestoRoot Bootstrapper</h1>
        <p>A lightweight setup tool to download, secure, and bootstrap RestoRoot directly from GitHub.</p>

        <div style="display:flex; gap:1rem; margin-bottom: 2rem; flex-wrap: wrap;">
            <div>
                Dir Writable: 
                <?php if(is_writable(__DIR__)): ?>
                    <span class="status-badge status-ok">Writable</span>
                <?php else: ?>
                    <span class="status-badge status-bad">Locked</span>
                <?php endif; ?>
            </div>
            <div>
                Downloader Engine: 
                <?php if(function_exists('curl_init') || ini_get('allow_url_fopen')): ?>
                    <span class="status-badge status-ok">Available</span>
                <?php else: ?>
                    <span class="status-badge status-bad">Missing</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$installing || $downloadError): ?>
            <form method="POST">
                <?php if ($downloadError): ?>
                    <div style="background: rgba(231, 76, 60, 0.1); border: 1px solid var(--primary); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                        <span style="color:var(--primary); font-weight:bold;">⚠️ Warning:</span> Deployment parameters failed. Ensure paths and internet connectivity are correct.
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>GitHub Repository Path:</label>
                    <input type="text" name="repo" value="<?= htmlspecialchars($repo) ?>" required>
                </div>
                <div class="form-group">
                    <label>Repository Branch:</label>
                    <input type="text" name="branch" value="<?= htmlspecialchars($branch) ?>" required>
                </div>
                <div class="form-group">
                    <label>Subdirectory Path (within repository):</label>
                    <input type="text" name="path" value="<?= htmlspecialchars($path) ?>">
                </div>

                <!-- Required EULA/License Checkbox Verification -->
                <div class="checkbox-row">
                    <input type="checkbox" name="agree_terms" id="agree_terms" value="1" required>
                    <label for="agree_terms" style="font-weight: normal; color: #cbd5e1; cursor: pointer;">
                        I agree to the <strong>RestoRoot End User License Agreement (EULA.md)</strong> and <strong>LICENSE.md</strong> terms. I understand commercial redistribution or code resale is strictly prohibited.
                    </label>
                </div>
                
                <button type="submit" name="start_install" style="width:100%;">Pull Files & Secure System</button>
            </form>
        <?php else: ?>
            <h3>Deployment Output:</h3>
            <div class="log-box">
                <?php foreach ($downloadLogs as $log): ?>
                    <div class="log-entry"><?= htmlspecialchars($log) ?></div>
                <?php endforeach; ?>
            </div>

            <?php if (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Nginx') !== false): ?>
                <div style="background: rgba(231, 76, 60, 0.15); border: 1px solid var(--primary); padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <strong>🛡️ Nginx Security Directive Needed:</strong><br>
                    Add this to your configuration to block access to your local SQLite file:
                    <pre style="background:#0f172a; padding:0.5rem; border-radius:4px; margin-top:0.5rem; font-size:0.8rem; overflow-x:auto;">
location ~* \.sqlite$ {
    deny all;
}</pre>
                </div>
            <?php endif; ?>

            <div style="text-align:center;">
                <p>Downloading complete! Launching the setup script will permanently delete this bootstrapper file.</p>
                <a href="installer.php?clean_and_launch=1" class="btn-launch">Launch Setup & Self-Delete</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
