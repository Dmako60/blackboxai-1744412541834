<?php
$monthYear = date('F Y', strtotime($report['period']));
$totalRevenue = number_format($report['total_revenue'], 2);
$occupancyRate = number_format($report['occupancy_rate'], 1);
$avgRating = number_format($report['average_rating'], 1);

// Pre-calculate conditional colors and arrows
$revenueColor = $report['revenue_change'] >= 0 ? '#28a745' : '#dc3545';
$revenueArrow = $report['revenue_change'] >= 0 ? '↑' : '↓';
$bookingsColor = $report['bookings_change'] >= 0 ? '#28a745' : '#dc3545';
$bookingsArrow = $report['bookings_change'] >= 0 ? '↑' : '↓';
$occupancyColor = $report['occupancy_change'] >= 0 ? '#28a745' : '#dc3545';
$occupancyArrow = $report['occupancy_change'] >= 0 ? '↑' : '↓';

$content = <<<HTML
<h1>Monthly Performance Report - {$monthYear}</h1>

<p>Dear {$agent['name']},</p>

<p>Here's your property performance summary for {$monthYear}. Use these insights to optimize your listings and boost your earnings.</p>

<div class="info">
    <h3>Key Performance Metrics</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="text-align: center; padding: 15px;">
                <div style="font-size: 24px; color: #28a745;">
                    ${$totalRevenue}
                </div>
                <div style="color: #666; font-size: 14px;">Total Revenue</div>
            </td>
            <td style="text-align: center; padding: 15px;">
                <div style="font-size: 24px; color: #17a2b8;">
                    {$report['total_bookings']}
                </div>
                <div style="color: #666; font-size: 14px;">Total Bookings</div>
            </td>
            <td style="text-align: center; padding: 15px;">
                <div style="font-size: 24px; color: #ffc107;">
                    {$occupancyRate}%
                </div>
                <div style="color: #666; font-size: 14px;">Occupancy Rate</div>
            </td>
            <td style="text-align: center; padding: 15px;">
                <div style="font-size: 24px; color: #dc3545;">
                    {$avgRating}★
                </div>
                <div style="color: #666; font-size: 14px;">Average Rating</div>
            </td>
        </tr>
    </table>
</div>

<div class="info">
    <h3>Property Performance</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="background-color: #f8f9fa;">
            <th style="padding: 10px; text-align: left;">Property</th>
            <th style="padding: 10px; text-align: right;">Revenue</th>
            <th style="padding: 10px; text-align: right;">Bookings</th>
            <th style="padding: 10px; text-align: right;">Occupancy</th>
            <th style="padding: 10px; text-align: right;">Rating</th>
        </tr>
HTML;

foreach ($report['properties'] as $property) {
    $propertyRevenue = number_format($property['revenue'], 2);
    $propertyOccupancy = number_format($property['occupancy_rate'], 1);
    $propertyRating = number_format($property['rating'], 1);
    
    $content .= <<<HTML
        <tr style="border-bottom: 1px solid #dee2e6;">
            <td style="padding: 10px;">{$property['title']}</td>
            <td style="padding: 10px; text-align: right;">${$propertyRevenue}</td>
            <td style="padding: 10px; text-align: right;">{$property['bookings']}</td>
            <td style="padding: 10px; text-align: right;">{$propertyOccupancy}%</td>
            <td style="padding: 10px; text-align: right;">{$propertyRating}★</td>
        </tr>
HTML;
}

$content .= <<<HTML
    </table>
</div>

<div class="info">
    <h3>Month-over-Month Changes</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Revenue:</strong></td>
            <td style="color: {$revenueColor}">
                {$report['revenue_change']}% {$revenueArrow}
            </td>
        </tr>
        <tr>
            <td><strong>Bookings:</strong></td>
            <td style="color: {$bookingsColor}">
                {$report['bookings_change']}% {$bookingsArrow}
            </td>
        </tr>
        <tr>
            <td><strong>Occupancy:</strong></td>
            <td style="color: {$occupancyColor}">
                {$report['occupancy_change']}% {$occupancyArrow}
            </td>
        </tr>
    </table>
</div>

<div class="info">
    <h3>Top Guest Reviews</h3>
HTML;

foreach (array_slice($report['reviews'], 0, 3) as $review) {
    $reviewDate = date('M j, Y', strtotime($review['created_at']));
    $content .= <<<HTML
    <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #dee2e6;">
        <p style="margin: 0;">"{$review['comment']}"</p>
        <p style="margin: 5px 0 0; color: #666; font-size: 14px;">
            {$review['rating']}★ - For {$review['property_title']} - {$reviewDate}
        </p>
    </div>
HTML;
}

$content .= <<<HTML
</div>

<div class="info">
    <h3>Recommendations</h3>
    <ul>
HTML;

foreach ($report['recommendations'] as $recommendation) {
    $content .= <<<HTML
        <li>{$recommendation}</li>
HTML;
}

$content .= <<<HTML
    </ul>
</div>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= SITE_URL ?>/agent/analytics" class="button">View Detailed Analytics</a>
</div>

<div style="margin-top: 30px;">
    <h3>Need Help Optimizing Your Listings?</h3>
    <p>Our host success team is here to help you maximize your earnings:</p>
    <ul>
        <li>Schedule a <a href="<?= SITE_URL ?>/agent/consultation">free consultation</a></li>
        <li>Visit our <a href="<?= SITE_URL ?>/host-resources">Host Resource Center</a></li>
        <li>Contact host support: host-support@airbnbclone.com</li>
    </ul>
</div>

<p style="margin-top: 30px;">
    Here's to your continued success!
</p>

<p>Best regards,<br>The Airbnb Clone Team</p>

<div style="margin-top: 20px; font-size: 12px; color: #666;">
    <p>This report was generated on {$report['generated_at']} and includes data from {$report['period_start']} to {$report['period_end']}.</p>
</div>
HTML;

include __DIR__ . '/layout.php';
?>
