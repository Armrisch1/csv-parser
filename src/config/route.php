<?php
use src\core\Router;
use src\controllers\UserController;

$router = new Router();
$router->post('/save-csv-users', [UserController::class, 'saveCsvUsers']);
$router->post('/send-newsletters', [UserController::class, 'sendNewsletters']);
$router->dispatch();