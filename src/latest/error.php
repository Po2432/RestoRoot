<?php

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */

/*
 * Copyright (c) 2026 po2432
 * Repository: https://github.com/Po2432/RestoRoot
 */
$status = filter_input(INPUT_GET, 'status', FILTER_VALIDATE_INT) ?: 404;

$errorTitles = [
    403 => "403 Forbidden Access",
    404 => "404 Page Not Found",
    500 => "500 Internal Server Error"
];

$errorMessages = [
    403 => "You do not have permission to view this directory or page.",
    404 => "The page or food item you are looking for has been removed, renamed, or is currently unavailable.",
    500 => "Something went wrong on our server. Please try refreshing or contact management."
];

$title = $errorTitles[$status] ?? "Error Occurred";
$msg = $errorMessages[$status] ?? "An unexpected error occurred.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="display:flex; justify-content:center; align-items:center; height:100vh; background:#f8f9fa;">
    <main style="max-width:500px; text-align:center; padding:2rem; background:#fff; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin: 1rem;">
        <h1 style="font-size:3rem; color:#e74c3c; margin-bottom:1rem;"><?= $status ?></h1>
        <h2 style="color:#2c3e50; margin-bottom:1rem;"><?= htmlspecialchars($title) ?></h2>
        <p style="color:#666; margin-bottom:2rem;"><?= htmlspecialchars($msg) ?></p>
        <a href="index.php" style="padding: 0.8rem 1.5rem; background:#2c3e50; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold;">Return to Menu</a>
    </main>
</body>
</html>