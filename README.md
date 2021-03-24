# Cell-Genie
A php web tool to manage cells and their genealogy.

Requires PHP 8.0 or later.

## Installation for development
Make sure `php` and `composer` are available.

```shell
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
composer require --dev doctrine/doctrine-fixtures-bundle
```