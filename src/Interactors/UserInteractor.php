<?php

namespace Interactors;

use Repositories\UserRepository;

class UserInteractor
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct($userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function registration($login, $password)
    {
        return $this->userRepository->registration($login, $password);
    }

    public function authorization($login, $password)
    {
        return $this->userRepository->authorization($login, $password);
    }

    public function getItems()
    {
        return $this->userRepository->getAllItems();
    }

    public function getStatusExchange()
    {
        return $this->userRepository->getStatusExchange();
    }

    public function getPeriodRevenue($from, $to, $login)
    {
        return $this->userRepository->getPeriodRevenue($from, $to, $login);
    }

    public function getTopItems($from, $to)
    {
        return $this->userRepository->getPeriodTopItems($from, $to);
    }

    public function getTopUsers($from, $to)
    {
        return $this->userRepository->getPeriodTopUsers($from, $to);
    }

    public function getUserById($id)
    {
        return $this->userRepository->getUser($id);
    }

    public function createBuyOrder($login, $item, $price)
    {
        $userWithInventory = $this->userRepository->getUserByLogin($login);
        $userId = $userWithInventory['user']['id'];
        $balance = $userWithInventory['user']['balance'];
        if ($balance < $price) {
            return false;
        }
        return $this->userRepository->createBuyOrder($userId, $item, $price);
    }

    public function createSellOrder($login, $inventoryId, $price)
    {
        $inventory = $this->userRepository->getInventory($inventoryId);
        $item = $inventory['item_id'];
        $checkItem = $this->userRepository->checkItem($item);
        if (!empty($checkItem))
            return false;
        return $this->userRepository->createSellOrder($inventoryId, $price);
    }

    public function buyItem($login, $orderId)
    {
        $userWithInventory = $this->userRepository->getUserByLogin($login);
        $user = $userWithInventory['user'];
        $buyerId = $user['id'];
        $balance = $user['balance'];
        $order = $this->userRepository->getOrder($orderId);
        if (empty($order)){
            return false;
        }
        $price = $order['price'];
        $sellerId = $order['seller_id'];
        if ($balance < $price || $buyerId === $sellerId){
            return false;
        }
        return $this->userRepository->buyItem($orderId, $buyerId);
    }

    public function getSaleOrders()
    {
        return $this->userRepository->getSellOrders();
    }

    public function getBuyOrders()
    {
        return $this->userRepository->getBuyOrders();
    }

    public function cancelOrder($orderId)
    {
        return $this->userRepository->cancelOrder($orderId);
    }

    public function getUserByLogin($login)
    {
        return $this->userRepository->getUserByLogin($login);
    }
}