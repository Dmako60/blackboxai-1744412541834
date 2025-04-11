<?php
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../utils/Response.php';

class PaymentController {
    private $db;
    private $payment;
    private $reservation;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->payment = new Payment($this->db);
        $this->reservation = new Reservation($this->db);
    }

    public function processCreditCardPayment($input) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Validate reservation
            $stmt = $this->reservation->getById($input['reservation_id']);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                throw new Exception('Reservation not found');
            }

            if ($reservation['status'] !== 'pending') {
                throw new Exception('Invalid reservation status');
            }

            // Validate amount matches reservation
            if ($input['amount'] != $reservation['total_price']) {
                throw new Exception('Payment amount does not match reservation total');
            }

            // Process credit card payment
            $card_details = $input['card_details'];
            
            // Validate card details
            if (!$this->validateCreditCard($card_details)) {
                throw new Exception('Invalid credit card details');
            }

            // Generate transaction ID
            $transaction_id = $this->generateTransactionId();

            // Create payment record
            $this->payment->reservation_id = $input['reservation_id'];
            $this->payment->amount = $input['amount'];
            $this->payment->payment_method = 'credit_card';
            $this->payment->status = 'completed';
            $this->payment->transaction_id = $transaction_id;
            $this->payment->payment_details = json_encode([
                'card_last4' => substr($card_details['number'], -4),
                'card_type' => $this->getCardType($card_details['number']),
                'payment_date' => date('Y-m-d H:i:s')
            ]);

            if (!$this->payment->create()) {
                throw new Exception('Failed to create payment record');
            }

            // Update reservation status
            $this->reservation->id = $input['reservation_id'];
            $this->reservation->status = 'confirmed';
            
            if (!$this->reservation->updateStatus()) {
                throw new Exception('Failed to update reservation status');
            }

            // Commit transaction
            $this->db->commit();

            Response::success([
                'transaction_id' => $transaction_id,
                'status' => 'completed'
            ], 'Payment processed successfully');

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            Response::error($e->getMessage());
        }
    }

    public function processPayPalPayment($input) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Validate reservation
            $stmt = $this->reservation->getById($input['reservation_id']);
            $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reservation) {
                throw new Exception('Reservation not found');
            }

            // TODO: Implement PayPal API integration
            $paypal_response = [
                'success' => true,
                'transaction_id' => 'PP_' . uniqid()
            ];

            if ($paypal_response['success']) {
                // Create payment record
                $this->payment->reservation_id = $input['reservation_id'];
                $this->payment->amount = $input['amount'];
                $this->payment->payment_method = 'paypal';
                $this->payment->status = 'completed';
                $this->payment->transaction_id = $paypal_response['transaction_id'];

                if (!$this->payment->create()) {
                    throw new Exception('Failed to create payment record');
                }

                // Update reservation status
                $this->reservation->id = $input['reservation_id'];
                $this->reservation->status = 'confirmed';
                
                if (!$this->reservation->updateStatus()) {
                    throw new Exception('Failed to update reservation status');
                }

                // Commit transaction
                $this->db->commit();

                Response::success([
                    'transaction_id' => $paypal_response['transaction_id'],
                    'status' => 'completed'
                ], 'Payment processed successfully');
            } else {
                throw new Exception('PayPal payment failed');
            }

        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage());
        }
    }

    public function processSubscriptionPayment($input) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Calculate subscription amount
            $amount = $this->calculateSubscriptionAmount($input['subscription_type']);

            // Process payment based on method
            switch ($input['payment_method']) {
                case 'credit_card':
                    if (!$this->validateCreditCard($input['card_details'])) {
                        throw new Exception('Invalid credit card details');
                    }
                    $transaction_id = 'CC_SUB_' . uniqid();
                    break;

                case 'paypal':
                    // TODO: Implement PayPal subscription
                    $transaction_id = 'PP_SUB_' . uniqid();
                    break;

                default:
                    throw new Exception('Invalid payment method');
            }

            // Create subscription payment record
            $this->payment->user_id = $input['user_data']->id;
            $this->payment->amount = $amount;
            $this->payment->payment_method = $input['payment_method'];
            $this->payment->status = 'completed';
            $this->payment->transaction_id = $transaction_id;
            $this->payment->payment_type = 'subscription';

            if (!$this->payment->createSubscriptionPayment()) {
                throw new Exception('Failed to create subscription payment record');
            }

            // Update user subscription
            // TODO: Implement subscription update logic

            // Commit transaction
            $this->db->commit();

            Response::success([
                'transaction_id' => $transaction_id,
                'status' => 'completed',
                'subscription_type' => $input['subscription_type']
            ], 'Subscription payment processed successfully');

        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage());
        }
    }

    public function processRefund($input) {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Get payment details
            $stmt = $this->payment->getById($input['payment_id']);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                throw new Exception('Payment not found');
            }

            if ($payment['status'] !== 'completed') {
                throw new Exception('Payment cannot be refunded');
            }

            // Process refund based on original payment method
            switch ($payment['payment_method']) {
                case 'credit_card':
                    // TODO: Implement credit card refund
                    break;

                case 'paypal':
                    // TODO: Implement PayPal refund
                    break;
            }

            // Create refund record
            $this->payment->original_payment_id = $payment['id'];
            $this->payment->amount = $payment['amount'];
            $this->payment->payment_method = $payment['payment_method'];
            $this->payment->status = 'completed';
            $this->payment->transaction_id = 'REF_' . uniqid();
            $this->payment->payment_type = 'refund';

            if (!$this->payment->createRefund()) {
                throw new Exception('Failed to create refund record');
            }

            // Update original payment status
            if (!$this->payment->updateStatus($payment['id'], 'refunded')) {
                throw new Exception('Failed to update payment status');
            }

            // Commit transaction
            $this->db->commit();

            Response::success([
                'refund_id' => $this->payment->transaction_id,
                'amount' => $payment['amount'],
                'status' => 'completed'
            ], 'Refund processed successfully');

        } catch (Exception $e) {
            $this->db->rollBack();
            Response::error($e->getMessage());
        }
    }

    private function validateCreditCard($card_details) {
        // Basic validation
        if (empty($card_details['number']) ||
            empty($card_details['expiry']) ||
            empty($card_details['cvv']) ||
            empty($card_details['holder_name'])) {
            return false;
        }

        // Validate card number using Luhn algorithm
        $number = preg_replace('/\D/', '', $card_details['number']);
        $sum = 0;
        $length = strlen($number);
        for ($i = 0; $i < $length; $i++) {
            $digit = (int)$number[$length - 1 - $i];
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

    private function getCardType($number) {
        $patterns = [
            'visa' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
            'mastercard' => '/^5[1-5][0-9]{14}$/',
            'amex' => '/^3[47][0-9]{13}$/',
            'discover' => '/^6(?:011|5[0-9]{2})[0-9]{12}$/'
        ];

        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $number)) {
                return $type;
            }
        }
        return 'unknown';
    }

    private function generateTransactionId() {
        return uniqid('TXN_') . '_' . time();
    }

    private function calculateSubscriptionAmount($type) {
        $prices = [
            'base' => 0.00,
            'gold' => 29.99,
            'vip' => 99.99
        ];
        return $prices[$type] ?? 0.00;
    }
}
?>
