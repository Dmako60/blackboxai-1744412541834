<?php
require_once __DIR__ . '/../config/constants.php';

class Response {
    /**
     * Send a success response
     * 
     * @param mixed $data The data to be sent in the response
     * @param string|null $message Optional success message
     * @param int $code HTTP status code
     */
    public static function success($data = null, $message = null, $code = HTTP_OK) {
        self::setHeaders($code);
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }

    /**
     * Send an error response
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param mixed $errors Additional error details
     */
    public static function error($message, $code = HTTP_BAD_REQUEST, $errors = null) {
        self::setHeaders($code);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ]);
        exit();
    }

    /**
     * Send a validation error response
     * 
     * @param array $errors Validation errors
     * @param string $message Optional error message
     */
    public static function validationError($errors, $message = 'Validation failed') {
        self::setHeaders(HTTP_BAD_REQUEST);
        echo json_encode([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ]);
        exit();
    }

    /**
     * Send an unauthorized error response
     * 
     * @param string $message Optional error message
     */
    public static function unauthorized($message = 'Unauthorized access') {
        self::setHeaders(HTTP_UNAUTHORIZED);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit();
    }

    /**
     * Send a forbidden error response
     * 
     * @param string $message Optional error message
     */
    public static function forbidden($message = 'Access forbidden') {
        self::setHeaders(HTTP_FORBIDDEN);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit();
    }

    /**
     * Send a not found error response
     * 
     * @param string $message Optional error message
     */
    public static function notFound($message = 'Resource not found') {
        self::setHeaders(HTTP_NOT_FOUND);
        echo json_encode([
            'status' => 'error',
            'message' => $message
        ]);
        exit();
    }

    /**
     * Send a server error response
     * 
     * @param string $message Optional error message
     * @param mixed $debug Debug information (only included in development)
     */
    public static function serverError($message = 'Internal server error', $debug = null) {
        self::setHeaders(HTTP_SERVER_ERROR);
        $response = [
            'status' => 'error',
            'message' => $message
        ];

        // Include debug information only in development
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development' && $debug) {
            $response['debug'] = $debug;
        }

        echo json_encode($response);
        exit();
    }

    /**
     * Set response headers
     * 
     * @param int $code HTTP status code
     */
    private static function setHeaders($code) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');
        http_response_code($code);
    }

    /**
     * Send a paginated response
     * 
     * @param array $data The data to be paginated
     * @param int $total Total number of items
     * @param int $page Current page number
     * @param int $limit Items per page
     * @param string|null $message Optional success message
     */
    public static function paginated($data, $total, $page, $limit, $message = null) {
        self::setHeaders(HTTP_OK);
        echo json_encode([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        exit();
    }

    /**
     * Send a file download response
     * 
     * @param string $filePath Path to the file
     * @param string $fileName Optional filename for download
     */
    public static function download($filePath, $fileName = null) {
        if (!file_exists($filePath)) {
            self::notFound('File not found');
        }

        $fileName = $fileName ?? basename($filePath);
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($filePath);
        exit();
    }

    /**
     * Send a stream response
     * 
     * @param resource $stream The stream resource
     * @param string $mimeType MIME type of the stream
     */
    public static function stream($stream, $mimeType = 'application/octet-stream') {
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: no-cache');
        header('Content-Transfer-Encoding: binary');

        fpassthru($stream);
        exit();
    }
}
?>
