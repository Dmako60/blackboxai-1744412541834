<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer {
    private static $instance = null;
    private $mailer;
    private $logger;
    private $templatePath;
    private $defaultFrom;
    private $defaultFromName;

    private function __construct() {
        $this->logger = Logger::getInstance();
        $this->templatePath = __DIR__ . '/../templates/emails/';
        $this->defaultFrom = defined('MAIL_FROM') ? MAIL_FROM : 'noreply@airbnbclone.com';
        $this->defaultFromName = defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Airbnb Clone';

        try {
            $this->mailer = new PHPMailer(true);

            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.mailtrap.io';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
            $this->mailer->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            $this->mailer->SMTPSecure = defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
            $this->mailer->CharSet = 'UTF-8';

            // Debug level
            $this->mailer->SMTPDebug = defined('ENVIRONMENT') && ENVIRONMENT === 'development' ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

        } catch (Exception $e) {
            $this->logger->error('Mailer initialization failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function send($to, $subject, $template, $data = [], $attachments = []) {
        try {
            // Reset all recipients and attachments
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();

            // Set sender
            $this->mailer->setFrom($this->defaultFrom, $this->defaultFromName);

            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Set subject
            $this->mailer->Subject = $subject;

            // Set content
            $html = $this->renderTemplate($template, $data);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            // Add attachments
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $this->mailer->addAttachment(
                        $attachment['path'],
                        $attachment['name'] ?? basename($attachment['path'])
                    );
                } else {
                    $this->mailer->addAttachment($attachment);
                }
            }

            // Send email
            $result = $this->mailer->send();

            $this->logger->info('Email sent successfully', [
                'to' => $to,
                'subject' => $subject,
                'template' => $template
            ]);

            return $result;

        } catch (Exception $e) {
            $this->logger->error('Email sending failed', [
                'to' => $to,
                'subject' => $subject,
                'template' => $template,
                'error' => $e->getMessage()
            ]);

            throw new Exception('Failed to send email: ' . $e->getMessage());
        }
    }

    private function renderTemplate($template, $data) {
        $templateFile = $this->templatePath . $template . '.php';

        if (!file_exists($templateFile)) {
            throw new Exception("Email template not found: $template");
        }

        // Extract data to make variables available in template
        extract($data);

        // Start output buffering
        ob_start();

        // Include template
        include $templateFile;

        // Get contents and clean buffer
        return ob_get_clean();
    }

    public function sendWelcomeEmail($user) {
        return $this->send(
            $user['email'],
            'Welcome to ' . $this->defaultFromName,
            'welcome',
            ['name' => $user['name']]
        );
    }

    public function sendReservationConfirmation($reservation, $user, $property) {
        return $this->send(
            $user['email'],
            'Reservation Confirmation #' . $reservation['id'],
            'reservation-confirmation',
            [
                'user' => $user,
                'reservation' => $reservation,
                'property' => $property
            ]
        );
    }

    public function sendPaymentReceipt($payment, $user, $reservation) {
        // Generate PDF receipt
        $pdf = $this->generateReceipt($payment, $reservation);
        
        return $this->send(
            $user['email'],
            'Payment Receipt #' . $payment['id'],
            'payment-receipt',
            [
                'user' => $user,
                'payment' => $payment,
                'reservation' => $reservation
            ],
            [
                [
                    'path' => $pdf,
                    'name' => 'receipt.pdf'
                ]
            ]
        );
    }

    public function sendPasswordReset($user, $resetToken) {
        return $this->send(
            $user['email'],
            'Password Reset Request',
            'password-reset',
            [
                'name' => $user['name'],
                'reset_link' => SITE_URL . '/reset-password?token=' . $resetToken
            ]
        );
    }

    public function sendPropertyApproval($agent, $property) {
        return $this->send(
            $agent['email'],
            'Property Listing Approved',
            'property-approval',
            [
                'agent' => $agent,
                'property' => $property
            ]
        );
    }

    private function generateReceipt($payment, $reservation) {
        // TODO: Implement PDF generation
        // This is a placeholder - you would typically use a library like TCPDF or FPDF
        return 'path/to/generated/receipt.pdf';
    }

    public function sendBulk($recipients, $subject, $template, $commonData = [], $individualData = []) {
        $results = ['success' => [], 'failed' => []];

        foreach ($recipients as $recipient) {
            $data = array_merge($commonData, $individualData[$recipient['email']] ?? []);
            
            try {
                $this->send($recipient['email'], $subject, $template, $data);
                $results['success'][] = $recipient['email'];
            } catch (Exception $e) {
                $results['failed'][] = [
                    'email' => $recipient['email'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
?>
