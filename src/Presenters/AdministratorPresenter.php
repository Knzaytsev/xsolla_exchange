<?php


namespace Presenters;


use Interactors\AdministratorInteractor;

class AdministratorPresenter
{
    /**
     * @var AdministratorInteractor
     */
    private $adminInteractor;

    public function __construct($adminInteractor)
    {
        $this->adminInteractor = $adminInteractor;
    }

    public function addBalance($id, $sum)
    {
        return $this->adminInteractor->addBalance($id, $sum);
    }

    public function subtractBalance($id, $sum)
    {
        if($sum < 0){
            return false;
        }
        return $this->adminInteractor->subtractBalance($id, $sum);
    }

    public function createItem($name)
    {
        return $this->adminInteractor->createItem($name);
    }

    //TODO: что-то сделать с условием!
    public function isAdmin($login){
        $privilege = $this->adminInteractor->getPrivilege($login);
        if ($privilege === 1)
            return false;
        return true;
    }

    public function setItem($userId, $itemId){
        return $this->adminInteractor->setItem($userId, $itemId);
    }

    public function changeCommission($commission){
        return $this->adminInteractor->changeCommission($commission);
    }

    public function getBalance(){
        return $this->adminInteractor->getBalance();
    }
}