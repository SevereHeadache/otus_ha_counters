<?php

namespace SevereHeadache\OtusHaCounters\Helpers;

use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use PhpOption\Option;

class Env
{
    /**
     * The environment repository instance.
     *
     * @var \Dotenv\Repository\RepositoryInterface|null
     */
    protected static $repository;

    /**
     * Get the environment repository instance.
     *
     * @return \Dotenv\Repository\RepositoryInterface
     */
    public static function getRepository()
    {
        if (static::$repository === null) {
            self::loadEnv();
            $builder = RepositoryBuilder::createWithDefaultAdapters();
            $builder = $builder->addAdapter(PutenvAdapter::class);

            static::$repository = $builder->immutable()->make();
        }

        return static::$repository;
    }

    protected static function loadEnv()
    {
        if (is_file(ENV_FILE)) {
            $env = file_get_contents(ENV_FILE);
            foreach(explode(PHP_EOL, $env) as $line) {
                if (!str_contains($line, '#')) {
                    \putenv($line);
                }
            }
        }
    }

    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Option::fromValue(static::getRepository()->get($key))
            ->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return;
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            })
            ->getOrCall(function () use ($default) {
                return $default;
            });
    }
}
