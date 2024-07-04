<?php
declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

(new \Symfony\Component\Filesystem\Filesystem())->remove(__DIR__.'/../var/cache/test');