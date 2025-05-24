<?php require __DIR__ . '/../vendor/autoload.php';

$twig->getEnvironment()->addGlobal('currentUserId', $_SESSION['user_id'] ?? null);
$twig->getEnvironment()->addGlobal('currentUserName', $_SESSION['username'] ?? '');
