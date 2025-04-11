<?php
$monthYear = date('F Y');
$editionNumber = str_pad($newsletter['edition'], 3, '0', STR_PAD_LEFT);

$content = <<<HTML
<div style="text-align: center; padding: 20px; background-color: {$newsletter['theme_color']}; color: white;">
    <h1 style="margin: 0;">{$newsletter['title']}</h1>
    <p style="margin: 10px 0;">Edition #{$editionNumber} - {$monthYear}</p>
</div>

<div style="text-align: center; padding: 20px;">
    <p style="font-size: 18px; color: #666;">
        {$newsletter['subtitle']}
    </p>
</div>

<!-- Featured Story -->
<div class="info">
    <img src="{$newsletter['featured_image']}" alt="Featured Story" style="width: 100%; height: auto; border-radius: 8px;">
    <h2>{$newsletter['featured_title']}</h2>
    <p>{$newsletter['featured_excerpt']}</p>
    <a href="{$newsletter['featured_link']}" class="button">Read More</a>
</div>

<!-- Latest Updates -->
<div class="info" style="margin-top: 30px;">
    <h2>Latest Updates</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
HTML;

foreach ($newsletter['updates'] as $update) {
    $content .= <<<HTML
        <div style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0;">{$update['title']}</h3>
            <p>{$update['description']}</p>
            <a href="{$update['link']}" style="color: #ff5a5f; text-decoration: none;">Learn more →</a>
        </div>
HTML;
}

$content .= <<<HTML
    </div>
</div>

<!-- Travel Tips -->
<div class="info" style="margin-top: 30px;">
    <h2>Travel Tips & Insights</h2>
HTML;

foreach ($newsletter['tips'] as $tip) {
    $content .= <<<HTML
    <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
        <h3 style="color: #ff5a5f;">💡 {$tip['title']}</h3>
        <p>{$tip['content']}</p>
    </div>
HTML;
}

$content .= <<<HTML
</div>

<!-- Featured Properties -->
<div class="info" style="margin-top: 30px;">
    <h2>Featured Properties</h2>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
HTML;

foreach ($newsletter['featured_properties'] as $property) {
    $price = number_format($property['price_per_night'], 2);
    $content .= <<<HTML
        <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <img src="{$property['image']}" alt="{$property['title']}" style="width: 100%; height: 150px; object-fit: cover;">
            <div style="padding: 15px;">
                <h3 style="margin: 0 0 10px 0;">{$property['title']}</h3>
                <p style="margin: 0; color: #666;">{$property['location']}</p>
                <p style="margin: 10px 0 0 0; color: #ff5a5f; font-weight: bold;">${$price}/night</p>
            </div>
        </div>
HTML;
}

$content .= <<<HTML
    </div>
    <div style="text-align: center; margin-top: 20px;">
        <a href="<?= SITE_URL ?>/properties" class="button">Explore More Properties</a>
    </div>
</div>

<!-- Community Stories -->
<div class="info" style="margin-top: 30px;">
    <h2>Community Stories</h2>
HTML;

foreach ($newsletter['stories'] as $story) {
    $content .= <<<HTML
    <div style="margin-bottom: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
        <div style="display: flex; align-items: center; margin-bottom: 10px;">
            <img src="{$story['author_image']}" alt="{$story['author_name']}" 
                style="width: 50px; height: 50px; border-radius: 25px; margin-right: 15px;">
            <div>
                <h3 style="margin: 0;">{$story['author_name']}</h3>
                <p style="margin: 5px 0 0 0; color: #666;">{$story['author_type']}</p>
            </div>
        </div>
        <p style="font-style: italic;">"{$story['quote']}"</p>
        <a href="{$story['link']}" style="color: #ff5a5f; text-decoration: none;">Read full story →</a>
    </div>
HTML;
}

$content .= <<<HTML
</div>

<!-- Upcoming Events -->
<div class="info" style="margin-top: 30px;">
    <h2>Upcoming Events</h2>
    <table style="width: 100%; border-collapse: collapse;">
HTML;

foreach ($newsletter['events'] as $event) {
    $eventDate = date('F j, Y', strtotime($event['date']));
    $content .= <<<HTML
        <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 15px;">
                <h3 style="margin: 0;">{$event['title']}</h3>
                <p style="margin: 5px 0 0 0; color: #666;">{$eventDate}</p>
            </td>
            <td style="padding: 15px; text-align: right;">
                <a href="{$event['link']}" class="button" style="background-color: #28a745;">Learn More</a>
            </td>
        </tr>
HTML;
}

$content .= <<<HTML
    </table>
</div>

<!-- Social Media -->
<div style="text-align: center; margin: 40px 0;">
    <h3>Connect With Us</h3>
    <div style="margin: 20px 0;">
        <a href="{$social['facebook']}" style="margin: 0 10px; text-decoration: none;">Facebook</a>
        <a href="{$social['twitter']}" style="margin: 0 10px; text-decoration: none;">Twitter</a>
        <a href="{$social['instagram']}" style="margin: 0 10px; text-decoration: none;">Instagram</a>
        <a href="{$social['linkedin']}" style="margin: 0 10px; text-decoration: none;">LinkedIn</a>
    </div>
</div>

<div style="margin-top: 20px; text-align: center; font-size: 12px; color: #666;">
    <p>
        You're receiving this email because you subscribed to our newsletter.<br>
        To update your preferences or unsubscribe, <a href="<?= SITE_URL ?>/preferences/email?email={$user['email']}">click here</a>
    </p>
</div>
HTML;

include __DIR__ . '/layout.php';
?>
