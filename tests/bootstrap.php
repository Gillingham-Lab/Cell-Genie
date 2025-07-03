<?php
declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

(new Filesystem())->remove(__DIR__.'/../var/cache/test');

passthru(sprintf(
    "php %s/../bin/console doctrine:database:drop --quiet --force -e %s",
    __DIR__,
    $_ENV["APP_ENV"],
));

passthru(sprintf(
    "php %s/../bin/console doctrine:database:create --quiet -e %s",
    __DIR__,
    $_ENV["APP_ENV"],
));

passthru(sprintf(
    "php %s/../bin/console doctrine:schema:update --quiet --complete --force -e %s",
    __DIR__,
    $_ENV["APP_ENV"],
));

passthru(sprintf(
    "php %s/../bin/console doctrine:fixtures:load --purge-with-truncate --quiet -e %s",
    __DIR__,
    $_ENV["APP_ENV"],
));
