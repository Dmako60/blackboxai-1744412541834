<?php
require_once __DIR__ . '/../controllers/ReservationController.php';
require_once __DIR__ . '/../utils/JWTHandler.php';
require_once __DIR__ . '/../utils/Response.php';

$reservation = new ReservationController();
$jwt = new JWTHandler();

// Get the endpoint from URI segments
$endpoint = isset($uri_segments[1]) ? $uri_segments[1] : '';

// All reservation endpoints require authentication
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
            case 'create':
                // Validate required fields
                if (empty($input['property_id']) || 
                    empty($input['check_in']) || 
                    empty($input['check_out']) || 
                    empty($input['guests_count'])) {
                    Response::error('Property ID, check-in date, check-out date, and guests count are required');
                }

                // Validate dates
                $check_in = strtotime($input['check_in']);
                $check_out = strtotime($input['check_out']);
                $today = strtotime('today');

                if ($check_in < $today) {
                    Response::error('Check-in date cannot be in the past');
                }

                if ($check_out <= $check_in) {
                    Response::error('Check-out date must be after check-in date');
                }

                if (($check_out - $check_in) / (60 * 60 * 24) > MAX_RESERVATION_DAYS) {
                    Response::error('Maximum reservation period is ' . MAX_RESERVATION_DAYS . ' days');
                }

                $reservation->create($input);
                break;

            case 'cancel':
                if (empty($input['reservation_id'])) {
                    Response::error('Reservation ID is required');
                }
                $reservation->cancel($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'GET':
        switch ($endpoint) {
            case 'list':
                // List reservations with optional filters
                $reservation->list($input);
                break;

            case 'view':
                if (empty($input['id'])) {
                    Response::error('Reservation ID is required');
                }
                $reservation->view($input);
                break;

            case 'check-availability':
                if (empty($input['property_id']) || 
                    empty($input['check_in']) || 
                    empty($input['check_out'])) {
                    Response::error('Property ID, check-in date, and check-out date are required');
                }
                $reservation->checkAvailability($input);
                break;

            case 'upcoming':
                // Get upcoming reservations
                $reservation->getUpcoming($input);
                break;

            case 'past':
                // Get past reservations
                $reservation->getPast($input);
                break;

            case 'cancelled':
                // Get cancelled reservations
                $reservation->getCancelled($input);
                break;

            default:
                Response::notFound('Endpoint not found');
                break;
        }
        break;

    case 'PUT':
        switch ($endpoint) {
            case 'update-dates':
                if (empty($input['reservation_id']) || 
                    empty($input['check_in']) || 
                    empty($input['check_out'])) {
                    Response::error('Reservation ID, new check-in date, and new check-out date are required');
                }

                // Validate dates
                $check_in = strtotime($input['check_in']);
                $check_out = strtotime($input['check_out']);
                $today = strtotime('today');

                if ($check_in < $today) {
                    Response::error('Check-in date cannot be in the past');
                }

                if ($check_out <= $check_in) {
                    Response::error('Check-out date must be after check-in date');
                }

                $reservation->updateDates($input);
                break;

            case 'update-guests':
                if (empty($input['reservation_id']) || 
                    empty($input['guests_count'])) {
                    Response::error('Reservation ID and guests count are required');
                }
                $reservation->updateGuestsCount($input);
                break;

            case 'special-requests':
                if (empty($input['reservation_id'])) {
                    Response::error('Reservation ID is required');
                }
                $reservation->updateSpecialRequests($input);
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

// Helper functions for date validation
function isValidDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function getDaysDifference($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    return $interval->days;
}

function isDateAvailable($property_id, $check_in, $check_out) {
    global $reservation;
    return $reservation->checkAvailability([
        'property_id' => $property_id,
        'check_in' => $check_in,
        'check_out' => $check_out
    ]);
}

function calculateTotalPrice($property_id, $check_in, $check_out) {
    global $reservation;
    return $reservation->calculatePrice([
        'property_id' => $property_id,
        'check_in' => $check_in,
        'check_out' => $check_out
    ]);
}

function canCancelReservation($reservation_id) {
    global $reservation;
    return $reservation->canCancel([
        'reservation_id' => $reservation_id
    ]);
}
?>
