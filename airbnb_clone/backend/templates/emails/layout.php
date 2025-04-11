<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $subject ?? 'Airbnb Clone' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            background-color: #ff5a5f;
            padding: 20px;
            text-align: center;
            color: white;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .content {
            padding: 20px;
            line-height: 1.6;
        }
        .footer {
            background-color: #f8f8f8;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #ff5a5f;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #ff4146;
        }
        .social-links {
            margin: 20px 0;
        }
        .social-links a {
            margin: 0 10px;
            color: #666;
            text-decoration: none;
        }
        .info {
            background-color: #f8f8f8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .divider {
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="Airbnb Clone" class="logo">
        </div>
        
        <div class="content">
            <?= $content ?? '' ?>
        </div>

        <div class="footer">
            <div class="social-links">
                <a href="<?= SOCIAL_FACEBOOK ?? '#' ?>">Facebook</a>
                <a href="<?= SOCIAL_TWITTER ?? '#' ?>">Twitter</a>
                <a href="<?= SOCIAL_INSTAGRAM ?? '#' ?>">Instagram</a>
            </div>
            <div class="divider"></div>
            <p>© <?= date('Y') ?> Airbnb Clone. All rights reserved.</p>
            <p>You're receiving this email because you have an account with Airbnb Clone.</p>
            <p><small><a href="<?= SITE_URL ?>/unsubscribe?email=<?= $email ?? '' ?>">Unsubscribe</a></small></p>
        </div>
    </div>
</body>
</html>
