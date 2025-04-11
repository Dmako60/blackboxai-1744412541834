# Airbnb Clone

A full-stack Airbnb clone built with PHP backend and Flutter frontend.

## Features

### Admin Panel
- Dashboard with statistics
- Agent management (approve/reject/delete)
- Property management
- Booking overview
- Payment tracking

### Agent Features
- Property management
- Booking management
- Subscription plans (Base/Gold/VIP)
- Payment processing
- Analytics dashboard

### User Features
- Property browsing and search
- Booking management
- Payment processing
- Review system

## Setup Instructions

### Backend Setup

1. Configure web server to point to the `backend` directory
2. Create MySQL database and import schema:
```sql
mysql -u your_username -p your_database_name < backend/setup_database.sql
```
3. Update database configuration in `backend/config/Database.php`
4. Ensure `uploads` directory has write permissions

### Frontend Setup

1. Install Flutter dependencies:
```bash
cd frontend
flutter pub get
```
2. Update API endpoint in `lib/utils/constants.dart`
3. Run the application:
```bash
flutter run
```

## Subscription Plans

### Base Plan (Free)
- Up to 4 property listings
- Basic support
- Standard visibility

### Gold Plan ($29.99/month)
- Up to 10 property listings
- Priority support
- Featured listings
- Analytics dashboard

### VIP Plan ($99.99/month)
- Unlimited property listings
- 24/7 premium support
- Featured listings
- Advanced analytics
- Marketing tools

## API Endpoints

### Authentication
- POST `/auth/login` - User login
- POST `/auth/register` - User registration
- POST `/admin/login` - Admin login
- POST `/agents/login` - Agent login

### Properties
- GET `/properties/list` - List properties
- GET `/properties/view/{id}` - View property details
- POST `/properties/add` - Add new property
- PUT `/properties/update` - Update property
- DELETE `/properties/delete` - Delete property

### Reservations
- POST `/reservations/create` - Create reservation
- GET `/reservations/list` - List reservations
- PUT `/reservations/update` - Update reservation
- POST `/reservations/cancel` - Cancel reservation

### Payments
- POST `/payments/process` - Process payment
- GET `/payments/history` - Payment history
- POST `/payments/refund` - Process refund
