<?php
class Logger {
    private static $instance = null;
    private $logPath;
    private $logLevel;

    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';

    private function __construct() {
        $this->logPath = __DIR__ . '/../logs/';
        $this->logLevel = defined('LOG_LEVEL') ? LOG_LEVEL : self::LEVEL_INFO;
        
        // Create logs directory if it doesn't exist
        if (!file_exists($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function debug($message, array $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    public function info($message, array $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    public function warning($message, array $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    public function error($message, array $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    public function critical($message, array $context = []) {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }

    private function log($level, $message, array $context = []) {
        if (!$this->shouldLog($level)) {
            return;
        }

        $logEntry = $this->formatLogEntry($level, $message, $context);
        $filename = $this->getLogFilename();
        
        file_put_contents(
            $this->logPath . $filename,
            $logEntry . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        // If it's a critical error, also send notification
        if ($level === self::LEVEL_CRITICAL) {
            $this->notifyAdmin($message, $context);
        }
    }

    private function shouldLog($level) {
        $levels = [
            self::LEVEL_DEBUG => 1,
            self::LEVEL_INFO => 2,
            self::LEVEL_WARNING => 3,
            self::LEVEL_ERROR => 4,
            self::LEVEL_CRITICAL => 5
        ];

        return $levels[$level] >= $levels[$this->logLevel];
    }

    private function formatLogEntry($level, $message, array $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $requestId = $this->getRequestId();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $user = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'anonymous';

        $contextString = empty($context) ? '' : ' ' . json_encode($context);
        
        return sprintf(
            '[%s] [%s] [%s] [%s] [%s %s] [User: %s] %s%s',
            $timestamp,
            $level,
            $requestId,
            $ip,
            $method,
            $uri,
            $user,
            $message,
            $contextString
        );
    }

    private function getLogFilename() {
        return date('Y-m-d') . '.log';
    }

    private function getRequestId() {
        if (!isset($_SERVER['REQUEST_ID'])) {
            $_SERVER['REQUEST_ID'] = uniqid('req_', true);
        }
        return $_SERVER['REQUEST_ID'];
    }

    private function notifyAdmin($message, array $context = []) {
        // TODO: Implement admin notification system
        // This could be email, SMS, Slack notification, etc.
        if (defined('ADMIN_EMAIL') && ADMIN_EMAIL) {
            $subject = 'CRITICAL ERROR - Airbnb Clone';
            $body = $this->formatLogEntry(self::LEVEL_CRITICAL, $message, $context);
            mail(ADMIN_EMAIL, $subject, $body);
        }
    }

    public function getRecentLogs($minutes = 60, $level = null) {
        $logs = [];
        $filename = $this->getLogFilename();
        $filepath = $this->logPath . $filename;

        if (!file_exists($filepath)) {
            return $logs;
        }

        $handle = fopen($filepath, 'r');
        if ($handle) {
            $cutoff = time() - ($minutes * 60);

            while (($line = fgets($handle)) !== false) {
                if (preg_match('/^\[(.*?)\]/', $line, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    if ($timestamp >= $cutoff) {
                        if ($level) {
                            if (strpos($line, "[$level]") !== false) {
                                $logs[] = $line;
                            }
                        } else {
                            $logs[] = $line;
                        }
                    }
                }
            }
            fclose($handle);
        }

        return $logs;
    }

    public function rotateLogs() {
        $files = glob($this->logPath . '*.log');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                $lastModified = filemtime($file);
                // If file is older than 30 days
                if ($now - $lastModified >= 30 * 24 * 60 * 60) {
                    // Create archive directory if it doesn't exist
                    $archivePath = $this->logPath . 'archive/';
                    if (!file_exists($archivePath)) {
                        mkdir($archivePath, 0755, true);
                    }
                    
                    // Move file to archive
                    $filename = basename($file);
                    rename($file, $archivePath . $filename . '.gz');
                    // Compress the file
                    system('gzip ' . escapeshellarg($archivePath . $filename));
                }
            }
        }
    }

    public function clearOldLogs($days = 90) {
        $files = glob($this->logPath . 'archive/*.log.gz');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file)) {
                $lastModified = filemtime($file);
                // If file is older than specified days
                if ($now - $lastModified >= $days * 24 * 60 * 60) {
                    unlink($file);
                }
            }
        }
    }
}
?>
