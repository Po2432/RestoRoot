/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */

<?php
require 'includes/core.php';

$id = $_GET['id'] ?? 0;
// Only fetch if available
$item = $db->fetch("SELECT * FROM menu_items WHERE id = ? AND is_available = 1", [$id]);

if (!$item) {
    // If food was marked unavailable, boot visitor to 404 error page
    header("Location: error.php?status=404");
    exit;
}

$settings = getSettings($db);
$currency = $settings['currency_symbol'] ?? '$';
$shareLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on'?"https":"http")."://$_SERVER[HTTP_HOST]".dirname($_SERVER['PHP_SELF'])."/share.php?id=".$item['id'];

include 'includes/header.php';
?>

<main class="menu-container" style="max-width: 600px; margin-top: 3rem;">
    <div class="menu-item share-card" style="display:flex; flex-direction:column;">
        
        <!-- Uploaded Food Image -->
        <?php if(!empty($item['image']) && file_exists("uploads/".$item['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 300px; width:100%; object-fit:cover;">
        <?php endif; ?>
        
        <div class="item-content" style="padding: 2rem;">
            <h1 style="color: var(--secondary-color); margin-bottom: 0.5rem;"><?= htmlspecialchars($item['name']) ?></h1>
            <p style="font-size: 1.2rem; margin-bottom: 1rem; color: #555;"><?= htmlspecialchars($item['description']) ?></p>
            
            <?php if(!empty($item['detail_text'])): ?>
                <div class="detail-text" style="background: #f1f3f5; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?= nl2br(htmlspecialchars($item['detail_text'])) ?>
                </div>
            <?php endif; ?>

            <!-- Item Variations / Add-ons -->
            <?php if(!empty($item['variations'])): ?>
                <div style="margin-bottom: 1.5rem; border:1px solid #eee; border-radius:8px; padding:1rem;">
                    <h4 style="margin-bottom: 0.5rem; color: var(--primary-color);">Available Add-ons:</h4>
                    <ul style="list-style:none; padding:0;">
                    <?php 
                        $vars = array_filter(explode(',', $item['variations']));
                        foreach($vars as $v) {
                            $parts = explode(':', $v);
                            if(count($parts) == 2) {
                                echo "<li style='display:flex; justify-content:space-between; margin-bottom:0.3rem; padding-bottom:0.3rem; border-bottom:1px dashed #eee;'>";
                                echo "<span>".htmlspecialchars(trim($parts[0]))."</span>";
                                echo "<strong>+{$currency}".number_format((float)$parts[1], 2)."</strong>";
                                echo "</li>";
                            }
                        }
                    ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if(!empty($item['allergens'])): ?>
                <div class="allergens-list" style="margin-bottom: 1.5rem;">
                    <strong>Dietary info: </strong>
                    <?php foreach(explode(',', $item['allergens']) as $al): ?>
                        <span class="allergen-badge"><?= htmlspecialchars($al) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="price-container" style="border-top: 1px solid #eee; padding-top: 1.5rem; margin-top:auto;">
                <span class="base-price" style="font-size: 1.8rem;"><?= $currency ?><?= number_format($item['base_price'], 2) ?></span>
                <button onclick="copyShareLink('<?= $shareLink ?>')" class="btn-share" style="padding: 0.8rem 1.5rem; font-size: 1.1rem;">🔗 Copy Link</button>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="index.php" style="color: var(--primary-color); font-weight: bold; text-decoration: none;">← See Full Menu</a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>