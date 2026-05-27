<?php
$settings = getSettings($db);
$restName = $settings['restaurant_name'] ?? 'Our Restaurant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Underway</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#f8f9fa; font-family:system-ui, sans-serif;">
    <main style="max-width:550px; text-align:center; padding:3rem; background:#fff; border-radius:12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin: 1rem;">
        <span style="font-size:4rem;">🛠️</span>
        <h1 style="color:#2c3e50; margin-top:1.5rem; margin-bottom:1rem; font-size:2rem;"><?= htmlspecialchars($restName) ?></h1>
        <h2 style="color:#e74c3c; margin-bottom:1rem; font-size:1.3rem;">Under Maintenance</h2>
        <p style="color:#666; line-height:1.6; margin-bottom:2rem;">
            We are currently updating our digital menu to serve you better. We'll be back online in just a few moments! Thank you for your patience.
        </p>
        <div style="font-size:0.85rem; color:#aaa; border-top: 1px solid #eee; padding-top:1.5rem;">
            Please visit us again shortly.
        </div>
    </main>
</body>
</html>