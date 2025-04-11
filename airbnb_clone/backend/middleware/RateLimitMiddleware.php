<?php
require_once __DIR__ . '/Middleware.php';
require_once __DIR__ . '/../utils/Response.php';

class RateLimitMiddleware extends Middleware {
    private $requestsPerMinute;
    private $redisKey;
    private $redis;

    public function __construct($next = null, $requestsPerMinute = 60) {
        parent::__construct($next);
        $this->requestsPerMinute = $requestsPerMinute;
        
        // Initialize Redis connection
        try {
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);
        } catch (Exception $e) {
            // Fallback to in-memory storage if Redis is not available
            $this->redis = null;
        }
    }

    public function handle($request) {
        $ip = $this->getClientIp();
        $endpoint = $request['endpoint'] ?? 'default';
        $this->redisKey = "rate_limit:{$ip}:{$endpoint}";

        if ($this->isRateLimited()) {
            $retryAfter = $this->getRetryAfterSeconds();
            header('Retry-After: ' . $retryAfter);
            Response::error('Too many requests. Please try again later.', 429);
        }

        $this->incrementRequestCount();
        return $this->handleNext($request);
    }

    private function isRateLimited() {
        if ($this->redis) {
            return $this->isRateLimitedRedis();
        }
        return $this->isRateLimitedMemory();
    }

    private function isRateLimitedRedis() {
        $count = $this->redis->get($this->redisKey) ?: 0;
        return $count >= $this->requestsPerMinute;
    }

    private function isRateLimitedMemory() {
        $storage = $this->getMemoryStorage();
        $currentTime = time();
        $count = 0;

        foreach ($storage as $time) {
            if ($currentTime - $time < 60) {
                $count++;
            }
        }

        return $count >= $this->requestsPerMinute;
    }

    private function incrementRequestCount() {
        if ($this->redis) {
            $this->incrementRequestCountRedis();
        } else {
            $this->incrementRequestCountMemory();
        }
    }

    private function incrementRequestCountRedis() {
        if (!$this->redis->exists($this->redisKey)) {
            $this->redis->setex($this->redisKey, 60, 1);
        } else {
            $this->redis->incr($this->redisKey);
        }
    }

    private function incrementRequestCountMemory() {
        $storage = $this->getMemoryStorage();
        $storage[] = time();
        $this->cleanupOldRequests($storage);
        $this->saveMemoryStorage($storage);
    }

    private function getRetryAfterSeconds() {
        if ($this->redis) {
            return $this->redis->ttl($this->redisKey);
        }
        
        $storage = $this->getMemoryStorage();
        if (empty($storage)) {
            return 0;
        }
        
        $oldestRequest = min($storage);
        return max(0, 60 - (time() - $oldestRequest));
    }

    private function cleanupOldRequests(&$storage) {
        $currentTime = time();
        $storage = array_filter($storage, function($time) use ($currentTime) {
            return $currentTime - $time < 60;
        });
    }

    private function getMemoryStorage() {
        $filename = sys_get_temp_dir() . '/' . md5($this->redisKey) . '.txt';
        if (!file_exists($filename)) {
            return [];
        }
        return json_decode(file_get_contents($filename), true) ?: [];
    }

    private function saveMemoryStorage($storage) {
        $filename = sys_get_temp_dir() . '/' . md5($this->redisKey) . '.txt';
        file_put_contents($filename, json_encode($storage));
    }

    private function getClientIp() {
        $headers = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    public function __destruct() {
        if ($this->redis) {
            $this->redis->close();
        }
    }
}
?>
