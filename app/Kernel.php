<?php

declare(strict_types=1);

namespace App;

use App\Domain\Repository\ExpenseRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Persistence\PdoExpenseRepository;
use App\Infrastructure\Persistence\PdoUserRepository;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PDO;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

use function DI\autowire;
use function DI\factory;

class Kernel
{
    public static function createApp(): App
    {
        // Start session early
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Load environment variables first
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }

        // Configure the DI container builder and build the DI container
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);  // Enable autowiring explicitly

        $builder->addDefinitions([
                // Define a factory for the Monolog logger with a stream handler that writes to var/app.log
            LoggerInterface::class => function () {
                $logger = new Logger('app');
                $logger->pushHandler(new StreamHandler(__DIR__ . '/../var/app.log', Level::Debug));
                return $logger;
            },

                // Define a factory for Twig view renderer
            Twig::class => function () {
                return Twig::create(__DIR__ . '/../templates', ['cache' => false]);
            },

                // Define a factory for PDO database connection
            PDO::class => factory(function () {
                static $pdo = null;
                if ($pdo === null) {
                    $dbPath = $_ENV['DB_PATH'] ?? 'database/db.sqlite';

                    if (!str_starts_with($dbPath, '/')) {
                        $dbPath = __DIR__ . '/../' . $dbPath;
                    }

                    $pdo = new PDO('sqlite:' . $dbPath);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                }
                return $pdo;
            }),

                // Map interfaces to concrete implementations
            UserRepositoryInterface::class => autowire(PdoUserRepository::class),
            ExpenseRepositoryInterface::class => autowire(PdoExpenseRepository::class),
        ]);
        $container = $builder->build();

        // Create an application instance and configure
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // IMPORTANT: Middleware is executed in LIFO (Last In, First Out) order
        // This means the LAST middleware added will be executed FIRST

        // 1. Add flash message clearing middleware FIRST (executed LAST - after response)
        $app->add(function ($request, $handler) {
            $response = $handler->handle($request);

            // Clear flash messages AFTER the response has been generated
            if (isset($_SESSION['flash_success'])) {
                unset($_SESSION['flash_success']);
            }
            if (isset($_SESSION['flash_error'])) {
                unset($_SESSION['flash_error']);
            }

            return $response;
        });

        // 2. Add Twig Middleware LAST (executed FIRST - before response generation)
        $app->add(TwigMiddleware::createFromContainer($app, Twig::class));

        // Add session globals to Twig environment
        $twig = $container->get(Twig::class);
        $twig->getEnvironment()->addGlobal('session', $_SESSION);

        (require __DIR__ . '/../config/settings.php')($app);
        (require __DIR__ . '/../config/routes.php')($app);

        // Add current logged in user ID to Twig globals
        $loggedInUserId = $_SESSION['user_id'] ?? null;
        $twig->getEnvironment()->addGlobal('currentUserId', $loggedInUserId);

        return $app;
    }
}
