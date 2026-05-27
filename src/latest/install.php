<?php

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */
$dataDir = __DIR__ . '/data';
$uploadsDir = __DIR__ . '/uploads';

// 1. Auto-create the folders if they don't exist
if (!file_exists($dataDir)) {
    @mkdir($dataDir, 0775, true);
}
if (!file_exists($uploadsDir)) {
    @mkdir($uploadsDir, 0775, true);
}

// 2. Check if it worked
if (!is_writable($dataDir) || !is_writable($uploadsDir)): 
?>
    <div style="font-family: sans-serif; margin: 2rem;">
        <h2 style="color: #e74c3c;">Action Required: Missing Folders</h2>
        <p>We couldn't automatically create the necessary folders.</p>
        <p><b>How to fix this on your PC:</b></p>
        <ol>
            <li>Open your <b>RestoRoot CMS</b> folder.</li>
            <li>Right-click in the empty space > <b>New > Folder</b>. Name it <b>data</b></li>
            <li>Right-click again > <b>New > Folder</b>. Name it <b>uploads</b></li>
            <li>Refresh this page!</li>
        </ol>
    </div>
<?php
    exit;
endif;

// 3. Create the Database
$dbFile = $dataDir . '/database.sqlite';
$pdo = new PDO("sqlite:" . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$schema = "
    CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE, password TEXT, role TEXT);
    CREATE TABLE IF NOT EXISTS settings (key_name TEXT PRIMARY KEY, value TEXT);
    CREATE TABLE IF NOT EXISTS categories (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, sort_order INTEGER);
    CREATE TABLE IF NOT EXISTS menu_items (id INTEGER PRIMARY KEY AUTOINCREMENT, category_id INTEGER, name TEXT, description TEXT, base_price REAL, loyalty_price REAL, image TEXT);
    CREATE TABLE IF NOT EXISTS events (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT, description TEXT, start_time DATETIME, end_time DATETIME);
    CREATE TABLE IF NOT EXISTS geo_banners (id INTEGER PRIMARY KEY AUTOINCREMENT, region TEXT, message TEXT, is_active INTEGER DEFAULT 1);
";
$pdo->exec($schema);

// Add Default Admin
$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("INSERT OR IGNORE INTO users (username, password, role) VALUES ('admin', '$adminPass', 'admin')");

// Add Default Category
$pdo->exec("INSERT OR IGNORE INTO categories (id, name, sort_order) VALUES (1, 'Main Courses', 1)");

// Add Default Settings
$settings = [
    ['restaurant_name', 'My RestoRoot'],
    ['primary_color', '#e74c3c'],
    ['secondary_color', '#2c3e50'],
    ['logo', '']
];
$stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (key_name, value) VALUES (?, ?)");
foreach ($settings as $setting) {
    $stmt->execute($setting);
}
?>

<!-- Success Output -->
<div style="font-family: sans-serif; margin: 2rem;">
    <h2 style="color: #27ae60;">RestoRoot Installed Successfully!</h2>
    <p>Admin User: <b>admin</b><br>Password: <b>admin123</b></p>
    <a href="login.php" style="display:inline-block; padding: 10px 20px; background: #e74c3c; color: white; text-decoration: none; border-radius: 5px;">Go to Login</a>
    <br><br>
    <small style="color:red;">IMPORTANT: Delete install.php from your folder before putting this on the internet!</small>
</div>