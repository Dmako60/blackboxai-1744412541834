<?php
$checkIn = date('F j, Y', strtotime($reservation['check_in']));
$checkOut = date('F j, Y', strtotime($reservation['check_out']));
$total = number_format($reservation['total_price'], 2);

$content = <<<HTML
<h1>Reservation Confirmed!</h1>

<p>Dear {$user['name']},</p>

<p>Your reservation has been confirmed. Here are the details of your stay:</p>

<div class="info">
    <h3>{$property['title']}</h3>
    <p><strong>Location:</strong> {$property['location']}</p>
    <p><strong>Check-in:</strong> {$checkIn}</p>
    <p><strong>Check-out:</strong> {$checkOut}</p>
    <p><strong>Guests:</strong> {$reservation['guests_count']}</p>
    <p><strong>Total Amount:</strong> ${$total}</p>
</div>

<h3>Important Information:</h3>
<ul>
    <li>Check-in time: After 3:00 PM</li>
    <li>Check-out time: Before 11:00 AM</li>
    <li>Property contact: {$property['contact_phone']}</li>
</ul>

<div class="info">
    <h3>Property Rules:</h3>
    <p>{$property['rules']}</p>
</div>

<p>Need to modify your reservation?</p>
<a href="<?= SITE_URL ?>/reservations/{$reservation['id']}" class="button">Manage Booking</a>

<p><small>Please review our cancellation policy and house rules before your stay.</small></p>

<p>Have a great stay!<br>The Airbnb Clone Team</p>
HTML;

include __DIR__ . '/layout.php';
?>
