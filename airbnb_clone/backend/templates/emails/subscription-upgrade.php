<?php
$startDate = date('F j, Y', strtotime($subscription['start_date']));
$endDate = date('F j, Y', strtotime($subscription['end_date']));
$amount = number_format($subscription['amount'], 2);

// Get plan features based on subscription type
$planFeatures = [
    'base' => [
        'Up to 4 property listings',
        'Basic support',
        'Standard visibility',
        'Basic analytics'
    ],
    'gold' => [
        'Up to 10 property listings',
        'Priority support',
        'Featured listings',
        'Advanced analytics dashboard',
        'Professional photography tips',
        'Marketing tools'
    ],
    'vip' => [
        'Unlimited property listings',
        '24/7 premium support',
        'Featured listings with priority placement',
        'Advanced analytics with market insights',
        'Professional photography service credit',
        'Marketing tools and consultation',
        'Dedicated account manager',
        'Early access to new features'
    ]
];

$features = $planFeatures[$subscription['type']] ?? $planFeatures['base'];

$content = <<<HTML
<h1>Subscription Upgrade Confirmed! 🌟</h1>

<p>Dear {$agent['name']},</p>

<p>Thank you for upgrading your subscription to the <strong style="color: #ff5a5f; text-transform: uppercase;">{$subscription['type']}</strong> plan!</p>

<div class="info">
    <h3>Subscription Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Plan:</strong></td>
            <td style="text-transform: capitalize;">{$subscription['type']} Plan</td>
        </tr>
        <tr>
            <td><strong>Start Date:</strong></td>
            <td>{$startDate}</td>
        </tr>
        <tr>
            <td><strong>Renewal Date:</strong></td>
            <td>{$endDate}</td>
        </tr>
        <tr>
            <td><strong>Amount:</strong></td>
            <td>${$amount}/month</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td><span style="color: #28a745; text-transform: uppercase;">Active</span></td>
        </tr>
    </table>
</div>

<div class="info">
    <h3>Your New Benefits</h3>
    <ul style="list-style-type: none; padding-left: 0;">
HTML;

foreach ($features as $feature) {
    $content .= <<<HTML
        <li style="margin-bottom: 10px;">✓ {$feature}</li>
HTML;
}

$content .= <<<HTML
    </ul>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= SITE_URL ?>/agent/dashboard" class="button">Go to Dashboard</a>
</div>

<div class="info">
    <h3>Getting Started</h3>
    <p>Here's how to make the most of your upgraded subscription:</p>
    <ol>
        <li>Review your current listings and optimize them</li>
        <li>Add new properties to your portfolio</li>
        <li>Explore the new analytics dashboard</li>
        <li>Check out the marketing tools available to you</li>
        <li>Connect with your support team</li>
    </ol>
</div>

<div style="margin-top: 30px;">
    <h3>Need Assistance?</h3>
    <p>Your dedicated support team is here to help you succeed:</p>
    <ul>
        <li>Email: premium-support@airbnbclone.com</li>
        <li>Priority Phone: 1-800-VIP-HOST</li>
        <li>Support Hours: 24/7</li>
    </ul>
</div>

<div class="info" style="margin-top: 30px;">
    <h3>Automatic Renewal Notice</h3>
    <p><small>Your subscription will automatically renew on {$endDate} at the current rate of ${$amount}/month. 
    You can manage your subscription settings anytime from your dashboard.</small></p>
</div>

<p style="margin-top: 30px;">
    Thank you for your continued trust in Airbnb Clone. We're excited to help you grow your hosting business!
</p>

<p>Best regards,<br>The Airbnb Clone Team</p>
HTML;

include __DIR__ . '/layout.php';
?>
