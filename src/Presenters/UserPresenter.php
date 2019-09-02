<?php


namespace Presenters;


use Interactors\BuyOrder;
use Interactors\SellOrder;
use Interactors\UserInteractor;

class UserPresenter
{
    /**
     * @var UserInteractor
     */
    private $userInteractor;

    public function __construct($userInteractor)
    {
        $this->userInteractor = $userInteractor;
    }

    public function executeRegistration($login, $password)
    {
        return $this->userInteractor->registration($login, $password);
    }

    public function executeAuthorization($login, $password)
    {
        return $this->userInteractor->authorization($login, $password);
    }

    public function getItems()
    {
        return $this->userInteractor->getItems();
    }

    public function getStatusExchange()
    {
        return $this->userInteractor->getStatusExchange();
    }

    public function getPeriodRevenue($from, $to, $login)
    {
        return $this->userInteractor->getPeriodRevenue($from, $to, $login);
    }

    public function getTopItems($from, $to)
    {
        return $this->userInteractor->getTopItems($from, $to);
    }

    public function getTopUsers($from, $to)
    {
        return $this->userInteractor->getTopUsers($from, $to);
    }

    public function getUserByLogin($login)
    {
        return $this->userInteractor->getUserByLogin($login);
    }

    public function createBuyOrder($login, $item, $price){
        return $this->userInteractor->createBuyOrder($login, $item, $price);
    }

    public function createSellOrder($login, $inventoryId, $price) {
        return $this->userInteractor->createSellOrder($login, $inventoryId, $price);
    }

    public function buyItem($login, $orderId){
        return $this->userInteractor->buyItem($login, $orderId);
    }

    public function getSales(){
        return $this->userInteractor->getSaleOrders();
    }

    public function getPurchases(){
        return $this->userInteractor->getBuyOrders();
    }
    
    public function cancelOrder($orderId){
        return $this->userInteractor->cancelOrder($orderId);
    }
}