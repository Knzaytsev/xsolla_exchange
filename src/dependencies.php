<?php
declare(strict_types=1);

use Interactors\AdministratorInteractor;
use Interactors\GeneralUserInteractor;
use Interactors\UserInteractor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Presenters\AdministratorPresenter;
use Presenters\GeneralUserPresenter;
use Presenters\UserPresenter;
use Repositories\AdministratorRepository;
use Repositories\UserRepository;
use Slim\App;
use Slim\Views\PhpRenderer;

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new Logger($settings['name']);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    $container['db'] = function ($c) {
        $db = $c->get('settings')['db'];
        $pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['username'], $db['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    };

    $container['userRepository'] = function ($c) {
        $db = $c->get('db');
        return new UserRepository($db);
    };

    $container['userInteractor'] = function ($c) {
        $user = $c->get('userRepository');
        return new UserInteractor($user);
    };

    $container['userPresenter'] = function ($c) {
        $user = $c->get('userInteractor');
        return new UserPresenter($user);
    };

    $container['adminRepository'] = function ($c) {
        $db = $c->get('db');
        return new AdministratorRepository($db);
    };

    $container['adminInteractor'] = function ($c) {
        $admin = $c->get('adminRepository');
        return new AdministratorInteractor($admin);
    };

    $container['adminPresenter'] = function ($c) {
        $admin = $c->get('adminInteractor');
        return new AdministratorPresenter($admin);
    };
};
