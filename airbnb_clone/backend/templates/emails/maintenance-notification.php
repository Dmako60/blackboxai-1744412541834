<?php
$startTime = date('g:i A T', strtotime($maintenance['start_time']));
$endTime = date('g:i A T', strtotime($maintenance['end_time']));
$startDate = date('l, F j, Y', strtotime($maintenance['start_time']));
$duration = ceil((strtotime($maintenance['end_time']) - strtotime($maintenance['start_time'])) / 3600);

$impactLevels = [
    'minimal' => [
        'color' => '#28a745',
        'icon' => '✓',
        'text' => 'Minimal Impact'
    ],
    'moderate' => [
        'color' => '#ffc107',
        'icon' => '⚠️',
        'text' => 'Moderate Impact'
    ],
    'significant' => [
        'color' => '#dc3545',
        'icon' => '🚨',
        'text' => 'Significant Impact'
    ]
];

$impact = $impactLevels[$maintenance['impact_level']] ?? $impactLevels['minimal'];

$content = <<<HTML
<div style="text-align: center; padding: 20px;">
    <div style="font-size: 64px; margin-bottom: 20px;">🔧</div>
    <h1>Scheduled Maintenance Notice</h1>
    <p style="font-size: 18px; color: #666;">
        We're performing system maintenance to improve your experience.
    </p>
</div>

<div class="info">
    <h3>Maintenance Schedule</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Date:</strong></td>
            <td>{$startDate}</td>
        </tr>
        <tr>
            <td><strong>Start Time:</strong></td>
            <td>{$startTime}</td>
        </tr>
        <tr>
            <td><strong>End Time:</strong></td>
            <td>{$endTime}</td>
        </tr>
        <tr>
            <td><strong>Duration:</strong></td>
            <td>Approximately {$duration} hour(s)</td>
        </tr>
        <tr>
            <td><strong>Impact Level:</strong></td>
            <td style="color: {$impact['color']}">
                {$impact['icon']} {$impact['text']}
            </td>
        </tr>
    </table>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>What to Expect</h3>
    <ul>
HTML;

foreach ($maintenance['affected_services'] as $service) {
    $content .= <<<HTML
        <li>{$service}</li>
HTML;
}

$content .= <<<HTML
    </ul>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Services That Will Remain Available</h3>
    <ul style="list-style-type: none; padding-left: 0;">
HTML;

foreach ($maintenance['available_services'] as $service) {
    $content .= <<<HTML
        <li style="margin-bottom: 10px;">✓ {$service}</li>
HTML;
}

$content .= <<<HTML
    </ul>
</div>

<div style="margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
    <h3>Recommended Actions</h3>
    <ul>
        <li>Complete any ongoing transactions before the maintenance window</li>
        <li>Save any work in progress</li>
        <li>Plan activities around the maintenance period</li>
        <li>Check system status after maintenance for updates</li>
    </ul>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Why This Maintenance is Important</h3>
    <p>{$maintenance['purpose']}</p>
    <p>These improvements will help us:</p>
    <ul>
HTML;

foreach ($maintenance['benefits'] as $benefit) {
    $content .= <<<HTML
        <li>{$benefit}</li>
HTML;
}

$content .= <<<HTML
    </ul>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= SITE_URL ?>/system-status" class="button">Check System Status</a>
</div>

<div style="margin-top: 30px;">
    <h3>Emergency Support</h3>
    <p>If you require urgent assistance during the maintenance period:</p>
    <ul>
        <li>Emergency Hotline: 1-800-URGENT</li>
        <li>Email: emergency@airbnbclone.com</li>
        <li>Status Updates: <a href="<?= SITE_URL ?>/status">System Status Page</a></li>
    </ul>
</div>

<div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
    <p><strong>After Maintenance</strong></p>
    <ul>
        <li>Clear your browser cache</li>
        <li>Log out and log back in</li>
        <li>Check for any system updates</li>
        <li>Verify your scheduled bookings</li>
    </ul>
</div>

<div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
    <p>
        We apologize for any inconvenience this may cause.<br>
        Thank you for your patience as we work to improve our services.
    </p>
</div>

<div style="margin-top: 20px; font-size: 12px; color: #666;">
    <p>
        This is a system maintenance notification from Airbnb Clone.<br>
        Please do not reply to this message.
    </p>
</div>
HTML;

include __DIR__ . '/layout.php';
?>
