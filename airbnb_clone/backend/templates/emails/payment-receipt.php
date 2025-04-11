<?php
$paymentDate = date('F j, Y', strtotime($payment['created_at']));
$amount = number_format($payment['amount'], 2);
$checkIn = date('F j, Y', strtotime($reservation['check_in']));
$checkOut = date('F j, Y', strtotime($reservation['check_out']));

$content = <<<HTML
<h1>Payment Receipt</h1>

<p>Dear {$user['name']},</p>

<p>Thank you for your payment. Here's your receipt for your records:</p>

<div class="info">
    <h3>Payment Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Transaction ID:</strong></td>
            <td>{$payment['transaction_id']}</td>
        </tr>
        <tr>
            <td><strong>Date:</strong></td>
            <td>{$paymentDate}</td>
        </tr>
        <tr>
            <td><strong>Amount:</strong></td>
            <td>${$amount}</td>
        </tr>
        <tr>
            <td><strong>Payment Method:</strong></td>
            <td>{$payment['payment_method']}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td><span style="color: #28a745; text-transform: uppercase;">{$payment['status']}</span></td>
        </tr>
    </table>
</div>

<div class="info">
    <h3>Reservation Details</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td><strong>Booking ID:</strong></td>
            <td>{$reservation['id']}</td>
        </tr>
        <tr>
            <td><strong>Property:</strong></td>
            <td>{$reservation['property_title']}</td>
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
    </table>
</div>

<div class="info">
    <h3>Payment Breakdown</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td>Nightly Rate</td>
            <td>${$reservation['price_per_night']} × {$reservation['nights']} nights</td>
            <td>${number_format($reservation['price_per_night'] * $reservation['nights'], 2)}</td>
        </tr>
        <tr>
            <td>Cleaning Fee</td>
            <td></td>
            <td>${number_format($reservation['cleaning_fee'], 2)}</td>
        </tr>
        <tr>
            <td>Service Fee</td>
            <td></td>
            <td>${number_format($reservation['service_fee'], 2)}</td>
        </tr>
        <tr style="border-top: 1px solid #eee;">
            <td><strong>Total</strong></td>
            <td></td>
            <td><strong>${$amount}</strong></td>
        </tr>
    </table>
</div>

<p><small>A PDF copy of this receipt is attached to this email.</small></p>

<div class="info">
    <h3>Need Help?</h3>
    <p>If you have any questions about this payment or your reservation, our support team is here to help:</p>
    <ul>
        <li>Email: support@airbnbclone.com</li>
        <li>Phone: 1-800-AIRBNB-CLONE</li>
        <li>Support Hours: 24/7</li>
    </ul>
</div>

<a href="<?= SITE_URL ?>/reservations/{$reservation['id']}" class="button">View Reservation Details</a>

<p style="margin-top: 30px;"><small>This is an automated email. Please do not reply to this message.</small></p>

<p>Thank you for choosing Airbnb Clone!</p>
HTML;

include __DIR__ . '/layout.php';
?>
