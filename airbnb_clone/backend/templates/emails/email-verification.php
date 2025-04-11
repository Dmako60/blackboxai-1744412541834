<?php
$verificationLink = SITE_URL . '/verify-email?token=' . $verification['token'];
$expiryTime = date('g:i A', strtotime($verification['expiry']));
$expiryDate = date('F j, Y', strtotime($verification['expiry']));

$content = <<<HTML
<div style="text-align: center; padding: 20px;">
    <div style="font-size: 64px; margin-bottom: 20px;">✉️</div>
    <h1>Verify Your Email Address</h1>
    <p style="font-size: 18px; color: #666;">
        You're almost there! Just one click away from accessing your Airbnb Clone account.
    </p>
</div>

<div style="text-align: center; margin: 40px 0;">
    <a href="{$verificationLink}" class="button" style="font-size: 18px; padding: 15px 30px; background-color: #28a745;">
        Verify Email Address
    </a>
</div>

<div class="info" style="text-align: center;">
    <p>Button not working? Copy and paste this link into your browser:</p>
    <p style="background-color: #f8f9fa; padding: 10px; border-radius: 4px; word-break: break-all;">
        <small>{$verificationLink}</small>
    </p>
</div>

<div style="margin: 30px 0; padding: 20px; background-color: #fff3cd; border-radius: 4px;">
    <p style="color: #856404; margin: 0;">
        <strong>Note:</strong> This verification link will expire at {$expiryTime} on {$expiryDate}.
    </p>
</div>

<div class="info">
    <h3>What happens next?</h3>
    <ol>
        <li>Click the verification button above</li>
        <li>You'll be redirected to our website</li>
        <li>Your email will be verified automatically</li>
        <li>You can start using all features of your account</li>
    </ol>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Why verify your email?</h3>
    <ul style="list-style-type: none; padding-left: 0;">
        <li style="margin-bottom: 10px;">✓ Secure your account</li>
        <li style="margin-bottom: 10px;">✓ Receive important notifications</li>
        <li style="margin-bottom: 10px;">✓ Access all platform features</li>
        <li style="margin-bottom: 10px;">✓ Get booking confirmations</li>
    </ul>
</div>

<div style="margin-top: 30px;">
    <h3>Need Help?</h3>
    <p>If you're having trouble verifying your email, try these steps:</p>
    <ul>
        <li>Check if the verification link has expired</li>
        <li>Make sure you're using the latest verification email</li>
        <li>Check your internet connection</li>
        <li>Try copying and pasting the link directly into your browser</li>
    </ul>
</div>

<div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
    <p><strong>Still having issues?</strong></p>
    <p>Our support team is here to help:</p>
    <ul>
        <li>Email: support@airbnbclone.com</li>
        <li>Phone: 1-800-AIRBNB-CLONE</li>
        <li>Live Chat: Available on our website</li>
    </ul>
</div>

<div style="margin-top: 30px;">
    <p><strong>Didn't request this verification?</strong></p>
    <p>
        If you didn't create an account on Airbnb Clone, you can safely ignore this email. 
        Someone might have entered your email address by mistake.
    </p>
</div>

<div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
    <p>
        This is an automated email from Airbnb Clone.<br>
        Please do not reply to this message.
    </p>
</div>
HTML;

include __DIR__ . '/layout.php';
?>
