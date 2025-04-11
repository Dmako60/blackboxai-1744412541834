<?php
$checkIn = date('F j, Y', strtotime($reservation['check_in']));
$checkOut = date('F j, Y', strtotime($reservation['check_out']));
$cancellationDate = date('F j, Y', strtotime($reservation['cancelled_at']));
$refundAmount = number_format($reservation['refund_amount'], 2);
$isGuest = isset($is_guest) && $is_guest;
$userType = $isGuest ? 'travel' : 'hosting';

$content = <<<HTML
<h1>Booking Cancellation Notice</h1>

<p>Dear {$recipient['name']},</p>

<p>This email confirms that the following reservation has been cancelled:</p>

<div class="info">
    <h3>Reservation Details</h3>
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
            <td><strong>Check-in:</strong></td>
            <td>{$checkIn}</td>
        </tr>
        <tr>
            <td><strong>Check-out:</strong></td>
            <td>{$checkOut}</td>
        </tr>
        <tr>
            <td><strong>Guests:</strong></td>
            <td>{$reservation['guests_count']}</td>
        </tr>
        <tr>
            <td><strong>Cancellation Date:</strong></td>
            <td>{$cancellationDate}</td>
        </tr>
    </table>
</div>

HTML;

if ($isGuest) {
    // Content specific to guest
    $content .= <<<HTML
<div class="info">
    <h3>Refund Information</h3>
    <p>Based on the cancellation policy and timing, here's your refund breakdown:</p>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Refund Amount:</strong></td>
            <td>${$refundAmount}</td>
        </tr>
        <tr>
            <td><strong>Refund Method:</strong></td>
            <td>{$payment['payment_method']}</td>
        </tr>
        <tr>
            <td><strong>Expected Processing Time:</strong></td>
            <td>3-5 business days</td>
        </tr>
    </table>
    <p><small>The refund will be processed to your original payment method.</small></p>
</div>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?= SITE_URL ?>/properties" class="button">Browse Other Properties</a>
</p>
HTML;
} else {
    // Content specific to host
    $content .= <<<HTML
<div class="info">
    <h3>Next Steps</h3>
    <ul>
        <li>Your calendar has been automatically updated</li>
        <li>The dates are now available for new bookings</li>
        <li>Consider adjusting your pricing for these dates</li>
        <li>Review your cancellation policy if needed</li>
    </ul>
</div>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?= SITE_URL ?>/agent/calendar/{$property['id']}" class="button">Update Calendar</a>
</p>
HTML;
}

$content .= <<<HTML
<div class="info">
    <h3>Cancellation Policy</h3>
    <p>{$property['cancellation_policy']}</p>
</div>

<div style="margin-top: 30px;">
    <p><strong>Questions about this cancellation?</strong></p>
    <p>Our support team is here to help:</p>
    <ul>
        <li>Email: support@airbnbclone.com</li>
        <li>Phone: 1-800-AIRBNB-CLONE</li>
        <li>Support Hours: 24/7</li>
    </ul>
</div>

<p style="margin-top: 30px;">
    We hope to assist you with your {$userType} needs again soon.
</p>

<p>Best regards,<br>The Airbnb Clone Team</p>
HTML;

include __DIR__ . '/layout.php';
?>
