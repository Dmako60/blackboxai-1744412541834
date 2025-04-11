<?php
class Cache {
    private static $instance = null;
    private $redis;
    private $fallbackPath;
    private $isRedisAvailable;
    private $logger;
    private $defaultTTL = 3600; // 1 hour default TTL

    private function __construct() {
        $this->logger = Logger::getInstance();
        $this->fallbackPath = __DIR__ . '/../cache/';
        
        // Create cache directory if it doesn't exist
        if (!file_exists($this->fallbackPath)) {
            mkdir($this->fallbackPath, 0755, true);
        }

        // Try to connect to Redis
        try {
            $this->redis = new Redis();
            $this->redis->connect(
                defined('REDIS_HOST') ? REDIS_HOST : '127.0.0.1',
                defined('REDIS_PORT') ? REDIS_PORT : 6379,
                defined('REDIS_TIMEOUT') ? REDIS_TIMEOUT : 2
            );

            if (defined('REDIS_PASSWORD')) {
                $this->redis->auth(REDIS_PASSWORD);
            }

            $this->isRedisAvailable = true;
            $this->logger->info('Redis connection established');
        } catch (Exception $e) {
            $this->isRedisAvailable = false;
            $this->logger->warning('Redis connection failed, using file-based cache', [
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

    public function get($key, $default = null) {
        try {
            if ($this->isRedisAvailable) {
                $value = $this->redis->get($key);
                if ($value === false) {
                    return $default;
                }
                return json_decode($value, true);
            }

            return $this->getFromFile($key, $default);
        } catch (Exception $e) {
            $this->logger->error('Cache get error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    public function set($key, $value, $ttl = null) {
        try {
            $ttl = $ttl ?? $this->defaultTTL;
            $serialized = json_encode($value);

            if ($this->isRedisAvailable) {
                return $this->redis->setex($key, $ttl, $serialized);
            }

            return $this->saveToFile($key, $serialized, $ttl);
        } catch (Exception $e) {
            $this->logger->error('Cache set error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function delete($key) {
        try {
            if ($this->isRedisAvailable) {
                return $this->redis->del($key);
            }

            return $this->deleteFromFile($key);
        } catch (Exception $e) {
            $this->logger->error('Cache delete error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function clear() {
        try {
            if ($this->isRedisAvailable) {
                return $this->redis->flushAll();
            }

            array_map('unlink', glob($this->fallbackPath . '*.cache'));
            return true;
        } catch (Exception $e) {
            $this->logger->error('Cache clear error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    public function increment($key, $value = 1) {
        try {
            if ($this->isRedisAvailable) {
                return $this->redis->incrBy($key, $value);
            }

            $current = (int)$this->get($key, 0);
            $new = $current + $value;
            $this->set($key, $new);
            return $new;
        } catch (Exception $e) {
            $this->logger->error('Cache increment error', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function decrement($key, $value = 1) {
        return $this->increment($key, -$value);
    }

    public function tags(array $tags) {
        return new CacheTagSet($this, $tags);
    }

    private function getFromFile($key, $default = null) {
        $path = $this->getFilePath($key);
        
        if (!file_exists($path)) {
            return $default;
        }

        $data = json_decode(file_get_contents($path), true);
        
        if (!$data || time() > $data['expiry']) {
            @unlink($path);
            return $default;
        }

        return $data['value'];
    }

    private function saveToFile($key, $value, $ttl) {
        $path = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expiry' => time() + $ttl
        ];

        return file_put_contents($path, json_encode($data), LOCK_EX) !== false;
    }

    private function deleteFromFile($key) {
        $path = $this->getFilePath($key);
        if (file_exists($path)) {
            return @unlink($path);
        }
        return true;
    }

    private function getFilePath($key) {
        return $this->fallbackPath . md5($key) . '.cache';
    }

    public function cleanExpired() {
        if (!$this->isRedisAvailable) {
            $files = glob($this->fallbackPath . '*.cache');
            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data && time() > $data['expiry']) {
                    @unlink($file);
                }
            }
        }
    }

    public function getMultiple(array $keys, $default = null) {
        $values = [];
        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }
        return $values;
    }

    public function setMultiple(array $values, $ttl = null) {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    public function deleteMultiple(array $keys) {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    public function has($key) {
        return $this->get($key) !== null;
    }
}

class CacheTagSet {
    private $cache;
    private $tags;

    public function __construct($cache, array $tags) {
        $this->cache = $cache;
        $this->tags = $tags;
    }

    public function get($key, $default = null) {
        return $this->cache->get($this->taggedKey($key), $default);
    }

    public function set($key, $value, $ttl = null) {
        return $this->cache->set($this->taggedKey($key), $value, $ttl);
    }

    public function delete($key) {
        return $this->cache->delete($this->taggedKey($key));
    }

    private function taggedKey($key) {
        return implode(':', array_merge($this->tags, [$key]));
    }

    public function flush() {
        // Implement tag-based cache flushing
        $pattern = implode(':', array_merge($this->tags, ['*']));
        if ($this->cache->isRedisAvailable) {
            $keys = $this->cache->redis->keys($pattern);
            return $this->cache->deleteMultiple($keys);
        }
        return true;
    }
}
?>
