<?php
$expiryDate = date('F j, Y', strtotime($promotion['expiry_date']));
$validDays = ceil((strtotime($promotion['expiry_date']) - time()) / (60 * 60 * 24));

// Format discount based on type
$discountText = $promotion['type'] === 'percentage' 
    ? "{$promotion['value']}% OFF" 
    : "$" . number_format($promotion['value'], 2) . " OFF";

$content = <<<HTML
<div style="background-color: {$promotion['theme_color']}; padding: 40px 20px; text-align: center; color: white;">
    <h1 style="font-size: 36px; margin: 0;">{$promotion['title']}</h1>
    <p style="font-size: 24px; margin: 10px 0;">{$promotion['subtitle']}</p>
</div>

<div style="text-align: center; padding: 30px; background-color: #f8f9fa;">
    <div style="font-size: 32px; color: #dc3545; font-weight: bold;">
        {$discountText}
    </div>
    <p style="font-size: 18px; color: #666; margin: 10px 0;">
        Use Code: <strong style="color: #28a745;">{$promotion['code']}</strong>
    </p>
    <p style="color: #dc3545; font-size: 14px;">
        Expires in {$validDays} days - {$expiryDate}
    </p>
</div>

<div style="margin-top: 30px;">
    <h2>Featured Properties</h2>
    <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
HTML;

foreach (array_slice($promotion['featured_properties'], 0, 3) as $property) {
    $price = number_format($property['price_per_night'], 2);
    $discountedPrice = number_format($property['price_per_night'] * (1 - $promotion['value']/100), 2);
    
    $content .= <<<HTML
        <div style="width: 30%; margin-bottom: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <img src="{$property['image_url']}" alt="{$property['title']}" style="width: 100%; height: 150px; object-fit: cover;">
            <div style="padding: 15px;">
                <h3 style="margin: 0 0 10px 0;">{$property['title']}</h3>
                <p style="margin: 0; color: #666;">{$property['location']}</p>
                <div style="margin-top: 10px;">
                    <span style="text-decoration: line-through; color: #666;">${$price}</span>
                    <span style="color: #dc3545; font-weight: bold; margin-left: 10px;">${$discountedPrice}</span>
                    <span style="font-size: 12px; color: #28a745;"> per night</span>
                </div>
            </div>
        </div>
HTML;
}

$content .= <<<HTML
    </div>
</div>

<div style="text-align: center; margin: 40px 0;">
    <a href="<?= SITE_URL ?>/promotions/{$promotion['code']}" class="button" style="font-size: 18px; padding: 15px 30px;">
        Browse All Deals
    </a>
</div>

<div class="info">
    <h3>Why Book Now?</h3>
    <ul style="list-style-type: none; padding-left: 0;">
        <li style="margin-bottom: 10px;">✓ Best prices of the season</li>
        <li style="margin-bottom: 10px;">✓ Flexible cancellation options</li>
        <li style="margin-bottom: 10px;">✓ Wide selection of properties</li>
        <li style="margin-bottom: 10px;">✓ Secure booking process</li>
    </ul>
</div>

<div class="info" style="margin-top: 20px;">
    <h3>How to Redeem</h3>
    <ol>
        <li>Browse our selection of properties</li>
        <li>Select your desired dates and property</li>
        <li>Enter code <strong>{$promotion['code']}</strong> at checkout</li>
        <li>Enjoy your discounted stay!</li>
    </ol>
</div>

<div style="margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 4px;">
    <h3>Terms & Conditions</h3>
    <ul style="font-size: 12px; color: #666;">
HTML;

foreach ($promotion['terms'] as $term) {
    $content .= <<<HTML
        <li>{$term}</li>
HTML;
}

$content .= <<<HTML
    </ul>
</div>

<div style="text-align: center; margin-top: 30px;">
    <p style="font-size: 18px;">Questions about this offer?</p>
    <p>Contact our support team:</p>
    <p>
        Email: promotions@airbnbclone.com<br>
        Phone: 1-800-PROMO
    </p>
</div>

<div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
    <p>
        This offer was sent to {$user['email']}.<br>
        To unsubscribe from promotional emails, <a href="<?= SITE_URL ?>/preferences/email?email={$user['email']}">click here</a>
    </p>
</div>
HTML;

include __DIR__ . '/layout.php';
?>
