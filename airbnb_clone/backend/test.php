<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/utils/Response.php';

try {
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
    
    // Test queries for each table
    $tables = [
        'admins' => 'SELECT COUNT(*) as count FROM admins',
        'agents' => 'SELECT COUNT(*) as count FROM agents',
        'properties' => 'SELECT COUNT(*) as count FROM properties',
        'reservations' => 'SELECT COUNT(*) as count FROM reservations',
        'payments' => 'SELECT COUNT(*) as count FROM payments',
        'users' => 'SELECT COUNT(*) as count FROM users',
        'property_images' => 'SELECT COUNT(*) as count FROM property_images',
        'subscription_plans' => 'SELECT COUNT(*) as count FROM subscription_plans',
        'agent_subscriptions' => 'SELECT COUNT(*) as count FROM agent_subscriptions'
    ];

    $results = [];
    foreach ($tables as $table => $query) {
        try {
            $stmt = $db->query($query);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $results[$table] = [
                'status' => 'success',
                'count' => $row['count']
            ];
        } catch (PDOException $e) {
            $results[$table] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Test file system
    $upload_dirs = [
        'uploads',
        'uploads/properties',
        'uploads/profiles',
        'uploads/temp'
    ];

    $filesystem = [];
    foreach ($upload_dirs as $dir) {
        $full_path = __DIR__ . '/' . $dir;
        $filesystem[$dir] = [
            'exists' => file_exists($full_path),
            'writable' => is_writable($full_path),
            'path' => $full_path
        ];
    }

    // Test JWT generation
    require_once __DIR__ . '/utils/JWTHandler.php';
    $jwt = new JWTHandler();
    $test_token = $jwt->generateToken(['test' => true]);
    $token_valid = $jwt->validateToken($test_token);

    // Prepare response
    $response = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION,
        'database' => [
            'connection' => 'success',
            'tables' => $results
        ],
        'filesystem' => $filesystem,
        'jwt' => [
            'generation' => !empty($test_token),
            'validation' => $token_valid
        ],
        'environment' => [
            'max_file_size' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ]
    ];

    // Output results
    header('Content-Type: application/json');
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    Response::error('Test failed: ' . $e->getMessage());
}

// Function to test if a directory is writable by actually trying to write a file
function isDirectoryWritable($dir) {
    $test_file = $dir . '/test_' . uniqid() . '.txt';
    try {
        $handle = fopen($test_file, 'w');
        if ($handle) {
            fwrite($handle, 'test');
            fclose($handle);
            unlink($test_file);
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

// Function to format bytes to human readable format
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}
?>
