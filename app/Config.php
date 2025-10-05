<?php

declare(strict_types=1);

namespace App;

use Dotenv\Dotenv;

class Config
{
    private static array $config = [];

    public static function load(string $basePath): void
    {
        $dotenv = Dotenv::createImmutable($basePath);
        $dotenv->load();

        self::$config = [
            'app_env'   => $_ENV['APP_ENV'] ?? 'development',
            'app_base_path' => $_ENV['APP_BASE_PATH'] ?? ''
        ];
    }

    public static function get(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    public static function isProduction(): bool
    {
        return self::$config['app_env'] === 'production';
    }
}
