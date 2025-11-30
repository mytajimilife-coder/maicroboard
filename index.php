<?php
require_once 'config.php';
// requireLogin(); // Landing page is public

$page_title = "Welcome";
require_once 'inc/header.php';
?>
<link rel="stylesheet" href="skin/default/style.css">

<div class="landing-hero">
    <div class="hero-content">
        <h1>Welcome to MicroBoard</h1>
        <p>A simple, clean, and efficient bulletin board system for your community.</p>
        
        <div class="hero-actions">
            <?php if (isLoggedIn()): ?>
                <a href="list.php" class="btn btn-large">Go to Board</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-large">Login</a>
                <a href="register.php" class="btn btn-large btn-outline">Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>
