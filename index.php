<?php

namespace pelish8;

require 'vendor/autoload.php';

$view = Configuration::SLIM_VIEW;
$debug = Configuration::SLIM_DEBUG;
if (!$debug) {
    // Twig: added cache path
    $view::$twigOptions = [
        'cache' => Configuration::SLIN_CACHE
    ];
}

$app = new \Slim\Slim([
    'debug' => $debug,
    'templates.path' => Configuration::SLIM_TEMPLATE_PATH,
    'view' => $view
]);

// home page
$app->get('/', function () use ($app) {
    $page = new Page\Home();
    $app->render($page->template(), $page->userData());
});

// log in page
$app->get('/login', function () use ($app) {
    if (Session::sharedSession()->isLogIn()) {
        $app->redirect('/');
        return;
    }

    $page = new Page\Login();
    $app->render($page->template(), $page->userData());
});

// log out page
$app->get('/logout', function () use ($app) {
    Session::sharedSession()->userLogOut();
    $app->redirect('/');
});

// register new user
$app->get('/register', function () use ($app) {

    $page = new Page\Register();
    $app->render($page->template(), $page->userData()); //, $page->responseStatus);
});

// create new article
$app->get('/create', function () use ($app) {
    if (!Session::sharedSession()->isLogIn()) {
        $app->redirect('/login');
        return;
    }

    $page = new Page\Create();
    $app->render($page->template(), $page->userData());
});

// install aplication
$app->get('/install', function () {
    // @todo implement
});

// article details
$app->get('/:date/:time/:title', function ($date, $time, $title) use ($app) {
    $page = new Page\Article($date, $time, $title);
    $app->render($page->template(), $page->userData());
});


// get api calls
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

// post api calls
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

// error page
$app->error(function (\Exception $e) use ($app) {
    $app->render('error.html');
});

// page not found
$app->notFound(function () use ($app) {
    $app->render('404.html');
});

$app->run();