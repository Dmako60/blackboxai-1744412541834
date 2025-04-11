<?php
$deletionDate = date('F j, Y \a\t g:i A T', strtotime($deletion['scheduled_time']));
$recoveryDeadline = date('F j, Y \a\t g:i A T', strtotime($deletion['scheduled_time'] . ' +30 days'));
$accountType = ucfirst($user['type']); // guest or agent

$content = <<<HTML
<div style="text-align: center; padding: 20px;">
    <div style="font-size: 64px; margin-bottom: 20px;">👋</div>
    <h1>Account Deletion Confirmation</h1>
    <p style="font-size: 18px; color: #666;">
        We're sorry to see you go.
    </p>
</div>

<div class="info">
    <h3>Deletion Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Account:</strong></td>
            <td>{$user['email']}</td>
        </tr>
        <tr>
            <td><strong>Account Type:</strong></td>
            <td>{$accountType}</td>
        </tr>
        <tr>
            <td><strong>Scheduled Deletion:</strong></td>
            <td>{$deletionDate}</td>
        </tr>
        <tr>
            <td><strong>Recovery Deadline:</strong></td>
            <td style="color: #dc3545;">{$recoveryDeadline}</td>
        </tr>
    </table>
</div>

<div style="margin: 30px 0; padding: 20px; background-color: #fff3cd; border-radius: 4px;">
    <h3 style="color: #856404; margin-top: 0;">Important Information</h3>
    <p style="margin-bottom: 0;">
        Your account will be permanently deleted on {$deletionDate}. 
        You have until {$recoveryDeadline} to recover your account if you change your mind.
    </p>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= SITE_URL ?>/account/recover" class="button" style="background-color: #28a745; margin-right: 10px;">
        Recover My Account
    </a>
    <a href="<?= SITE_URL ?>/feedback" class="button" style="background-color: #6c757d;">
        Share Feedback
    </a>
</div>

<div class="info">
    <h3>What Happens Next</h3>
    <ul>
HTML;

if ($user['type'] === 'agent') {
    $content .= <<<HTML
        <li>All your property listings will be removed</li>
        <li>Pending reservations will be cancelled</li>
        <li>Active reservations will be completed as scheduled</li>
        <li>Your payout information will be retained for 90 days</li>
HTML;
} else {
    $content .= <<<HTML
        <li>Your upcoming reservations will be cancelled</li>
        <li>Active reservations will be completed as scheduled</li>
        <li>Your reviews will be anonymized</li>
        <li>Your payment information will be removed</li>
HTML;
}

$content .= <<<HTML
        <li>Your personal information will be permanently deleted</li>
        <li>Your data will be removed from our systems</li>
    </ul>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Data Retention</h3>
    <p>Some information may be retained for:</p>
    <ul>
        <li>Legal compliance purposes</li>
        <li>Financial record keeping</li>
        <li>Fraud prevention</li>
        <li>Platform security</li>
    </ul>
    <p>For more details, please review our <a href="<?= SITE_URL ?>/privacy">Privacy Policy</a>.</p>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Before You Go</h3>
    <ul>
        <li>Download your data and booking history</li>
        <li>Save any important messages or receipts</li>
        <li>Complete any pending transactions</li>
        <li>Remove any saved payment methods</li>
    </ul>
</div>

<div style="margin: 30px 0; text-align: center;">
    <a href="<?= SITE_URL ?>/account/download-data" class="button">
        Download My Data
    </a>
</div>

<div style="margin-top: 30px;">
    <h3>Need Help?</h3>
    <p>If you have any questions or need assistance:</p>
    <ul>
        <li>Email: support@airbnbclone.com</li>
        <li>Phone: 1-800-AIRBNB-CLONE</li>
        <li>Help Center: <a href="<?= SITE_URL ?>/help">Visit Help Center</a></li>
    </ul>
</div>

<div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
    <h3>Want to Come Back?</h3>
    <p>
        You can create a new account at any time after your current account is deleted. 
        However, your previous history, reviews, and preferences won't be recoverable.
    </p>
</div>

<div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
    <p>
        This is an automated message confirming your account deletion request.<br>
        If you didn't request this deletion, please contact our support team immediately.
    </p>
</div>
HTML;

include __DIR__ . '/layout.php';
?>
