<?php
$checkOut = date('F j, Y', strtotime($reservation['check_out']));
$reviewDeadline = date('F j, Y', strtotime($reservation['check_out'] . ' +14 days'));
$locationParam = urlencode($property['location']);

$content = <<<HTML
<h1>How was your stay? 🏠</h1>

<p>Dear {$user['name']},</p>

<p>We hope you had a wonderful stay at {$property['title']}! Your experience can help other travelers make informed decisions.</p>

<div class="info">
    <h3>Your Recent Stay</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Property:</strong></td>
            <td>{$property['title']}</td>
        </tr>
        <tr>
            <td><strong>Location:</strong></td>
            <td>{$property['location']}</td>
        </tr>
        <tr>
            <td><strong>Host:</strong></td>
            <td>{$agent['name']}</td>
        </tr>
        <tr>
            <td><strong>Check-out Date:</strong></td>
            <td>{$checkOut}</td>
        </tr>
    </table>
</div>

<div style="text-align: center; margin: 30px 0;">
    <p style="font-size: 18px; margin-bottom: 20px;">Ready to share your experience?</p>
    <a href="<?= SITE_URL ?>/reservations/{$reservation['id']}/review" class="button">Write a Review</a>
</div>

<div class="info">
    <h3>Review Guidelines</h3>
    <ul>
        <li>Be honest and detailed about your experience</li>
        <li>Include specific examples of what you liked or didn't like</li>
        <li>Keep it respectful and constructive</li>
        <li>Consider mentioning:
            <ul>
                <li>Cleanliness and accuracy of listing</li>
                <li>Communication with the host</li>
                <li>Location and neighborhood</li>
                <li>Value for money</li>
                <li>Overall experience</li>
            </ul>
        </li>
    </ul>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>Why Your Review Matters</h3>
    <ul style="list-style-type: none; padding-left: 0;">
        <li>✓ Helps other travelers make informed decisions</li>
        <li>✓ Provides valuable feedback to your host</li>
        <li>✓ Contributes to our community's trust and transparency</li>
        <li>✓ Improves the overall experience for everyone</li>
    </ul>
</div>

<div style="margin-top: 30px; background-color: #fff3cd; padding: 15px; border-radius: 4px;">
    <p style="color: #856404; margin: 0;">
        <strong>Note:</strong> You have until {$reviewDeadline} to submit your review. 
        After this date, the review period will close.
    </p>
</div>

<div style="margin-top: 30px;">
    <p><strong>Had an issue during your stay?</strong></p>
    <p>If you experienced any problems that need immediate attention, please contact our support team:</p>
    <ul>
        <li>Email: support@airbnbclone.com</li>
        <li>Phone: 1-800-AIRBNB-CLONE</li>
        <li>24/7 Support Available</li>
    </ul>
</div>

<div style="margin-top: 30px;">
    <p>Planning another trip?</p>
    <p style="margin-bottom: 20px;">Explore more amazing properties in {$property['location']} or discover new destinations:</p>
    <div style="text-align: center;">
        <a href="<?= SITE_URL ?>/properties?location={$locationParam}" class="button" style="margin-right: 10px;">
            Explore {$property['location']}
        </a>
        <a href="<?= SITE_URL ?>/properties" class="button" style="background-color: #28a745;">
            Browse All Properties
        </a>
    </div>
</div>

<p style="margin-top: 30px;">
    Thank you for choosing Airbnb Clone for your stay. We look forward to hosting you again!
</p>

<p>Best regards,<br>The Airbnb Clone Team</p>
HTML;

include __DIR__ . '/layout.php';
?>
