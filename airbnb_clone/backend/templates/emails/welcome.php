<?php
$content = <<<HTML
<h1>Welcome to Airbnb Clone, {$name}!</h1>

<p>We're excited to have you join our community of travelers and hosts from around the world.</p>

<div class="info">
    <h3>What you can do now:</h3>
    <ul>
        <li>Complete your profile</li>
        <li>Browse amazing properties</li>
        <li>Plan your first trip</li>
        <li>List your property</li>
    </ul>
</div>

<p>Ready to start exploring?</p>

<a href="<?= SITE_URL ?>/browse" class="button">Start Exploring</a>

<p>If you have any questions, feel free to contact our support team at <a href="mailto:support@airbnbclone.com">support@airbnbclone.com</a></p>

<p>Best regards,<br>The Airbnb Clone Team</p>
HTML;

include __DIR__ . '/layout.php';
?>
