# Airbnb Clone Installation Guide

This guide provides step-by-step instructions for setting up the Airbnb Clone application on both local development environment and cPanel hosting.

## Table of Contents
- [Prerequisites](#prerequisites)
- [Local Development Setup](#local-development-setup)
- [cPanel Hosting Setup](#cpanel-hosting-setup)
- [Configuration](#configuration)
- [Troubleshooting](#troubleshooting)

## Prerequisites

### Backend Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer
- Apache/Nginx web server
- PHP Extensions:
  - PDO PHP Extension
  - OpenSSL PHP Extension
  - Mbstring PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - JSON PHP Extension
  - FileInfo Extension

### Frontend Requirements
- Flutter SDK 3.0 or higher
- Dart SDK 2.17 or higher
- Android Studio / VS Code
- Node.js 14.x or higher (for development tools)

## Local Development Setup

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/airbnb_clone.git
cd airbnb_clone
```

### 2. Backend Setup
```bash
# Navigate to backend directory
cd backend

# Install PHP dependencies
composer install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database
mysql -u root -p
CREATE DATABASE airbnb_clone;
exit;

# Run database migrations and seeders
php artisan migrate --seed

# Set proper permissions
chmod -R 775 storage bootstrap/cache

# Start development server
php artisan serve
```

### 3. Frontend Setup
```bash
# Navigate to frontend directory
cd frontend

# Get Flutter dependencies
flutter pub get

# Run the app in development mode
flutter run
```

## cPanel Hosting Setup

### 1. Database Setup
1. Log in to cPanel
2. Navigate to "MySQL Databases"
3. Create a new database (e.g., `username_airbnb`)
4. Create a database user
5. Add user to database with all privileges
6. Note down the database name, username, and password

### 2. Upload Files
1. Navigate to "File Manager" in cPanel
2. Create a new directory (e.g., `airbnb`)
3. Upload backend files to this directory
4. Set file permissions:
   ```
   public_html/airbnb -> 755
   public_html/airbnb/storage -> 775
   public_html/airbnb/.env -> 644
   ```

### 3. Backend Configuration
1. Create/edit `.env` file:
   ```env
   APP_NAME="Airbnb Clone"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_username
   DB_PASSWORD=your_database_password
   
   MAIL_MAILER=smtp
   MAIL_HOST=your-mail-server
   MAIL_PORT=587
   MAIL_USERNAME=your-email
   MAIL_PASSWORD=your-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@your-domain.com
   
   JWT_SECRET=your-jwt-secret
   ```

2. Install Dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. Set up the database:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

4. Configure Apache/PHP:
   - Create/edit `.htaccess`:
     ```apache
     <IfModule mod_rewrite.c>
         RewriteEngine On
         RewriteBase /
         RewriteRule ^index\.php$ - [L]
         RewriteCond %{REQUEST_FILENAME} !-f
         RewriteCond %{REQUEST_FILENAME} !-d
         RewriteRule . /index.php [L]
     </IfModule>
     ```

### 4. Frontend Deployment
1. Build the Flutter web app:
   ```bash
   cd frontend
   flutter build web --release
   ```

2. Upload the build:
   - Upload contents of `build/web` to `public_html/airbnb/public`

## Configuration

### Security Settings
1. Enable SSL/HTTPS in cPanel
2. Configure secure headers in Apache/PHP
3. Set up CORS policies
4. Enable rate limiting
5. Configure firewall rules

### Email Configuration
1. Set up email accounts in cPanel
2. Configure SMTP settings in `.env`
3. Test email functionality

### File Upload Settings
1. Configure PHP upload limits in php.ini
2. Set appropriate permissions for upload directories
3. Configure backup settings

## Troubleshooting

### Common Issues and Solutions

1. **500 Internal Server Error**
   - Check error logs in cPanel
   - Verify .env file configuration
   - Check file permissions
   - Validate database connection

2. **Database Connection Issues**
   - Verify database credentials
   - Check database user privileges
   - Ensure proper database host configuration

3. **Email Not Working**
   - Verify SMTP settings
   - Check email quota
   - Review email logs

4. **Upload Issues**
   - Check PHP memory limit
   - Verify directory permissions
   - Review upload_max_filesize setting

### Debug Mode
To enable debug mode temporarily:
1. Set `APP_DEBUG=true` in `.env`
2. Check `storage/logs/laravel.log`
3. Remember to disable debug mode in production

### Support Resources
- [Flutter Documentation](https://flutter.dev/docs)
- [PHP Documentation](https://www.php.net/docs.php)
- [cPanel Documentation](https://docs.cpanel.net/)
- Project Issues: [GitHub Issues](https://github.com/yourusername/airbnb_clone/issues)

## Security Notes
- Always keep debug mode disabled in production
- Regularly update dependencies
- Use strong passwords
- Enable SSL/HTTPS
- Implement rate limiting
- Regular security audits
- Backup data regularly
