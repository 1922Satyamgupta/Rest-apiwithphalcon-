<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Application;
use Phalcon\Config;
use Phalcon\Loader;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$config = new Config([]);


require_once "../vendor/autoload.php";
$loader = new Loader();
$loader->registerNamespaces(
    [
        'Api\Models' => './models/',
    ]
);
$loader->register();
// $application = new Application($container);
$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client("mongodb://mongo", array("username" => 'root', "password" => "password123"));

        return $mongo;
    },
    true
);
$prod = new Api\Models\Robots();
$app = new Micro($container);
$app->before(
    function () use ($app) {
        if (!str_contains($_SERVER['REQUEST_URI'], 'gettoken')) {
            $token = $app->request->getQuery("token");
            if (!$token) {
                echo 'Provide token in URL"';
                die;
            }
            $key = 'example_key';
            try {
                $decoded = JWT::decode($token, new Key($key, 'HS256'));

            } catch (\Firebase\JWT\ExpiredException $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
                die;
            }
            if ($decoded->role != 'admin') {
                echo 'You Are Not Authorized';
                die;
            }
        }
    }
);

$app->get(
    '/api/search/{name}',
    [
        $prod,
        'search'
    ]
);
$app->post(
    '/api/products/add',
    [
        $prod,
        'add'
    ]
);

$app->get(
    '/api/gettoken',
    [
        $prod,
        'gettoken'
    ]
);
$app->get(
    '/api/getpage/{per_page}/{page}',
    [
        $prod,
        'getpage'
    ]
);
$app->handle(
    $_SERVER["REQUEST_URI"]
);

