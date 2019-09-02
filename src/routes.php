<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;


return function (App $app) {
    $container = $app->getContainer();

    //Общие методы
    //-----------------
    /**
     * Аавторизация
     */
    $app->post('/exchange/authorization', function (Request $request, Response $response) use ($container) {
        $userPresenter = $container['userPresenter'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $login = $data['login'];
        $password = $data['password'];
        $result = $userPresenter->executeAuthorization($login, $password);
        if (empty($result)) {
            return $response->write('Не удалось авторизироваться.');
        }
        $_SESSION['user_name'] = $login;
        return $response->withJson($result);
    });

    /**
     * Выход из системы
     */
    $app->post('/exchange/logout', function (Request $request, Response $response) use ($container) {
        unset($_SESSION['user_name']);
        return $response->write('Вы вышли из аккаунта.');
    });

    /**
     * Получение списка предметов
     */
    $app->get('/exchange/items', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $result = $userPresenter->getItems();
        return $response->withJson($result);
    });

    /**
     * Получение состояния биржи
     */
    $app->get('/exchange/status', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $result = $userPresenter->getStatusExchange();
        return $response->withJson($result);
    });

    /**
     * Получение выручки за период
     */
    $app->get('/exchange/revenue', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $login = $_SESSION['user_name'];
        $from = $data['from'];
        $to = $data['to'];
        $result = $userPresenter->getPeriodRevenue($from, $to, $login);
        return $response->withJson($result);
    });

    /**
     * Получение продаваемых предметов за период
     */
    $app->get('/exchange/items/top', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $from = $data['from'];
        $to = $data['to'];
        $result = $userPresenter->getTopItems($from, $to);
        return $response->withJson($result);
    });

    /**
     * Получение топа пользователей за период
     */
    $app->get('/exchange/users/top', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $from = $data['from'];
        $to = $data['to'];
        $result = $userPresenter->getTopUsers($from, $to);
        return $response->withJson($result);
    });

    /**
     * Получение информации о пользователе
     */
    //TODO: поиск по логину!
    $app->get('/exchange/users/information', function (Request $request, Response $response, $args) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $login = $_SESSION['user_name'];
        $result = $userPresenter->getUserByLogin($login);
        return $response->withJson($result);
    });
    //-----------------------

    //Методы админа биржи
    //------------------------------
    /**
     * Пополнение баланса
     */
    //TODO: учёт админа!
    $app->post('/exchange/users/{id}/balance/add', function (Request $request, Response $response, array $args) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $adminPresenter = $container['adminPresenter'];
        $login = $_SESSION['user_name'];
        $isAdmin = $adminPresenter->isAdmin($login);
        if($isAdmin === false){
            return $response->write('У Вас недостаточно прав!');
        }
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $sum = $data['sum'];
        $result = $adminPresenter->addBalance($args['id'], $sum);
        if ($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Сумма успешно добавлена!');
    });

    /**
     * Списание со счёта
     */
    $app->post('/exchange/users/{id}/balance/subtract', function (Request $request, Response $response, array $args) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $adminPresenter = $container['adminPresenter'];
        $login = $_SESSION['user_name'];
        $isAdmin = $adminPresenter->isAdmin($login);
        if($isAdmin === false){
            return $response->write('У Вас недостаточно прав!');
        }
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $sum = $data['sum'];
        $result = $adminPresenter->subtractBalance($args['id'], $sum);
        if ($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Сумма успешно списана!');
    });

    /**
     * Создание типа предмета
     */
    $app->post('/exchange/items/create', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $adminPresenter = $container['adminPresenter'];
        $login = $_SESSION['user_name'];
        $isAdmin = $adminPresenter->isAdmin($login);
        if($isAdmin === false){
            return $response->write('У Вас недостаточно прав!');
        }
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $name = $data['name'];
        $result = $adminPresenter->createItem($name);
        if($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Предмет успешно добавлен!');
    });

    /**
     * Начисление предмета
     */
    $app->post('/exchange/items/{item}/set/users/{user}', function (Request $request, Response $response, array $args) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $adminPresenter = $container['adminPresenter'];
        $login = $_SESSION['user_name'];
        $isAdmin = $adminPresenter->isAdmin($login);
        if($isAdmin === false){
            return $response->write('У Вас недостаточно прав!');
        }
        $result = $adminPresenter->setItem($args['user'], $args['item']);
        if($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Предмет успешно добавлен!');
    });

    /**
     * Изменение комиссии
     */
    $app->post('/exchange/commission/change', function (Request $request, Response $response, array $args) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $adminPresenter = $container['adminPresenter'];
        $login = $_SESSION['user_name'];
        $isAdmin = $adminPresenter->isAdmin($login);
        if($isAdmin === false){
            return $response->write('У Вас недостаточно прав!');
        }
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $commission = $data['commission'];
        $result = $adminPresenter->changeCommission($commission);
        if($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Комиссия успешно изменена!');
    });

    /**
     * Получение баланса биржи
     */
    $app->get('/exchange/balance', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $adminPresenter = $container['adminPresenter'];
        $login = $_SESSION['user_name'];
        $isAdmin = $adminPresenter->isAdmin($login);
        if($isAdmin === false){
            return $response->write('У Вас недостаточно прав!');
        }
        $result = $adminPresenter->getBalance();
        if(empty($result)){
            return $response->write('Что-то пошло не так.');
        }
        return $response->withJson($result);
    });
    //----------------------

    //Методы пользователя
    //---------------------
    /**
     * Регистрация
     */
    $app->post('/exchange/registration', function (Request $request, Response $response) use ($container) {
        unset($_SESSION['user_name']);
        $userPresenter = $container['userPresenter'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $login = $data['login'];
        $password = $data['password'];
        $result = $userPresenter->executeRegistration($login, $password);
        if ($result === false) {
            return $response->write('Не удалось зарегистрироваться.');
        }
        $_SESSION['user_name'] = $login;
        return $response->write('Регистрация прошла успешно!');
    });

    /**
     * Создание ордера на покупку
     */
    $app->post('/exchange/orders/create/buy/items/{id}', function (Request $request, Response $response, array $args) use($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $login = $_SESSION['user_name'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $item = $args['id'];
        $price = $data['price'];
        $result = $userPresenter->createBuyOrder($login, $item, $price);
        if($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Ордер создан!');
    });

    /**
     * Создание ордера на продажу
     */
    $app->post('/exchange/orders/create/sell/inventory/{id}', function (Request $request, Response $response, array $args) use($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $login = $_SESSION['user_name'];
        $jsonData = $request->getBody()->getContents();
        $data = json_decode($jsonData, true);
        $price = $data['price'];
        $inventoryId = $args['id'];
        $result = $userPresenter->createSellOrder($login, $inventoryId, $price);
        if($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Ордер создан!');
    });

    /**
     * Покупка предмета
     */
    $app->post('/exchange/orders/{id}/buy', function (Request $request, Response $response, array $args) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $login = $_SESSION['user_name'];
        $orderId = $args['id'];
        $result = $userPresenter->buyItem($login, $orderId);
        if($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Предмет куплен!');
    });

    /**
     * Получение ордеров на продажу
     */
    $app->get('/exchange/orders/sales', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $result = $userPresenter->getSales();
        return $response->withJson($result);
    });

    /**
     * Получение ордеров на покупку
     */
    $app->get('/exchange/orders/purchases', function (Request $request, Response $response) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $result = $userPresenter->getPurchases();
        return $response->withJson($result);
    });

    /**
     * Отмена ордера
     */
    $app->post('/exchange/orders/{id}/cancel', function (Request $request, Response $response, array $args) use ($container) {
        if (!isset($_SESSION['user_name'])) {
            return $response->write('Вы не авторизировались.');
        }
        $userPresenter = $container['userPresenter'];
        $result = $userPresenter->cancelOrder($args['id']);
        if($result === false){
            return $response->write('Что-то пошло не так.');
        }
        return $response->write('Удаление прошло успешно!');
    });
};
