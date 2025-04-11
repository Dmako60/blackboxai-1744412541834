<?php
require_once __DIR__ . '/../controllers/PaymentController.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/Response.php';

$payment = new PaymentController();
$jwt = new JWTHandler();

// Get the endpoint from URI segments
$endpoint = isset($uri_segments[1]) ? $uri_segments[1] : '';

// All payment endpoints require authentication
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    Response::unauthorized('Authorization token required');
}

try {
    $token_data = $jwt->getTokenData($_SERVER['HTTP_AUTHORIZATION']);
    $input['user_data'] = $token_data;
} catch (Exception $e) {
    Response::unauthorized('Invalid token');
}

// Route the request based on the endpoint and method
switch ($request_method) {
    case 'POST':
        switch ($endpoint) {
            case 'process':
                // Validate required fields for payment processing
                if (empty($input['reservation_id']) || 
                    empty($input['payment_method']) || 
                    empty($input['amount'])) {
                    Response::error('Reservation ID, payment method, and amount are required');
                }

                // Validate payment method
                if (!in_array($input['payment_method'], ['credit_card', 'paypal', 'bank_transfer'])) {
                    Response::error('Invalid payment method');
                }

                // Process payment based on method
                switch ($input['payment_method']) {
                    case 'credit_card':
                        if (empty($input['card_details'])) {
                            Response::error('Card details are required');
                        }
                        $payment->processCreditCardPayment($input);
                        break;

                    case 'paypal':
                        $payment->processPayPalPayment($input);
                        break;

                    case 'bank_transfer':
                        $payment->processBankTransferPayment($input);
                        break;
                }
                break;

            case 'subscription':
                // Process subscription payment
                if (empty($input['subscription_type']) || 
                    empty($input['payment_method'])) {
                    Response::error('Subscription type and payment method are required');
                }

                if (!in_array($input['subscription_type'], ['base', 'gold', 'vip'])) {
                    Response::error('Invalid subscription type');
                }

                $payment->processSubscriptionPayment($input);
                break;

            case 'refund':
                // Process refund
                if (empty($input['payment_id'])) {
                    Response::error('Payment ID is required');
                }
                $payment->processRefund($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'GET':
        switch ($endpoint) {
            case 'history':
                // Get payment history
                $payment->getPaymentHistory($input);
                break;

            case 'transaction':
                // Get specific transaction details
                if (empty($input['transaction_id'])) {
                    Response::error('Transaction ID is required');
                }
                $payment->getTransactionDetails($input);
                break;

            case 'methods':
                // Get saved payment methods
                $payment->getSavedPaymentMethods($input);
                break;

            case 'subscription-status':
                // Get subscription payment status
                $payment->getSubscriptionStatus($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'PUT':
        switch ($endpoint) {
            case 'update-method':
                // Update saved payment method
                if (empty($input['method_id'])) {
                    Response::error('Payment method ID is required');
                }
                $payment->updatePaymentMethod($input);
                break;

            case 'cancel-subscription':
                // Cancel subscription
                $payment->cancelSubscription($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'DELETE':
        switch ($endpoint) {
            case 'remove-method':
                // Remove saved payment method
                if (empty($input['method_id'])) {
                    Response::error('Payment method ID is required');
                }
                $payment->removePaymentMethod($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    default:
        Response::error('Method not allowed', 405);
        break;
}

// Helper functions for payment processing
function validateCreditCard($card_details) {
    // Basic credit card validation
    if (empty($card_details['number']) ||
        empty($card_details['expiry']) ||
        empty($card_details['cvv']) ||
        empty($card_details['holder_name'])) {
        return false;
    }

    // Remove spaces and dashes from card number
    $card_number = preg_replace('/[\s-]/', '', $card_details['number']);

    // Validate card number using Luhn algorithm
    $sum = 0;
    $length = strlen($card_number);
    for ($i = 0; $i < $length; $i++) {
        $digit = (int)$card_number[$length - 1 - $i];
        if ($i % 2 == 1) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
    }
    
    return ($sum % 10) == 0;
}

function validateExpiryDate($expiry) {
    // Validate expiry date format (MM/YY)
    if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiry, $matches)) {
        return false;
    }

    $month = (int)$matches[1];
    $year = (int)('20' . $matches[2]);
    $current_year = (int)date('Y');
    $current_month = (int)date('m');

    // Check if card is expired
    return ($year > $current_year || ($year == $current_year && $month >= $current_month));
}

function validateCVV($cvv) {
    // CVV should be 3 or 4 digits
    return preg_match('/^[0-9]{3,4}$/', $cvv);
}

function formatAmount($amount) {
    // Format amount to 2 decimal places
    return number_format((float)$amount, 2, '.', '');
}

function generateTransactionId() {
    // Generate unique transaction ID
    return uniqid('TXN_') . '_' . time();
}

function calculateSubscriptionAmount($type) {
    $prices = [
        'base' => 0.00,
        'gold' => 29.99,
        'vip' => 99.99
    ];
    return $prices[$type] ?? 0.00;
}
?>
