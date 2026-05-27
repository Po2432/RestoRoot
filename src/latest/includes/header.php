<?php

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */
$settings = getSettings($db);
$logoPath = 'uploads/' . ($settings['logo'] ?? '');
$hasLogo = !empty($settings['logo']) && file_exists(__DIR__ . '/../' . $logoPath);
$restName = $settings['restaurant_name'] ?? 'RestoRoot CMS';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($restName) ?></title>
    <link rel="icon" href="<?= $hasLogo ? $logoPath : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="%23e74c3c"/></svg>' ?>">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary-color: <?= htmlspecialchars($settings['primary_color'] ?? '#e74c3c') ?>;
            --secondary-color: <?= htmlspecialchars($settings['secondary_color'] ?? '#2c3e50') ?>;
        }
    </style>
</head>
<body>

    <!-- Global Dismissible Event Banner -->
    <?php if (($settings['global_banner_active'] ?? '0') === '1' && !empty($settings['global_banner_text'])): ?>
        <div id="global-event-banner" class="global-banner" style="display:none;">
            <span><?= htmlspecialchars($settings['global_banner_text']) ?></span>
            <button onclick="dismissBanner()" class="close-banner" aria-label="Dismiss Banner">✖</button>
        </div>
        <script>
            if(!localStorage.getItem('hide_resto_banner')) {
                document.getElementById('global-event-banner').style.display = 'flex';
            }
            function dismissBanner() {
                localStorage.setItem('hide_resto_banner', 'true');
                document.getElementById('global-event-banner').style.display = 'none';
            }
        </script>
    <?php endif; ?>

    <header class="site-header">
        <div class="logo-container">
            <a href="index.php">
            <?php if ($hasLogo): ?>
                <img src="<?= $logoPath ?>" alt="<?= htmlspecialchars($restName) ?> Logo" class="brand-logo">
            <?php else: ?>
                <svg width="250" height="60" viewBox="0 0 250 60">
                    <rect width="100%" height="100%" fill="var(--primary-color)" rx="5"/>
                    <text x="50%" y="50%" fill="#ffffff" font-size="20" font-family="Arial, sans-serif" font-weight="bold" text-anchor="middle" dominant-baseline="middle">
                        <?= htmlspecialchars($restName) ?>
                    </text>
                </svg>
            <?php endif; ?>
            </a>
        </div>
    </header>