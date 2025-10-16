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
            'app_base_path' => $_ENV['APP_BASE_PATH'] ?? '',
            'assets_dev_url' => $_ENV['ASSETS_DEV_URL'] ?? 'http://localhost',
            'db.host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db.database' => $_ENV['DB_DATABASE'] ?? 'database',
            'db.username' => $_ENV['DB_USERNAME'] ?? 'root',
            'db.password' => $_ENV['DB_PASSWORD'] ?? ''
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
