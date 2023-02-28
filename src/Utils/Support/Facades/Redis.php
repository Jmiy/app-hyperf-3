<?php

namespace App\Utils\Support\Facades;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Redis\RedisFactory;

class Redis
{
    /**
     * 获取redis连接
     * @param string $poolName 连接池
     * @return \Hyperf\Redis\RedisProxy
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     */
    public static function getRedis(string $poolName = 'default')
    {
        $container = ApplicationContext::getContainer();
        $redisFactory = $container->get(RedisFactory::class);

        $db = config('redis.' . $poolName . '.db');
        $redis = $redisFactory->get($poolName);
        $redis->select($db);

        return $redis;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        return static::getRedis()->{$method}(...$args);
    }

    /**
     * Adds a values to the set value stored at key. 添加集合元素 支持设置缓存时长
     *
     * @param $key 集合key
     * @param array $value 集合元素
     * @param int|null $seconds 缓存时间  单位秒(支持：0.02)
     * @param string $poolName 连接池
     * @return array|bool|int The number of elements added to the set.
     * If this value is already in the set, FALSE is returned
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     *
     * @link    https://redis.io/commands/sadd
     * @example
     * <pre>
     * $redis->sAdd('k', 'v1');                // int(1)
     * $redis->sAdd('k', 'v1', 'v2', 'v3');    // int(2)
     * </pre>
     */
    public static function sAdd($key, array $value, int $seconds = null, string $poolName = 'default')
    {
        $instance = static::getRedis($poolName);

        if ($seconds === null) {
            return $instance->sAdd($key, ...$value);
        }

        $instance->multi();
        $instance->sAdd($key, ...$value);
        $instance->pexpire($key, $seconds * 1000);
        $manyResult = $instance->exec();

        return [
            'result' => data_get($manyResult, 0),
            'pexpire' => data_get($manyResult, 1),
        ];
    }

    /**
     * Fills in a whole hash. Non-string values are converted to string, using the standard (string) cast.
     * NULL values are stored as empty strings
     *
     * @param $key
     * @param array $dictionary key → value array
     * @param int|null $seconds 缓存时间  单位秒(支持：0.02)
     * @param string $poolName 连接池
     * @return array|bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Throwable
     *
     * @link    https://redis.io/commands/hmset
     * @example
     * <pre>
     * $redis->del('user:1');
     * $redis->hMSet('user:1', array('name' => 'Joe', 'salary' => 2000));
     * $redis->hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.
     * </pre>
     */
    public static function hmset($key, array $dictionary, int $seconds = null, string $poolName = 'default')
    {
        $instance = static::getRedis($poolName);

        if ($seconds === null) {
            return $instance->hmset($key, $dictionary);
        }

        $instance->multi();
        $instance->hmset($key, $dictionary);
        $instance->pexpire($key, $seconds * 1000);
        $manyResult = $instance->exec();

        return [
            'result' => data_get($manyResult, 0),
            'pexpire' => data_get($manyResult, 1),
        ];
    }

    /**
     * Increments the value of a member from a hash by a given amount.
     *
     * @param string $key
     * @param string $hashKey
     * @param int    $value (integer) value that will be added to the member's value
     * @param int|null $seconds 缓存时间  单位秒(支持：0.02)
     * @param string $poolName 连接池
     *
     * @return array|int the new value
     *
     * @link    https://redis.io/commands/hincrby
     * @example
     * <pre>
     * $redis->del('h');
     * $redis->hIncrBy('h', 'x', 2); // returns 2: h[x] = 2 now.
     * $redis->hIncrBy('h', 'x', 1); // h[x] ← 2 + 1. Returns 3
     * </pre>
     */
    public static function hIncrBy($key, $hashKey, $value, int $seconds = null, string $poolName = 'default')
    {
        $instance = static::getRedis($poolName);

        if ($seconds === null) {
            return $instance->hIncrBy($key, $hashKey, $value);
        }

        $instance->multi();
        $instance->hIncrBy($key, $hashKey, $value);
        $instance->pexpire($key, $seconds * 1000);
        $manyResult = $instance->exec();

        return [
            'result' => data_get($manyResult, 0),
            'pexpire' => data_get($manyResult, 1),
        ];
    }

}
