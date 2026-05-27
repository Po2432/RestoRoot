<?php

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */
require 'includes/core.php';
requireRole('manager');

$action = $_GET['action'] ?? 'dashboard';

// Handle Add Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_item') {
    $imgName = '';
    if (!empty($_FILES['image']['name'])) {
        $imgName = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$imgName");
    }
    $allergens = isset($_POST['allergens']) ? implode(',', $_POST['allergens']) : '';
    $db->query("INSERT INTO menu_items (category_id, name, description, detail_text, variations, base_price, loyalty_price, image, allergens) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", 
        [$_POST['category_id'], $_POST['name'], $_POST['description'], $_POST['detail_text'], $_POST['variations'], $_POST['base_price'], $_POST['loyalty_price'], $imgName, $allergens]);
    header("Location: admin.php?action=dashboard&msg=Item+Added");
    exit;
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_category') {
    $imgName = '';
    if (!empty($_FILES['image']['name'])) {
        $imgName = time() . '_cat_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$imgName");
    }
    $db->query("INSERT INTO categories (name, sort_order, image) VALUES (?, ?, ?)", [$_POST['name'], (int)$_POST['sort_order'], $imgName]);
    header("Location: admin.php?action=categories&msg=Category+Added");
    exit;
}

// Handle Delete Item
if ($action === 'delete_item' && isset($_GET['id'])) {
    $db->query("DELETE FROM menu_items WHERE id = ?", [$_GET['id']]);
    header("Location: admin.php?action=dashboard&msg=Item+Deleted");
    exit;
}

// Handle Toggle Item Availability
if ($action === 'toggle_availability' && isset($_GET['id'])) {
    $item = $db->fetch("SELECT is_available FROM menu_items WHERE id = ?", [$_GET['id']]);
    $newStatus = $item['is_available'] == 1 ? 0 : 1;
    $db->query("UPDATE menu_items SET is_available = ? WHERE id = ?", [$newStatus, $_GET['id']]);
    header("Location: admin.php?action=dashboard&msg=Availability+Updated");
    exit;
}

// Handle Delete Category
if ($action === 'delete_category' && isset($_GET['id'])) {
    $db->query("DELETE FROM categories WHERE id = ?", [$_GET['id']]);
    // Safety fallback: Unassign foods from this category
    $db->query("UPDATE menu_items SET category_id = 0 WHERE category_id = ?", [$_GET['id']]);
    header("Location: admin.php?action=categories&msg=Category+Deleted");
    exit;
}

// Handle Delete Single Review
if ($action === 'delete_feedback' && isset($_GET['id'])) {
    $db->query("DELETE FROM feedback WHERE id = ?", [$_GET['id']]);
    header("Location: admin.php?action=feedback&msg=Review+Deleted");
    exit;
}

// Handle Delete All Reviews
if ($action === 'delete_all_feedback') {
    $db->query("DELETE FROM feedback");
    header("Location: admin.php?action=feedback&msg=All+Reviews+Deleted");
    exit;
}

// Handle Change Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'change_password') {
    $user = $db->fetch("SELECT password FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if (password_verify($_POST['current_password'], $user['password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $db->query("UPDATE users SET password = ? WHERE id = ?", [$newHash, $_SESSION['user_id']]);
            header("Location: admin.php?action=settings&msg=Password+Updated+Successfully");
            exit;
        } else {
            header("Location: admin.php?action=settings&error=New+passwords+do+not+match");
            exit;
        }
    } else {
        header("Location: admin.php?action=settings&error=Incorrect+current+password");
        exit;
    }
}

// Handle Settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'settings') {
    requireRole('admin');
    $fields = ['restaurant_name', 'primary_color', 'secondary_color', 'restaurant_region', 'layout_style', 'social_ig', 'social_fb', 'social_wa', 'social_web', 'currency_symbol', 'opening_hours', 'global_banner_text', 'global_banner_active', 'maintenance_mode'];
    
    foreach ($fields as $f) {
        $val = $_POST[$f] ?? '';
        if ($f === 'global_banner_active' && !isset($_POST[$f])) $val = '0';
        if ($f === 'maintenance_mode' && !isset($_POST[$f])) $val = '0';
        $db->query("UPDATE settings SET value=? WHERE key_name=?", [$val, $f]);
    }
    
    if (!empty($_FILES['logo']['name'])) {
        $logoName = time() . '_' . basename($_FILES['logo']['name']);
        move_uploaded_file($_FILES['logo']['tmp_name'], "uploads/$logoName");
        $db->query("UPDATE settings SET value=? WHERE key_name='logo'", [$logoName]);
    }
    header("Location: admin.php?action=settings&msg=Settings+Saved");
    exit;
}

