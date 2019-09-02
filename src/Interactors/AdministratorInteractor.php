<?php


namespace Interactors;


use Repositories\AdministratorRepository;

class AdministratorInteractor
{
    /**
     * @var AdministratorRepository
     */
    private $adminRepository;

    public function __construct($adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function addBalance($id, $sum)
    {
        return $this->adminRepository->addBalance($sum, $id);
    }

    public function subtractBalance($id, $sum)
    {
        $userWithInventory = $this->adminRepository->getUser($id);
        $balance = $userWithInventory['user']['balance'];
        if($balance < $sum){
            return false;
        }
        return $this->adminRepository->subtractBalance($sum, $id);
    }

    public function createItem($name)
    {
        return $this->adminRepository->createItem($name);
    }

    public function setItem($userId, $itemId){
        return $this->adminRepository->setItem($userId, $itemId);
    }

    public function changeCommission($commission){
        return $this->adminRepository->changeCommission($commission);
    }

    public function getBalance(){
        return $this->adminRepository->getBalanceExchange();
    }

    public function getPrivilege($login)
    {
        $user = $this->adminRepository->getUserByLogin($login);
        return $user['type_user_id'];
    }
}