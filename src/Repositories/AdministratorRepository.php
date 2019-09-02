<?php


namespace Repositories;


use PDO;
use PDOException;

class AdministratorRepository extends AbstractUserRepository
{
    public function addBalance($value, $idUser)
    {
        try {
            $query = $this->db->prepare('update users set balance = ? + balance
                                        where id = ?');
            $query->execute([$value, $idUser]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function subtractBalance($value, $idUser)
    {
        try {
            $query = $this->db->prepare('update users set balance = balance - ? 
                                        where id = ?');
            $query->execute([$value, $idUser]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function createItem($name)
    {
        try {
            $query = $this->db->prepare('insert into items (name) values (lower(?))');
            $query->execute([$name]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function setItem($userId, $itemId)
    {
        try {
            $query = $this->db->prepare('insert into inventories (user_id, item_id) 
                                    values (?, ?)');
            $query->execute([$userId, $itemId]);
            return true;
        } catch (PDOException $e){
            return false;
        }
    }

    public function changeCommission($commission)
    {
        try{
            $query = $this->db->prepare('update exchanges set commission = ?');
            $query->execute([$commission]);
            return true;
        } catch (PDOException $e){
            return false;
        }
    }

    public function getBalanceExchange()
    {
        $query = $this->db->prepare('select money from exchanges');
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }
}
