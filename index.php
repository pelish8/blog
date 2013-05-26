<?php

namespace pelish8;

require 'vendor/autoload.php';


$app = new \Slim\Slim([
    'debug' => Configuration::SLIM_DEBUG,
    'templates.path' => Configuration::SLIM_TEMPLATE_PATH,
    'view' => Configuration::SLIM_VIEW
]);

$app->get('/', function () use ($app) {
    $page = new pages\HomePage();
    $app->render($page->template(), $page->userData());
});

$app->get('/login', function () use ($app) {
    if (Session::sharedSession()->isLogIn()) {
        $app->redirect('/');
        return;
    }
    
    $page = new \pelish8\pages\LoginPage();
    $app->render($page->template(), $page->userData());
});

$app->get('/logout', function () use ($app) {
    Session::sharedSession()->userLogOut();
    $app->redirect('/');
});

$app->get('/register', function () use ($app) {
    
    $page = new pages\RegisterPage();
    $app->render($page->template(), $page->userData()); //, $page->responseStatus);
});

$app->get('/create', function () use ($app) {
    if (!Session::sharedSession()->isLogIn()) {
        $app->redirect('/login');
        return;
    }
    
    $page = new pages\CreatePage();
    $app->render($page->template(), $page->userData());
});

$app->get('/install', function () {
    echo 'install';
});

// api calls
$app->get('/api/:action', function ($action) use ($app) {

    if (!$app->request()->isAjax()) {
        $app->notFound();
        return;
    }

    $response = $app->response();
    // set header
    $response['Content-Type'] = 'application/json';

    new Api($action);
});

$app->post('/api/:action', function ($action) use ($app) {

    if (!$app->request()->isAjax()) {
        $app->notFound();
        return;
    }

    $res = $app->response();
    // set header
    $res['Content-Type'] = 'application/json';

    new Api($action);
});

$app->error(function (\Exception $e) use ($app) {
    $app->render('error.html');
});

$app->notFound(function () use ($app) {
    $app->render('404.html');
});

$app->run();