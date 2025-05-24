<?php require __DIR__ . '/../vendor/autoload.php';

$twig->getEnvironment()->addGlobal('currentUserId', $_SESSION['user_id']);
$twig->getEnvironment()->addGlobal('currentUsername', $_SESSION['username']);
