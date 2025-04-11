<?php
$alertTime = date('F j, Y \a\t g:i A', strtotime($alert['timestamp']));
$location = $alert['location'] ?? 'Unknown Location';
$device = $alert['device'] ?? 'Unknown Device';
$ip = $alert['ip_address'] ?? 'Unknown IP';

// Determine alert type and message
$alertTypes = [
    'login_attempt' => [
        'title' => 'Unusual Login Attempt Detected',
        'icon' => '🚨',
        'color' => '#dc3545'
    ],
    'password_change' => [
        'title' => 'Password Changed from New Device',
        'icon' => '🔑',
        'color' => '#ffc107'
    ],
    'new_device' => [
        'title' => 'New Device Login',
        'icon' => '📱',
        'color' => '#17a2b8'
    ],
    'account_update' => [
        'title' => 'Important Account Changes',
        'icon' => '⚠️',
        'color' => '#fd7e14'
    ]
];

$alertInfo = $alertTypes[$alert['type']] ?? [
    'title' => 'Security Alert',
    'icon' => '⚠️',
    'color' => '#dc3545'
];

$content = <<<HTML
<h1 style="color: {$alertInfo['color']};">{$alertInfo['icon']} {$alertInfo['title']}</h1>

<p>Dear {$user['name']},</p>

<p>We detected potentially suspicious activity on your Airbnb Clone account. Your security is our top priority, so we want to make sure you're aware of this activity.</p>

<div class="info" style="border-left: 4px solid {$alertInfo['color']};">
    <h3>Activity Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Time:</strong></td>
            <td>{$alertTime}</td>
        </tr>
        <tr>
            <td><strong>Location:</strong></td>
            <td>{$location}</td>
        </tr>
        <tr>
            <td><strong>Device:</strong></td>
            <td>{$device}</td>
        </tr>
        <tr>
            <td><strong>IP Address:</strong></td>
            <td>{$ip}</td>
        </tr>
    </table>
</div>

<div class="info" style="margin-top: 20px; background-color: #fff3cd; color: #856404;">
    <h3>Was this you?</h3>
    <p>If you recognize this activity, no further action is needed. However, if you don't recognize this activity, please take the following steps immediately:</p>
</div>

<div style="margin: 30px 0; text-align: center;">
    <a href="<?= SITE_URL ?>/account/security/password/reset" class="button" style="background-color: #dc3545; margin-right: 10px;">
        Reset Password
    </a>
    <a href="<?= SITE_URL ?>/account/security/devices" class="button" style="background-color: #28a745;">
        Review Devices
    </a>
</div>

<div class="info">
    <h3>Recommended Security Steps</h3>
    <ol>
        <li>Change your password immediately</li>
        <li>Enable two-factor authentication if not already enabled</li>
        <li>Review and remove any unrecognized devices</li>
        <li>Check your recent account activity</li>
        <li>Update your recovery email and phone number</li>
    </ol>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Additional Security Tips</h3>
    <ul>
        <li>Use a strong, unique password</li>
        <li>Never share your login credentials</li>
        <li>Be cautious of phishing emails</li>
        <li>Keep your devices and browsers up to date</li>
        <li>Regularly review your account activity</li>
    </ul>
</div>

<div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
    <h3>Need Immediate Assistance?</h3>
    <p>If you believe your account has been compromised, contact our security team immediately:</p>
    <ul>
        <li>Security Hotline: 1-800-SECURE (Available 24/7)</li>
        <li>Email: security@airbnbclone.com</li>
        <li>Live Chat: Available on our security page</li>
    </ul>
</div>

<div style="margin-top: 30px;">
    <p><strong>Account Security Checklist:</strong></p>
    <ul style="list-style-type: none; padding-left: 0;">
        <li style="margin-bottom: 10px;">
            ☐ Review recent <a href="<?= SITE_URL ?>/account/activity">account activity</a>
        </li>
        <li style="margin-bottom: 10px;">
            ☐ Enable <a href="<?= SITE_URL ?>/account/security/2fa">two-factor authentication</a>
        </li>
        <li style="margin-bottom: 10px;">
            ☐ Update <a href="<?= SITE_URL ?>/account/security/recovery">recovery options</a>
        </li>
        <li style="margin-bottom: 10px;">
            ☐ Review <a href="<?= SITE_URL ?>/account/security/connected-apps">connected applications</a>
        </li>
    </ul>
</div>

<p style="margin-top: 30px; font-size: 12px; color: #666;">
    This is an automated security alert. If you need assistance, please contact our security team directly using the information above.
</p>

<div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 4px; font-size: 12px;">
    <p style="margin: 0;">
        <strong>Note:</strong> This email was sent to {$user['email']}. If you received this email by mistake, please ignore it and delete it.
    </p>
</div>
HTML;

include __DIR__ . '/layout.php';
?>
