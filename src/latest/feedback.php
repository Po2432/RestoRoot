<?php
require 'includes/core.php';

// Initialize variables to prevent linter "undefined variable" warnings
$msg = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs to prevent blank spaces or malicious HTML tags
    $name = trim(strip_tags($_POST['name'] ?? ''));
    $message = trim(strip_tags($_POST['message'] ?? ''));
    
    if (!empty($name) && !empty($message)) {
        $db->query("INSERT INTO feedback (customer_name, message) VALUES (?, ?)", [$name, $message]);
        $msg = "Thank you for your feedback!";
    } else {
        $error = "Please fill out all fields.";
    }
}

include 'includes/header.php';
?>

<main class="menu-container" style="max-width: 500px; margin-top: 3rem;">
    <div class="card">
        <h2 style="color: var(--secondary-color); margin-bottom: 1rem;">We Value Your Opinion 🍽️</h2>
        
        <?php if ($msg): ?>
            <!-- Success Message -->
            <div class="alert" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 4px; font-weight: bold; text-align: center;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php else: ?>
            
            <?php if ($error): ?>
                <!-- Error Message -->
                <div class="alert" style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 1rem; font-weight: bold; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Feedback Form -->
            <form method="POST" class="form-grid">
                <input type="text" name="name" placeholder="Your Name" required>
                <textarea name="message" placeholder="Tell us about your experience..." rows="5" required></textarea>
                <button type="submit">Submit Feedback</button>
            </form>
            
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="index.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">← Back to Menu</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>