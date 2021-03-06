<?php

declare(strict_types=1);

// report all errors
error_reporting(-1);

// require composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

(function (): void {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
})();
