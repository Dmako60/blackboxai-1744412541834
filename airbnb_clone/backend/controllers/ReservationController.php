<?php
require_once __DIR__ . '/../models/Reservation.php';
require_once __DIR__ . '/../models/Property.php';
require_once __DIR__ . '/../utils/Response.php';

class ReservationController {
    private $db;
    private $reservation;
    private $property;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->reservation = new Reservation($this->db);
        $this->property = new Property($this->db);
    }

    public function create($input) {
        // Validate property exists and is available
        $stmt = $this->property->getById($input['property_id']);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$property) {
            Response::error('Property not found');
        }

        if ($property['status'] !== 'active') {
            Response::error('Property is not available for booking');
        }

        // Check if dates are available
        if (!$this->reservation->isAvailable(
            $input['property_id'],
            $input['check_in'],
            $input['check_out']
        )) {
            Response::error('Property is not available for the selected dates');
        }

        // Calculate total price
        $days = (strtotime($input['check_out']) - strtotime($input['check_in'])) / (60 * 60 * 24);
        $total_price = $days * $property['price_per_night'];

        // Set reservation data
        $this->reservation->property_id = $input['property_id'];
        $this->reservation->user_id = $input['user_data']->id;
        $this->reservation->check_in = $input['check_in'];
        $this->reservation->check_out = $input['check_out'];
        $this->reservation->guests_count = $input['guests_count'];
        $this->reservation->total_price = $total_price;
        $this->reservation->status = 'pending';
        $this->reservation->special_requests = $input['special_requests'] ?? null;

        if ($this->reservation->create()) {
            Response::success([
                'reservation_id' => $this->reservation->id,
                'total_price' => $total_price
            ], 'Reservation created successfully');
        } else {
            Response::error('Failed to create reservation');
        }
    }

    public function cancel($input) {
        // Get reservation details
        $stmt = $this->reservation->getById($input['reservation_id']);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            Response::error('Reservation not found');
        }

        // Check if user owns this reservation
        if ($reservation['user_id'] != $input['user_data']->id) {
            Response::forbidden('You do not have permission to cancel this reservation');
        }

        // Check if reservation can be cancelled
        $check_in_time = strtotime($reservation['check_in']);
        $cancellation_deadline = $check_in_time - (CANCELLATION_DEADLINE_HOURS * 3600);

        if (time() > $cancellation_deadline) {
            Response::error('Cancellation deadline has passed');
        }

        // Process cancellation
        $this->reservation->id = $input['reservation_id'];
        $this->reservation->status = 'cancelled';
        $this->reservation->cancellation_reason = $input['reason'] ?? null;

        if ($this->reservation->cancel()) {
            // Calculate refund amount
            $refund_amount = ($reservation['total_price'] * CANCELLATION_REFUND_PERCENTAGE) / 100;

            Response::success([
                'refund_amount' => $refund_amount
            ], 'Reservation cancelled successfully');
        } else {
            Response::error('Failed to cancel reservation');
        }
    }

    public function list($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : DEFAULT_PAGE_SIZE;
        $status = isset($input['status']) ? $input['status'] : null;

        $stmt = $this->reservation->getByUser(
            $input['user_data']->id,
            $page,
            $limit,
            $status
        );

        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->reservation->getTotalCount($input['user_data']->id, $status);

        Response::paginated(
            $reservations,
            $total,
            $page,
            $limit
        );
    }

    public function view($input) {
        $stmt = $this->reservation->getById($input['id']);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            Response::error('Reservation not found');
        }

        // Check if user has permission to view this reservation
        if ($reservation['user_id'] != $input['user_data']->id) {
            Response::forbidden('You do not have permission to view this reservation');
        }

        Response::success($reservation);
    }

    public function checkAvailability($input) {
        $available = $this->reservation->isAvailable(
            $input['property_id'],
            $input['check_in'],
            $input['check_out']
        );

        if ($available) {
            // Calculate total price
            $stmt = $this->property->getById($input['property_id']);
            $property = $stmt->fetch(PDO::FETCH_ASSOC);
            $days = (strtotime($input['check_out']) - strtotime($input['check_in'])) / (60 * 60 * 24);
            $total_price = $days * $property['price_per_night'];

            Response::success([
                'available' => true,
                'total_price' => $total_price
            ]);
        } else {
            Response::success([
                'available' => false,
                'message' => 'Property is not available for the selected dates'
            ]);
        }
    }

    public function updateDates($input) {
        // Get reservation details
        $stmt = $this->reservation->getById($input['reservation_id']);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            Response::error('Reservation not found');
        }

        // Check if user owns this reservation
        if ($reservation['user_id'] != $input['user_data']->id) {
            Response::forbidden('You do not have permission to update this reservation');
        }

        // Check if new dates are available
        if (!$this->reservation->isAvailable(
            $reservation['property_id'],
            $input['check_in'],
            $input['check_out'],
            $input['reservation_id']
        )) {
            Response::error('Property is not available for the selected dates');
        }

        // Calculate new total price
        $stmt = $this->property->getById($reservation['property_id']);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);
        $days = (strtotime($input['check_out']) - strtotime($input['check_in'])) / (60 * 60 * 24);
        $total_price = $days * $property['price_per_night'];

        // Update reservation
        $this->reservation->id = $input['reservation_id'];
        $this->reservation->check_in = $input['check_in'];
        $this->reservation->check_out = $input['check_out'];
        $this->reservation->total_price = $total_price;

        if ($this->reservation->updateDates()) {
            Response::success([
                'total_price' => $total_price
            ], 'Reservation dates updated successfully');
        } else {
            Response::error('Failed to update reservation dates');
        }
    }

    public function updateGuestsCount($input) {
        // Get reservation details
        $stmt = $this->reservation->getById($input['reservation_id']);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            Response::error('Reservation not found');
        }

        // Check if user owns this reservation
        if ($reservation['user_id'] != $input['user_data']->id) {
            Response::forbidden('You do not have permission to update this reservation');
        }

        // Check if property can accommodate the new guest count
        $stmt = $this->property->getById($reservation['property_id']);
        $property = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($input['guests_count'] > $property['max_guests']) {
            Response::error('Property cannot accommodate this many guests');
        }

        // Update reservation
        $this->reservation->id = $input['reservation_id'];
        $this->reservation->guests_count = $input['guests_count'];

        if ($this->reservation->updateGuestsCount()) {
            Response::success(null, 'Guest count updated successfully');
        } else {
            Response::error('Failed to update guest count');
        }
    }

    public function getUpcoming($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : DEFAULT_PAGE_SIZE;

        $stmt = $this->reservation->getUpcoming(
            $input['user_data']->id,
            $page,
            $limit
        );

        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->reservation->getTotalUpcoming($input['user_data']->id);

        Response::paginated(
            $reservations,
            $total,
            $page,
            $limit
        );
    }

    public function getPast($input) {
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $limit = isset($input['limit']) ? (int)$input['limit'] : DEFAULT_PAGE_SIZE;

        $stmt = $this->reservation->getPast(
            $input['user_data']->id,
            $page,
            $limit
        );

        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->reservation->getTotalPast($input['user_data']->id);

        Response::paginated(
            $reservations,
            $total,
            $page,
            $limit
        );
    }
}
?>
