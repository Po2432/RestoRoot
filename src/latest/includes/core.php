<?php

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */
session_start();

class DB {
    private $pdo;

    public function __construct($dbFile) {
        $this->pdo = new PDO("sqlite:" . $dbFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function addColumnIfNotExists($table, $column, $type) {
        $cols = $this->fetchAll("PRAGMA table_info($table)");
        $exists = false;
        foreach($cols as $c) {
            if($c['name'] === $column) $exists = true;
        }
        if(!$exists) {
            $this->query("ALTER TABLE $table ADD COLUMN $column $type");
        }
    }
}

$dbPath = __DIR__ . '/../data/database.sqlite';
$db = file_exists($dbPath) ? new DB($dbPath) : null;

// Auto-Installer Redirect
if ($db === null) {
    if (file_exists(__DIR__ . '/../install.php')) { 
        header("Location: install.php"); 
        exit; 
    } else { 
        die("Database not found. Please run install.php"); 
    }
}

// AUTO-MIGRATIONS (Seamless Upgrades)
$db->query("CREATE TABLE IF NOT EXISTS feedback (id INTEGER PRIMARY KEY AUTOINCREMENT, customer_name TEXT, message TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
$db->addColumnIfNotExists('menu_items', 'allergens', 'TEXT DEFAULT ""');
$db->addColumnIfNotExists('menu_items', 'detail_text', 'TEXT DEFAULT ""');
$db->addColumnIfNotExists('menu_items', 'variations', 'TEXT DEFAULT ""'); 
$db->addColumnIfNotExists('menu_items', 'is_available', 'INTEGER DEFAULT 1'); 
$db->addColumnIfNotExists('categories', 'image', 'TEXT DEFAULT ""');

// Default Settings
$defaultSettings = [
    ['restaurant_region', 'Global'], ['layout_style', 'grid'], 
    ['social_ig', ''], ['social_fb', ''], ['social_wa', ''], ['social_web', ''],
    ['currency_symbol', '$'], ['opening_hours', ''], 
    ['global_banner_text', ''], ['global_banner_active', '0'],
    ['maintenance_mode', '0']
];

$stmt = $db->query("INSERT OR IGNORE INTO settings (key_name, value) VALUES (?, ?)", ['dummy', 'dummy']);
foreach ($defaultSettings as $ds) {
    $db->query("INSERT OR IGNORE INTO settings (key_name, value) VALUES (?, ?)", $ds);
}

function getSettings($db) {
    $rows = $db->fetchAll("SELECT key_name, value FROM settings");
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['key_name']] = $row['value'];
    }
    return $settings;
}

$settings = getSettings($db);

// --- STRICT EULA GATEKEEPER LOCKOUT ---
$eulaFlag = __DIR__ . '/../eula.flag';
$currentPage = basename($_SERVER['PHP_SELF']);
$bypassPages = ['install.php', 'login.php', 'logout.php', 'admin.php', 'error.php'];

// Block public access if EULA flag file is missing
if (!file_exists($eulaFlag) && !in_array($currentPage, $bypassPages)) {
    // RELEASE THE SESSION WRITE LOCK IMMEDIATELY
    // This prevents cascading connection timeouts if the page is refreshed or accessed multiple times.
    session_write_close(); 

    $eulaText = "EULA agreement file (EULA.md) is missing. Please read the license terms.";
    if (file_exists(__DIR__ . '/../EULA.md')) {
        $eulaText = file_get_contents(__DIR__ . '/../EULA.md');
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>License Agreement Required</title>
        <style>
            body { font-family: ui-monospace, monospace; background: #0f172a; color: #cbd5e1; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 1rem; margin: 0; }
            .box { max-width: 650px; width: 100%; background: #1e293b; padding: 2rem; border-radius: 8px; border: 1px solid #334155; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
            h1 { color: #fff; font-size: 1.4rem; margin-top: 0; margin-bottom: 0.5rem; }
            pre { background: #0b0f19; padding: 1rem; border-radius: 4px; border: 1px solid #334155; font-size: 0.85rem; overflow-y: auto; height: 220px; white-space: pre-wrap; color: #94a3b8; line-height: 1.5; margin-bottom: 1.5rem; }
            .instructions { background: rgba(231, 76, 60, 0.1); border-left: 4px solid #e74c3c; padding: 1rem; border-radius: 4px; color: #f8a5c2; font-size: 0.9rem; margin-bottom: 0; line-height: 1.5; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>License Verification Required</h1>
            <p style="font-size: 0.9rem; color: #94a3b8; margin-bottom: 1.5rem;">This application is currently locked because the End User License Agreement (EULA) has not been finalized.</p>
            <pre><?= htmlspecialchars($eulaText) ?></pre>
            <div class="instructions">
                <strong>🔒 Access Blocked</strong><br>
                If you are the administrator of this site and missed the agreement phase during installation, you must manually create an empty file named <code>eula.flag</code> in your root directory via FTP or File Manager to unlock your public views.\n (c) 2026 Po2432
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// MAINTENANCE BLOCKER
if (($settings['maintenance_mode'] ?? '0') === '1' && !isset($_SESSION['user_id'])) {
    if (!in_array($currentPage, $bypassPages)) {
        include __DIR__ . '/../maintenance.php';
        exit;
    }
}

function requireRole($role = 'manager') {
    if (!isset($_SESSION['user_id'])) { 
        header("Location: login.php"); 
        exit; 
    }
    if ($role === 'admin' && $_SESSION['role'] !== 'admin') {
        die("Access Denied. Admin role required.");
    }
}

function getUserRegion() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if ($ip === '127.0.0.1' || $ip === '::1') return 'Local'; 
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=regionName", false, $ctx);
    if ($json) { 
        $data = json_decode($json, true); 
        return $data['regionName'] ?? 'Unknown'; 
    }
    return 'Unknown';
}
