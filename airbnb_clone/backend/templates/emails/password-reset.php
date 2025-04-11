<?php
$content = <<<HTML
<h1>Password Reset Request</h1>

<p>Dear {$name},</p>

<p>We received a request to reset your password. If you didn't make this request, you can safely ignore this email.</p>

<div class="info">
    <h3>Security Notice</h3>
    <p>This password reset link will expire in 1 hour for your security.</p>
    <p>If you didn't request a password reset, please:</p>
    <ul>
        <li>Keep your current password</li>
        <li>Review your account security</li>
        <li>Enable two-factor authentication if not already enabled</li>
    </ul>
</div>

<p style="text-align: center; margin: 30px 0;">
    <a href="{$reset_link}" class="button">Reset Password</a>
</p>

<div class="info">
    <p><strong>Having trouble with the button?</strong></p>
    <p>Copy and paste this link into your browser:</p>
    <p style="word-break: break-all;"><small>{$reset_link}</small></p>
</div>

<div style="margin-top: 30px;">
    <p><strong>Additional Security Tips:</strong></p>
    <ul>
        <li>Choose a strong password that you haven't used before</li>
        <li>Use a combination of letters, numbers, and special characters</li>
        <li>Never share your password with anyone</li>
        <li>Enable two-factor authentication for extra security</li>
    </ul>
</div>

<p style="margin-top: 30px;">
    If you need help or didn't request this reset, please contact our security team at 
    <a href="mailto:security@airbnbclone.com">security@airbnbclone.com</a>
</p>

<div class="info" style="margin-top: 30px;">
    <p><small>This email was sent to {$email}. If you received this email by mistake, please delete it.</small></p>
</div>

<p>Best regards,<br>The Airbnb Clone Security Team</p>
HTML;

include __DIR__ . '/layout.php';
?>
