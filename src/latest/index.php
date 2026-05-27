<?php

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */
require 'includes/core.php';

$userRegion = getUserRegion();
$settings = getSettings($db);
$layout = $settings['layout_style'] ?? 'grid';
$homeRegion = $settings['restaurant_region'] ?? 'Global';
$currency = $settings['currency_symbol'] ?? '$';

$geoBanners = $db->fetchAll("SELECT * FROM geo_banners WHERE is_active = 1 AND region = ?", [$userRegion]);
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY sort_order ASC");

include 'includes/header.php';
?>

<main class="menu-container">

    <!-- Opening Hours -->
    <?php if(!empty($settings['opening_hours'])): ?>
        <div class="opening-hours card" style="text-align:center; background:#fff; padding:1rem; border-radius:8px; margin-bottom:1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <h3 style="color:var(--secondary-color);">🕒 Opening Hours</h3>
            <p style="white-space: pre-line; color:#555;"><?= htmlspecialchars($settings['opening_hours']) ?></p>
        </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div style="margin-bottom: 2rem;">
        <input type="text" id="menu-search" placeholder="🔍 Search the menu..." style="width: 100%; padding: 1rem; border-radius: 8px; border: 1px solid #ccc; font-size: 1.1rem; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);" onkeyup="searchMenu()">
    </div>
    
    <!-- Allergy Legend -->
    <div class="allergy-legend" style="background:#f1f3f5; padding:1rem; border-radius:8px; margin-bottom:2rem; font-size:0.95rem; text-align:center;">
        <strong>Allergy & Dietary Guide:</strong> 
        🥬 Vegan | 🌾 Gluten-Free | 🧀 Dairy | 🥜 Nuts
        <br><small style="color:#666;">If you have severe allergies, please speak with our staff before ordering.</small>
    </div>

    <!-- Region Warning Banner -->
    <?php if ($homeRegion !== 'Global' && $userRegion !== 'Unknown' && $userRegion !== 'Local' && $userRegion !== $homeRegion): ?>
        <section class="banner alert-banner">
            ⚠️ <b>Note:</b> You appear to be in <?= htmlspecialchars($userRegion) ?>. We are located in <?= htmlspecialchars($homeRegion) ?>!
        </section>
    <?php endif; ?>

    <!-- Geo-Banners -->
    <?php foreach ($geoBanners as $banner): ?>
        <section class="banner geo-banner">📍 Special in <?= htmlspecialchars($userRegion) ?>: <?= htmlspecialchars($banner['message']) ?></section>
    <?php endforeach; ?>

    <?php if(empty($categories)) echo "<p style='text-align:center;'>No menu items available yet.</p>"; ?>
    
    <?php foreach ($categories as $cat): ?>
        <article class="menu-category">
            
            <!-- Category Header Image -->
            <?php if(!empty($cat['image']) && file_exists("uploads/".$cat['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($cat['image']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="category-header-img">
            <?php endif; ?>

            <h2 class="category-title"><?= htmlspecialchars($cat['name']) ?></h2>
            
            <div class="items-<?= $layout ?>">
                <?php 
                // ONLY SHOW PRODUCTS WHERE IS_AVAILABLE = 1
                $items = $db->fetchAll("SELECT * FROM menu_items WHERE category_id = ? AND is_available = 1", [$cat['id']]);
                foreach ($items as $item): 
                    $shareLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on'?"https":"http")."://$_SERVER[HTTP_HOST]".dirname($_SERVER['PHP_SELF'])."/share.php?id=".$item['id'];
                ?>
                    <div class="menu-item <?= $layout ?>-item" data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>">
                        
                        <!-- Uploaded Food Image -->
                        <?php if(!empty($item['image']) && file_exists("uploads/".$item['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" loading="lazy" class="item-img">
                        <?php endif; ?>
                        
                        <div class="item-content">
                            <h3>
                                <a href="share.php?id=<?= $item['id'] ?>" class="item-link"><?= htmlspecialchars($item['name']) ?></a>
                            </h3>
                            <p class="desc"><?= htmlspecialchars($item['description']) ?></p>
                            
                            <!-- Allergens -->
                            <?php if(!empty($item['allergens'])): ?>
                                <div class="allergens-list">
                                    <?php foreach(explode(',', $item['allergens']) as $al): ?>
                                        <span class="allergen-badge"><?= htmlspecialchars($al) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="price-container">
                                <div>
                                    <span class="base-price"><?= $currency ?><?= number_format($item['base_price'], 2) ?></span>
                                    <?php if($item['loyalty_price']): ?>
                                        <span class="loyalty-price">Loyalty: <?= $currency ?><?= number_format($item['loyalty_price'], 2) ?></span>
                                    <?php endif; ?>
                                </div>
                                <button onclick="copyShareLink('<?= $shareLink ?>')" class="btn-share">🔗 Share</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    <?php endforeach; ?>
</main>

<script>
function searchMenu() {
    let input = document.getElementById('menu-search').value.toLowerCase();
    let items = document.getElementsByClassName('menu-item');
    
    for (let i = 0; i < items.length; i++) {
        let name = items[i].getAttribute('data-name');
        if (name.includes(input)) {
            items[i].style.display = "";
        } else {
            items[i].style.display = "none";
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>