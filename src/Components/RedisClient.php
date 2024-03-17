<?php

namespace SevereHeadache\OtusHaCounters\Components;

class RedisClient
{
    private \Redis $redis;

    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect(env('REDIS_HOST'), env('REDIS_PORT'));
        $this->redis->auth(env('REDIS_PASSWORD'));
    }

    public function __call($name, $arguments)
    {
        return $this->redis->$name(...$arguments);
    }
}
