<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class AuthController extends BaseController
{
    public function __construct(
        Twig $view,
        private AuthService $authService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($view);
    }

    public function showRegister(Request $request, Response $response): Response
    {
        // TODO: you also have a logger service that you can inject and use anywhere; file is var/app.log
        $this->logger->info('Register page requested');

        return $this->render($response, 'auth/register.twig', [
            'username' => '',
            'password' => '',
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }

    public function register(Request $request, Response $response): Response
    {
        // TODO: call corresponding service to perform user registration
        $data = (array) $request->getParsedBody();

        if (
            empty($data['csrf_token']) ||
            $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')
        ) {
            $errors['form'] = 'Invalid CSRF token.';
            return $this->render($response, 'auth/register.twig', [
                'username' => $data['username'] ?? '',
                'errors' => $errors,
                'csrf_token' => $this->getCsrfToken(),
            ]);
        }

        $username = trim($data['username']);
        $password = trim($data['password']);

        $errors = [];

        if ($username == '' || $password == '') {
            $errors['username'] = 'Username or password is not completed';
        }

        if (strlen($username) < 4) {
            $errors['username'] = 'Username must be at least 4 characters';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (empty($errors)) {
            try {
                $this->authService->register($username, $password);
                return $response->withHeader('Location', '/register')->withStatus(302);
            } catch (\Exception $e) {
                $errors['username'] = $e->getMessage();
            }
        }

        return $this->render($response, 'auth/login.twig', [
            'username' => $username,
            'errors' => $errors,
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }

    public function showLogin(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the login page

        $this->logger->info('Login page requested');

        return $this->render($response, 'auth/login.twig', [
            'username' => '',
            'password' => '',
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }

    public function login(Request $request, Response $response): Response
    {
        // TODO: call corresponding service to perform user login, handle login failures
        $data = (array) $request->getParsedBody();

        if (
            empty($data['csrf_token']) ||
            $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')
        ) {
            $errors['form'] = 'Invalid CSRF token.';
            return $this->render($response, 'auth/login.twig', [
                'username' => $data['username'] ?? '',
                'errors' => $errors,
                'csrf_token' => $this->getCsrfToken(),
            ]);
        }

        $username = trim($data['username']);
        $password = trim($data['password']);

        $errors = [];

        try {
            $this->authService->attempt($username, $password);
            return $response->withHeader('Location', '/')->withStatus(302);
        } catch (\Exception $e) {
            $errors['form'] = $e->getMessage();
            $this->logger->warning("Login failed for user '{$username}': " . $e->getMessage());
        }

        return $this->render($response, 'auth/login.twig', [
            'username' => $username,
            'errors' => $errors,
            'csrf_token' => $this->getCsrfToken(),
        ]);
    }

    public function logout(Request $request, Response $response): Response
    {
        // TODO: handle logout by clearing session data and destroying session

        //Deleting the session
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);

        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['csrf_token']);

        session_destroy();

        return $response->withHeader('Location', '/login')->withStatus(302);
    }
}