$settings = getSettings($db);
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>RestoRoot Admin</title><link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <h1>RestoRoot Admin</h1>
        <nav>
            <a href="admin.php?action=dashboard">Dashboard</a>
            <a href="admin.php?action=add_item_form">Add Item</a>
            <a href="admin.php?action=categories">Categories</a>
            <a href="admin.php?action=feedback">Feedback</a>
            <a href="admin.php?action=settings">Settings</a>
            <a href="index.php" target="_blank">Live Site</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    
    <main class="admin-container">
        <?php if (isset($_GET['msg'])) echo "<div class='alert' style='background:#d4edda; color:#155724;'>".htmlspecialchars($_GET['msg'])."</div>"; ?>
        <?php if (isset($_GET['error'])) echo "<div class='alert' style='background:#f8d7da; color:#721c24;'>".htmlspecialchars($_GET['error'])."</div>"; ?>

        <!-- DASHBOARD: LIST ALL PRODUCTS -->
        <?php if ($action === 'dashboard'): ?>
            <section class="card mb-2">
                <h2>Menu QR Code</h2>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($baseUrl) ?>" alt="QR Code">
                <p>Print this code for your tables.</p>
            </section>

            <section class="card">
                <h2>Manage Products Dashboard</h2>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse; margin-top:1rem; text-align: left;">
                        <thead>
                            <tr style="background:#f1f3f5; border-bottom: 2px solid #ddd;">
                                <th style="padding:10px;">Food</th>
                                <th style="padding:10px;">Price</th>
                                <th style="padding:10px;">Availability</th>
                                <th style="padding:10px; text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $items = $db->fetchAll("SELECT menu_items.*, categories.name as cat_name FROM menu_items LEFT JOIN categories ON menu_items.category_id = categories.id ORDER BY menu_items.category_id");
                            if(empty($items)) echo "<tr><td colspan='4' style='padding:10px;'>No food items found. Add some!</td></tr>";
                            foreach($items as $item): 
                            ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding:10px;">
                                        <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                        <small style="color:#666;"><?= htmlspecialchars($item['cat_name'] ?? 'Uncategorized') ?></small>
                                    </td>
                                    <td style="padding:10px;"><?= htmlspecialchars($settings['currency_symbol'] ?? '$') ?><?= number_format($item['base_price'], 2) ?></td>
                                    <td style="padding:10px;">
                                        <a href="admin.php?action=toggle_availability&id=<?= $item['id'] ?>" style="text-decoration:none; padding: 4px 8px; border-radius: 4px; font-weight:bold; font-size:0.8rem; background: <?= $item['is_available'] == 1 ? '#d4edda; color:#155724;' : '#f8d7da; color:#721c24;' ?>">
                                            <?= $item['is_available'] == 1 ? 'Available' : 'Unavailable' ?>
                                        </a>
                                    </td>
                                    <td style="padding:10px; text-align:right;">
                                        <a href="admin.php?action=delete_item&id=<?= $item['id'] ?>" onclick="return confirm('Delete this food item?');" style="color:#e74c3c; font-weight:bold; text-decoration:none;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <!-- ADD MENU ITEM FORM -->
        <?php if ($action === 'add_item_form'): ?>
            <section class="card">
                <h2>Add Menu Item</h2>
                <form action="admin.php?action=add_item" method="POST" enctype="multipart/form-data" class="form-grid">
                    <input type="text" name="name" placeholder="Item Name" required>
                    <select name="category_id" required>
                        <?php foreach($db->fetchAll("SELECT * FROM categories ORDER BY sort_order") as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div style="display:flex; gap:1rem;">
                        <input type="number" step="0.01" name="base_price" placeholder="Base Price" required style="flex:1">
                        <input type="number" step="0.01" name="loyalty_price" placeholder="Loyalty Price (Optional)" style="flex:1">
                    </div>
                    <textarea name="description" placeholder="Short Description (Appears on menu)"></textarea>
                    <textarea name="detail_text" placeholder="Long Description (Appears on details/share page)"></textarea>
                    
                    <label>Item Variations / Add-ons:
                        <small>Format: <b>Name:Price, Name2:Price</b> (e.g. Extra Cheese:1.50, Bacon:2.00)</small>
                        <textarea name="variations" placeholder="Extra Cheese:1.50, Bacon:2.00"></textarea>
                    </label>

                    <label>Allergens & Tags:</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="allergens[]" value="Vegan"> 🥬 Vegan</label>
                        <label><input type="checkbox" name="allergens[]" value="Gluten-Free"> 🌾 Gluten-Free</label>
                        <label><input type="checkbox" name="allergens[]" value="Dairy"> 🧀 Dairy</label>
                        <label><input type="checkbox" name="allergens[]" value="Nuts"> 🥜 Nuts</label>
                    </div>

                    <label>Image: <input type="file" name="image" accept="image/*"></label>
                    <button type="submit">Save Menu Item</button>
                </form>
            </section>
        <?php endif; ?>

        <!-- MANAGE CATEGORIES -->
        <?php if ($action === 'categories'): ?>
            <section class="card">
                <h2>Add Category</h2>
                <form action="admin.php?action=add_category" method="POST" enctype="multipart/form-data" class="form-grid">
                    <input type="text" name="name" placeholder="Category Name (e.g. Desserts)" required>
                    <input type="number" name="sort_order" placeholder="Sort Order (e.g. 1)" required>
                    <label>Category Header Image (Optional): <input type="file" name="image" accept="image/*"></label>
                    <button type="submit">Add Category</button>
                </form>
                <hr style="margin:2rem 0;">
                <h3>Existing Categories</h3>
                <ul style="list-style:none; padding:0;">
                    <?php foreach($db->fetchAll("SELECT * FROM categories ORDER BY sort_order") as $c): ?>
                        <li style="margin-bottom: 0.8rem; padding-bottom:0.8rem; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong><?= htmlspecialchars($c['name']) ?></strong> (Order: <?= $c['sort_order'] ?>)
                                <?php if(!empty($c['image'])): ?> <small style="color:#27ae60;">[Header Image Attached]</small> <?php endif; ?>
                            </div>
                            <a href="admin.php?action=delete_category&id=<?= $c['id'] ?>" onclick="return confirm('Deleting this category will unassign its products. Proceed?');" style="color:#e74c3c; text-decoration:none; font-weight:bold;">🗑️ Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endif; ?>

        <!-- MANAGE FEEDBACK -->
        <?php if ($action === 'feedback'): ?>
            <section class="card">
                <?php $fb = $db->fetchAll("SELECT * FROM feedback ORDER BY created_at DESC"); ?>
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                    <h2>Customer Feedback</h2>
                    <?php if(!empty($fb)): ?>
                        <a href="admin.php?action=delete_all_feedback" onclick="return confirm('⚠️ DANGER: Are you absolutely sure you want to delete ALL customer reviews? This action cannot be undone.');" style="background:#e74c3c; color:#fff; text-decoration:none; padding:0.6rem 1.2rem; border-radius:4px; font-weight:bold; font-size:0.9rem;">🗑️ Delete All Reviews</a>
                    <?php endif; ?>
                </div>

                <?php if(empty($fb)) echo "<p>No feedback reviews found.</p>"; ?>
                
                <?php foreach($fb as $f): ?>
                    <div style="border-bottom:1px solid #eee; padding: 1.2rem 0; display:flex; justify-content:space-between; align-items:flex-start; gap:1.5rem;">
                        <div style="flex-grow:1;">
                            <strong><?= htmlspecialchars($f['customer_name']) ?></strong> 
                            <small style="color:#888; margin-left:10px;"><?= $f['created_at'] ?></small>
                            <p style="margin-top:0.5rem; color:#444;"><?= htmlspecialchars($f['message']) ?></p>
                        </div>
                        <a href="admin.php?action=delete_feedback&id=<?= $f['id'] ?>" onclick="return confirm('Delete this review?');" style="color:#e74c3c; text-decoration:none; font-weight:bold; font-size:0.9rem; padding:0.3rem 0.6rem; border:1px solid #e74c3c; border-radius:4px; background:#fff8f8;">🗑️ Delete</a>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <!-- SETTINGS, SYSTEM, CONFIG, & MAINTENANCE -->
        <?php if ($action === 'settings'): ?>
            <section class="card mb-2">
                <h2>Security / Change Password</h2>
                <form action="admin.php?action=change_password" method="POST" class="form-grid">
                    <input type="password" name="current_password" placeholder="Current Password" required>
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                    <button type="submit" style="background:#2c3e50;">Update Password</button>
                </form>
            </section>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <section class="card mb-2">
                <h2>Server Rewrite & Redirection Config</h2>
                <p>To route all error pages to `error.php?status=code` automatically, add these configurations to your web server:</p>
                
                <?php if(strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Nginx') !== false): ?>
                    <h4>Detected Web Server: <b>Nginx</b></h4>
                    <pre style="background:#f1f3f5; padding:1rem; border-radius:4px; overflow-x:auto; font-size:0.9rem;">
# Add this block inside your nginx server block:
error_page 404 /error.php?status=404;
error_page 403 /error.php?status=403;
error_page 500 502 503 504 /error.php?status=500;
                    </pre>
                <?php else: ?>
                    <h4>Detected Web Server: <b>Apache</b> (or generic)</h4>
                    <p>Copy these rules and save them in a file named <b>`.htaccess`</b> in your root directory:</p>
                    <pre style="background:#f1f3f5; padding:1rem; border-radius:4px; overflow-x:auto; font-size:0.9rem;">
ErrorDocument 404 /error.php?status=404
ErrorDocument 403 /error.php?status=403
ErrorDocument 500 /error.php?status=500
                    </pre>
                <?php endif; ?>
            </section>

            <section class="card">
                <h2>System Settings & Maintenance Mode</h2>
                <form action="admin.php?action=settings" method="POST" enctype="multipart/form-data" class="form-grid">
                    
                    <label style="background: #fff8f8; padding: 1rem; border-radius: 8px; border: 1px solid #fbc4c4; font-weight: bold; color: #c0392b;">
                        <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                        ⚠️ ENABLE MAINTENANCE MODE (Blocks public visitors with a styled message)
                    </label>

                    <label>Restaurant Name:<input type="text" name="restaurant_name" value="<?= htmlspecialchars($settings['restaurant_name'] ?? '') ?>"></label>
                    
                    <div style="display:flex; gap:1rem;">
                        <label style="flex:1">Primary Color:<input type="color" name="primary_color" value="<?= htmlspecialchars($settings['primary_color'] ?? '#e74c3c') ?>" style="width:100%"></label>
                        <label style="flex:1">Secondary Color:<input type="color" name="secondary_color" value="<?= htmlspecialchars($settings['secondary_color'] ?? '#2c3e50') ?>" style="width:100%"></label>
                    </div>

                    <label>Logo Upload: <input type="file" name="logo" accept="image/png, image/jpeg, image/svg+xml"></label>
                    
                    <hr>
                    <label>Currency Symbol (e.g. $, €, £):
                        <input type="text" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '$') ?>" required>
                    </label>

                    <label>Opening Hours:
                        <textarea name="opening_hours" placeholder="Mon-Fri: 9AM - 10PM..."><?= htmlspecialchars($settings['opening_hours'] ?? '') ?></textarea>
                    </label>

                    <hr>
                    <label>Restaurant's Home Region (e.g. 'California' or 'London'):
                        <input type="text" name="restaurant_region" value="<?= htmlspecialchars($settings['restaurant_region'] ?? 'Global') ?>">
                        <small>Users outside this region will see a warning banner.</small>
                    </label>

                    <label>Menu Layout:
                        <select name="layout_style">
                            <option value="grid" <?= ($settings['layout_style'] ?? 'grid') == 'grid' ? 'selected' : '' ?>>Grid (Cards)</option>
                            <option value="list" <?= ($settings['layout_style'] ?? 'grid') == 'list' ? 'selected' : '' ?>>List (Compact)</option>
                        </select>
                    </label>

                    <hr>
                    <h3>Global Event Banner</h3>
                    <label>
                        <input type="checkbox" name="global_banner_active" value="1" <?= ($settings['global_banner_active'] ?? '0') === '1' ? 'checked' : '' ?>> Enable Banner
                    </label>
                    <input type="text" name="global_banner_text" placeholder="e.g. 🎉 20% Off all Burgers Today!" value="<?= htmlspecialchars($settings['global_banner_text'] ?? '') ?>">

                    <hr>
                    <h3>Social Media Links (Leave blank to hide)</h3>
                    <label>Instagram URL: <input type="url" name="social_ig" value="<?= htmlspecialchars($settings['social_ig'] ?? '') ?>"></label>
                    <label>Facebook URL: <input type="url" name="social_fb" value="<?= htmlspecialchars($settings['social_fb'] ?? '') ?>"></label>
                    <label>WhatsApp URL: <input type="url" name="social_wa" value="<?= htmlspecialchars($settings['social_wa'] ?? '') ?>"></label>
                    <label>Website URL: <input type="url" name="social_web" value="<?= htmlspecialchars($settings['social_web'] ?? '') ?>"></label>
                    
                    <button type="submit">Update Branding & Settings</button>
                </form>
            </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>