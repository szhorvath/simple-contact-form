<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../vendor/autoload.php';

$app = new App\App;

$container = $app->getContainer();

$container['session'] = function () {
    return new App\Session();
};

$container['errorHandler'] = function () {
    return function ($request, $response) {
        return $response->setBody('Page not found')->withStatus(404);
    };
};

$container['config'] = function () {
    return [
        'db_driver' => 'mysql',
        'db_host' => 'mysql',
        'db_name' => 'townsend',
        'db_user' => 'root',
        'db_pass' => 'secret',
        'admin_email' => 'admin@townsend.com',
    ];
};

$container['db'] = function ($c) {
    return new PDO(
        $c->config['db_driver'] . ':host=' . $c->config['db_host'] . ';dbname=' . $c->config['db_name'],
        $c->config['db_user'],
        $c->config['db_pass']
    );
};

$container['view'] = function () {
    $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../resources/views');
    return new \Twig\Environment($loader);
};

$container['validator'] = function ($c) {
    $errorHandler = new App\Validation\ErrorHandler;
    return new App\Validation\Validator($errorHandler, $c->session);
};

$app->get('/', [new App\Controllers\ContactController($container), 'index']);
$app->post('/store', [new App\Controllers\ContactController($container), 'store']);
$app->get('/sent', [new App\Controllers\ContactController($container), 'sent']);

$app->run();
