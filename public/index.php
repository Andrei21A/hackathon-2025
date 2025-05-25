<?php

declare(strict_types=1);

session_start();

// initializing class autoloader
require __DIR__ . '/../vendor/autoload.php';

use App\Kernel;
use Dotenv\Dotenv;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// loading .env file variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// creating web application FIRST
$app = Kernel::createApp();

// THEN set up twig globals
$container = $app->getContainer();
$twig = $container->get(\Slim\Views\Twig::class);

$twig->getEnvironment()->addGlobal('currentUserId', $_SESSION['user_id'] ?? null);
$twig->getEnvironment()->addGlobal('currentUserName', $_SESSION['username'] ?? '');

// running the app
$app->run();
