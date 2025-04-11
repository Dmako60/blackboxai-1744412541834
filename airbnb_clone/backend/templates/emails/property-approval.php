<?php
$listingDate = date('F j, Y', strtotime($property['created_at']));

$content = <<<HTML
<h1>Property Listing Approved! 🎉</h1>

<p>Dear {$agent['name']},</p>

<p>Great news! Your property listing has been reviewed and approved by our team.</p>

<div class="info">
    <h3>Property Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Property Name:</strong></td>
            <td>{$property['title']}</td>
        </tr>
        <tr>
            <td><strong>Location:</strong></td>
            <td>{$property['location']}</td>
        </tr>
        <tr>
            <td><strong>Listed On:</strong></td>
            <td>{$listingDate}</td>
        </tr>
        <tr>
            <td><strong>Price per Night:</strong></td>
            <td>${number_format($property['price_per_night'], 2)}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td><span style="color: #28a745; text-transform: uppercase;">ACTIVE</span></td>
        </tr>
    </table>
</div>

<div class="info">
    <h3>Next Steps</h3>
    <ul>
        <li>Review your listing details and make any necessary updates</li>
        <li>Set up your calendar availability</li>
        <li>Configure instant booking settings</li>
        <li>Add more photos if desired</li>
        <li>Review and adjust your pricing strategy</li>
    </ul>
</div>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?= SITE_URL ?>/agent/properties/{$property['id']}" class="button">View Your Listing</a>
</p>

<div class="info">
    <h3>Tips for Success</h3>
    <ul>
        <li>Keep your calendar up to date to avoid double bookings</li>
        <li>Respond to inquiries promptly to maintain a good response rate</li>
        <li>Set clear house rules and expectations</li>
        <li>Consider offering special rates for longer stays</li>
        <li>Regularly update your photos and property description</li>
    </ul>
</div>

<div style="margin-top: 30px;">
    <p><strong>Need Help?</strong></p>
    <p>Our host support team is available 24/7 to assist you:</p>
    <ul>
        <li>Email: host-support@airbnbclone.com</li>
        <li>Phone: 1-800-HOST-HELP</li>
        <li>Visit our <a href="<?= SITE_URL ?>/host-resources">Host Resource Center</a></li>
    </ul>
</div>

<p style="margin-top: 30px;">
    Thank you for choosing to host with Airbnb Clone. We're excited to help you succeed!
</p>

<p>Best regards,<br>The Airbnb Clone Team</p>
HTML;

include __DIR__ . '/layout.php';
?>
