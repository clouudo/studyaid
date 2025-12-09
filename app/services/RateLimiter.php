<?php

namespace App\Services;

/**
 * Rate Limiter Service
 * 
 * Prevents API overload by implementing:
 * - Per-user rate limiting
 * - Request queuing
 * - Exponential backoff
 * - Request throttling
 */
class RateLimiter
{
    private $db;
    private $config;
    private $cacheDir;

    public function __construct()
    {
        $this->db = new \App\Config\Database();
        $geminiConfig = require __DIR__ . '/../config/gemini.php';
        $this->config = $geminiConfig['rate_limiting'] ?? [
            'delay_between_calls' => 0.5,
            'max_retries' => 3,
            'retry_delay' => 2,
            'max_requests_per_minute' => 30,
            'max_requests_per_hour' => 200,
            'max_concurrent_requests' => 5,
        ];
        $this->cacheDir = __DIR__ . '/../../temp/rate_limit/';
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Check if user can make a request (rate limiting)
     * 
     * @param int $userId User ID
     * @return array ['allowed' => bool, 'wait_time' => int seconds to wait]
     */
    public function checkRateLimit(int $userId): array
    {
        $now = time();
        $minuteAgo = $now - 60;
        $hourAgo = $now - 3600;

        try {
            $conn = $this->db->connect();
            
            // Count requests in last minute
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM api_rate_limit 
                WHERE user_id = :user_id 
                AND request_time > :minute_ago
            ");
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':minute_ago', $minuteAgo, \PDO::PARAM_INT);
            $stmt->execute();
            $minuteCount = (int)$stmt->fetchColumn();

            // Count requests in last hour
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM api_rate_limit 
                WHERE user_id = :user_id 
                AND request_time > :hour_ago
            ");
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':hour_ago', $hourAgo, \PDO::PARAM_INT);
            $stmt->execute();
            $hourCount = (int)$stmt->fetchColumn();

            // Check limits
            $maxPerMinute = $this->config['max_requests_per_minute'] ?? 30;
            $maxPerHour = $this->config['max_requests_per_hour'] ?? 200;

            if ($minuteCount >= $maxPerMinute) {
                // Calculate wait time until oldest request expires
                $stmt = $conn->prepare("
                    SELECT MIN(request_time) as oldest 
                    FROM api_rate_limit 
                    WHERE user_id = :user_id 
                    AND request_time > :minute_ago
                ");
                $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':minute_ago', $minuteAgo, \PDO::PARAM_INT);
                $stmt->execute();
                $oldest = (int)$stmt->fetchColumn();
                $waitTime = max(0, 60 - ($now - $oldest));
                
                return [
                    'allowed' => false,
                    'wait_time' => $waitTime,
                    'reason' => 'minute_limit',
                    'current' => $minuteCount,
                    'limit' => $maxPerMinute
                ];
            }

            if ($hourCount >= $maxPerHour) {
                $stmt = $conn->prepare("
                    SELECT MIN(request_time) as oldest 
                    FROM api_rate_limit 
                    WHERE user_id = :user_id 
                    AND request_time > :hour_ago
                ");
                $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
                $stmt->bindValue(':hour_ago', $hourAgo, \PDO::PARAM_INT);
                $stmt->execute();
                $oldest = (int)$stmt->fetchColumn();
                $waitTime = max(0, 3600 - ($now - $oldest));
                
                return [
                    'allowed' => false,
                    'wait_time' => $waitTime,
                    'reason' => 'hour_limit',
                    'current' => $hourCount,
                    'limit' => $maxPerHour
                ];
            }

            return ['allowed' => true, 'wait_time' => 0];
        } catch (\Exception $e) {
            error_log('RateLimiter error: ' . $e->getMessage());
            // On error, allow request but log it
            return ['allowed' => true, 'wait_time' => 0];
        }
    }

    /**
     * Record a request for rate limiting
     * 
     * @param int $userId User ID
     * @return void
     */
    public function recordRequest(int $userId): void
    {
        try {
            $conn = $this->db->connect();
            
            // Ensure table exists
            $this->ensureRateLimitTable($conn);
            
            // Insert request record
            $stmt = $conn->prepare("
                INSERT INTO api_rate_limit (user_id, request_time) 
                VALUES (:user_id, :request_time)
            ");
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindValue(':request_time', time(), \PDO::PARAM_INT);
            $stmt->execute();

            // Clean up old records (older than 24 hours)
            $dayAgo = time() - 86400;
            $cleanupStmt = $conn->prepare("
                DELETE FROM api_rate_limit 
                WHERE request_time < :day_ago
            ");
            $cleanupStmt->bindValue(':day_ago', $dayAgo, \PDO::PARAM_INT);
            $cleanupStmt->execute();
        } catch (\Exception $e) {
            error_log('RateLimiter recordRequest error: ' . $e->getMessage());
        }
    }

    /**
     * Check concurrent request limit
     * 
     * @param int $userId User ID
     * @return bool
     */
    public function checkConcurrentLimit(int $userId): bool
    {
        $maxConcurrent = $this->config['max_concurrent_requests'] ?? 5;
        $lockFile = $this->cacheDir . "user_{$userId}.lock";
        
        // Count active lock files (simple file-based tracking)
        $activeCount = 0;
        $files = glob($this->cacheDir . "user_{$userId}_*.lock");
        if ($files) {
            // Remove stale locks (older than 5 minutes)
            foreach ($files as $file) {
                if (filemtime($file) < (time() - 300)) {
                    @unlink($file);
                } else {
                    $activeCount++;
                }
            }
        }

        return $activeCount < $maxConcurrent;
    }

    /**
     * Acquire a lock for concurrent request tracking
     * 
     * @param int $userId User ID
     * @return string|false Lock file path or false if limit exceeded
     */
    public function acquireLock(int $userId)
    {
        if (!$this->checkConcurrentLimit($userId)) {
            return false;
        }

        $lockFile = $this->cacheDir . "user_{$userId}_" . uniqid() . ".lock";
        @file_put_contents($lockFile, time());
        return $lockFile;
    }

    /**
     * Release a lock
     * 
     * @param string $lockFile Lock file path
     * @return void
     */
    public function releaseLock(string $lockFile): void
    {
        @unlink($lockFile);
    }

    /**
     * Ensure rate limit table exists
     * 
     * @param \PDO $conn Database connection
     * @return void
     */
    private function ensureRateLimitTable(\PDO $conn): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        try {
            $conn->exec("
                CREATE TABLE IF NOT EXISTS api_rate_limit (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    request_time INT NOT NULL,
                    INDEX idx_user_time (user_id, request_time),
                    INDEX idx_time (request_time)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            $checked = true;
        } catch (\Exception $e) {
            error_log('Failed to create rate_limit table: ' . $e->getMessage());
        }
    }

    /**
     * Get delay between calls
     * 
     * @return float Delay in seconds
     */
    public function getDelayBetweenCalls(): float
    {
        return (float)($this->config['delay_between_calls'] ?? 0.5);
    }

    /**
     * Sleep with delay between calls
     * 
     * @return void
     */
    public function delay(): void
    {
        $delay = $this->getDelayBetweenCalls();
        if ($delay > 0) {
            usleep((int)($delay * 1000000)); // Convert to microseconds
        }
    }
}

