<?php

namespace System\Includes;

use PDO;

/**
 * 캐싱 시스템
 * 파일, Redis, 메모리 등 다양한 드라이버를 지원하는 캐싱 시스템
 */
class CacheManager
{
    private array $config;
    private $driver;
    private Logger $logger;
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'writes' => 0,
        'deletes' => 0
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'driver' => 'file',
            'prefix' => 'mp_cache_',
            'default_ttl' => 3600,
            'file' => [
                'path' => __DIR__ . '/../../system/cache/',
                'extension' => '.cache'
            ],
            'redis' => [
                'host' => 'localhost',
                'port' => 6379,
                'database' => 0,
                'password' => null
            ],
            'memory' => [
                'max_items' => 1000
            ]
        ], $config);

        $this->logger = new Logger('cache');
        $this->driver = $this->createDriver();
    }

    /**
     * 캐시 드라이버 생성
     */
    private function createDriver()
    {
        $driver = $this->config['driver'];
        
        return match($driver) {
            'file' => new FileCacheDriver($this->config['file']),
            'redis' => new RedisCacheDriver($this->config['redis']),
            'memory' => new MemoryCacheDriver($this->config['memory']),
            default => throw new \InvalidArgumentException("Unsupported cache driver: {$driver}")
        };
    }

    /**
     * 캐시 저장
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->config['default_ttl'];
        $fullKey = $this->config['prefix'] . $key;
        
        try {
            $result = $this->driver->set($fullKey, $value, $ttl);
            
            if ($result) {
                $this->stats['writes']++;
                $this->logger->debug('Cache set', [
                    'key' => $key,
                    'ttl' => $ttl
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache set failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 캐시 조회
     */
    public function get(string $key, $default = null)
    {
        $fullKey = $this->config['prefix'] . $key;
        
        try {
            $value = $this->driver->get($fullKey);
            
            if ($value !== null) {
                $this->stats['hits']++;
                $this->logger->debug('Cache hit', ['key' => $key]);
                return $value;
            } else {
                $this->stats['misses']++;
                $this->logger->debug('Cache miss', ['key' => $key]);
                return $default;
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache get failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * 캐시 존재 확인
     */
    public function has(string $key): bool
    {
        $fullKey = $this->config['prefix'] . $key;
        
        try {
            return $this->driver->has($fullKey);
        } catch (\Exception $e) {
            $this->logger->error('Cache has failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 캐시 삭제
     */
    public function delete(string $key): bool
    {
        $fullKey = $this->config['prefix'] . $key;
        
        try {
            $result = $this->driver->delete($fullKey);
            
            if ($result) {
                $this->stats['deletes']++;
                $this->logger->debug('Cache deleted', ['key' => $key]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache delete failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 패턴으로 캐시 삭제
     */
    public function deletePattern(string $pattern): int
    {
        $fullPattern = $this->config['prefix'] . $pattern;
        
        try {
            $count = $this->driver->deletePattern($fullPattern);
            
            if ($count > 0) {
                $this->stats['deletes'] += $count;
                $this->logger->info('Cache pattern deleted', [
                    'pattern' => $pattern,
                    'count' => $count
                ]);
            }
            
            return $count;
        } catch (\Exception $e) {
            $this->logger->error('Cache pattern delete failed', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * 모든 캐시 삭제
     */
    public function clear(): bool
    {
        try {
            $result = $this->driver->clear();
            
            if ($result) {
                $this->logger->info('Cache cleared');
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache clear failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 캐시 증가
     */
    public function increment(string $key, int $value = 1): int
    {
        $fullKey = $this->config['prefix'] . $key;
        
        try {
            $result = $this->driver->increment($fullKey, $value);
            
            if ($result !== false) {
                $this->stats['writes']++;
                $this->logger->debug('Cache incremented', [
                    'key' => $key,
                    'value' => $value,
                    'result' => $result
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache increment failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 캐시 감소
     */
    public function decrement(string $key, int $value = 1): int
    {
        $fullKey = $this->config['prefix'] . $key;
        
        try {
            $result = $this->driver->decrement($fullKey, $value);
            
            if ($result !== false) {
                $this->stats['writes']++;
                $this->logger->debug('Cache decremented', [
                    'key' => $key,
                    'value' => $value,
                    'result' => $result
                ]);
            }
            
            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Cache decrement failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 캐시에 저장하거나 콜백 실행
     */
    public function remember(string $key, callable $callback, int $ttl = null)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }

    /**
     * 캐시 태그 관리
     */
    public function tags(array $tags): TaggedCache
    {
        return new TaggedCache($this, $tags);
    }

    /**
     * 캐시 통계 가져오기
     */
    public function getStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? ($this->stats['hits'] / $total) * 100 : 0;
        
        return array_merge($this->stats, [
            'hit_rate' => round($hitRate, 2),
            'driver' => $this->config['driver']
        ]);
    }

    /**
     * 캐시 정보 가져오기
     */
    public function getInfo(): array
    {
        return [
            'driver' => $this->config['driver'],
            'prefix' => $this->config['prefix'],
            'default_ttl' => $this->config['default_ttl'],
            'stats' => $this->getStats()
        ];
    }
}

/**
 * 파일 캐시 드라이버
 */
class FileCacheDriver
{
    private string $path;
    private string $extension;

    public function __construct(array $config)
    {
        $this->path = $config['path'];
        $this->extension = $config['extension'];
        
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function set(string $key, $value, int $ttl): bool
    {
        $filename = $this->getFilename($key);
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl,
            'created_at' => time()
        ];
        
        return file_put_contents($filename, serialize($data)) !== false;
    }

    public function get(string $key)
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        $data = unserialize(file_get_contents($filename));
        
        if (!$data || !isset($data['expires_at']) || time() > $data['expires_at']) {
            unlink($filename);
            return null;
        }
        
        return $data['value'];
    }

    public function has(string $key): bool
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($filename));
        return $data && isset($data['expires_at']) && time() <= $data['expires_at'];
    }

    public function delete(string $key): bool
    {
        $filename = $this->getFilename($key);
        
        if (file_exists($filename)) {
            return unlink($filename);
        }
        
        return true;
    }

    public function deletePattern(string $pattern): int
    {
        $count = 0;
        $files = glob($this->path . '*' . $this->extension);
        
        foreach ($files as $file) {
            $key = basename($file, $this->extension);
            if (fnmatch($pattern, $key)) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    public function clear(): bool
    {
        $files = glob($this->path . '*' . $this->extension);
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }

    public function increment(string $key, int $value): int
    {
        $current = $this->get($key) ?? 0;
        $newValue = $current + $value;
        $this->set($key, $newValue, 3600);
        return $newValue;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, -$value);
    }

    private function getFilename(string $key): string
    {
        return $this->path . md5($key) . $this->extension;
    }
}

/**
 * Redis 캐시 드라이버
 */
class RedisCacheDriver
{
    private $redis;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    private function connect(): void
    {
        $this->redis = new \Redis();
        
        $connected = $this->redis->connect(
            $this->config['host'],
            $this->config['port']
        );
        
        if (!$connected) {
            throw new \Exception("Failed to connect to Redis");
        }
        
        if ($this->config['password']) {
            $this->redis->auth($this->config['password']);
        }
        
        $this->redis->select($this->config['database']);
    }

    public function set(string $key, $value, int $ttl): bool
    {
        $serialized = serialize($value);
        return $this->redis->setex($key, $ttl, $serialized);
    }

    public function get(string $key)
    {
        $value = $this->redis->get($key);
        
        if ($value === false) {
            return null;
        }
        
        return unserialize($value);
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($key);
    }

    public function delete(string $key): bool
    {
        return $this->redis->del($key) > 0;
    }

    public function deletePattern(string $pattern): int
    {
        $keys = $this->redis->keys($pattern);
        
        if (empty($keys)) {
            return 0;
        }
        
        return $this->redis->del($keys);
    }

    public function clear(): bool
    {
        return $this->redis->flushDB();
    }

    public function increment(string $key, int $value): int
    {
        return $this->redis->incrBy($key, $value);
    }

    public function decrement(string $key, int $value): int
    {
        return $this->redis->decrBy($key, $value);
    }
}

/**
 * 메모리 캐시 드라이버
 */
class MemoryCacheDriver
{
    private array $cache = [];
    private array $expires = [];
    private int $maxItems;

    public function __construct(array $config)
    {
        $this->maxItems = $config['max_items'];
    }

    public function set(string $key, $value, int $ttl): bool
    {
        // 최대 아이템 수 체크
        if (count($this->cache) >= $this->maxItems && !isset($this->cache[$key])) {
            $this->evictOldest();
        }
        
        $this->cache[$key] = $value;
        $this->expires[$key] = time() + $ttl;
        
        return true;
    }

    public function get(string $key)
    {
        if (!isset($this->cache[$key])) {
            return null;
        }
        
        if (time() > $this->expires[$key]) {
            $this->delete($key);
            return null;
        }
        
        return $this->cache[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->cache[$key]) && time() <= $this->expires[$key];
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expires[$key]);
        return true;
    }

    public function deletePattern(string $pattern): int
    {
        $count = 0;
        
        foreach (array_keys($this->cache) as $key) {
            if (fnmatch($pattern, $key)) {
                $this->delete($key);
                $count++;
            }
        }
        
        return $count;
    }

    public function clear(): bool
    {
        $this->cache = [];
        $this->expires = [];
        return true;
    }

    public function increment(string $key, int $value): int
    {
        $current = $this->get($key) ?? 0;
        $newValue = $current + $value;
        $this->set($key, $newValue, 3600);
        return $newValue;
    }

    public function decrement(string $key, int $value): int
    {
        return $this->increment($key, -$value);
    }

    private function evictOldest(): void
    {
        if (empty($this->expires)) {
            return;
        }
        
        $oldestKey = array_keys($this->expires, min($this->expires))[0];
        $this->delete($oldestKey);
    }
}

/**
 * 태그된 캐시
 */
class TaggedCache
{
    private CacheManager $cache;
    private array $tags;

    public function __construct(CacheManager $cache, array $tags)
    {
        $this->cache = $cache;
        $this->tags = $tags;
    }

    public function set(string $key, $value, int $ttl = null): bool
    {
        $taggedKey = $this->getTaggedKey($key);
        return $this->cache->set($taggedKey, $value, $ttl);
    }

    public function get(string $key, $default = null)
    {
        $taggedKey = $this->getTaggedKey($key);
        return $this->cache->get($taggedKey, $default);
    }

    public function has(string $key): bool
    {
        $taggedKey = $this->getTaggedKey($key);
        return $this->cache->has($taggedKey);
    }

    public function delete(string $key): bool
    {
        $taggedKey = $this->getTaggedKey($key);
        return $this->cache->delete($taggedKey);
    }

    public function flush(): bool
    {
        foreach ($this->tags as $tag) {
            $this->cache->deletePattern("tag_{$tag}_*");
        }
        return true;
    }

    private function getTaggedKey(string $key): string
    {
        $tagString = implode('_', $this->tags);
        return "tag_{$tagString}_{$key}";
    }
} 