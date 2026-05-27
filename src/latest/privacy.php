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
$settings = getSettings($db);
$restName = $settings['restaurant_name'] ?? 'This Restaurant';
include 'includes/header.php';
?>
<main class="menu-container" style="max-width: 800px; margin-top: 3rem; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <h1 style="color: var(--secondary-color); margin-bottom: 1rem;">Privacy Policy</h1>
    <p>Last updated: <?= date('F j, Y') ?></p>
    
    <h3 style="margin-top:1.5rem;">1. Information We Collect</h3>
    <p><strong><?= htmlspecialchars($restName) ?></strong> respects your privacy. We collect minimal data necessary to provide our services. We do not require you to create an account to view our menu. We may collect basic diagnostic data such as IP addresses (to display regional offers) and anonymous analytics.</p>
    
    <h3 style="margin-top:1.5rem;">2. Cookies and Storage</h3>
    <p>We use simple Local Storage strictly to remember if you have dismissed our announcements or banners. We do not use third-party tracking cookies to follow you across the internet.</p>
    
    <h3 style="margin-top:1.5rem;">3. Customer Feedback</h3>
    <p>If you submit feedback through our portal, your name and message are stored securely in our database solely for improving our services. This data is kept strictly internal and is never sold to third parties.</p>

    <h3 style="margin-top:1.5rem;">4. Contact Us</h3>
    <p>If you have any questions about this policy or your data, please speak to our management team in-store or reach out via our official social media channels.</p>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="index.php" style="color: var(--primary-color); font-weight: bold; text-decoration: none;">← Back to Menu</a>
    </div>
</main>
<?php include 'includes/footer.php'; ?>