<?php


namespace Repositories;


use PDO;

abstract class AbstractUserRepository
{
    /**
     * @var PDO
     */
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function authorization($login, $password)
    {
        $query = $this->db->prepare('select * from users 
                                    where login = ? 
                                    and password = ? ');
        $query->execute([$login, $password]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllItems()
    {
        $query = $this->db->prepare('select name "item" from items');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatusExchange()
    {
        return $this->executeStatement('select commission, count(distinct items.id) "items", count(distinct orders.id) "orders"
                                        from exchanges, items, orders
                                        group by commission');
    }

    private function executeStatement(string $statement)
    {
        $query = $this->db->prepare($statement);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getPeriodRevenue($from, $to, $login)
    {
        $query = $this->db->prepare('select login, sum(price) "sum" from journal
                                    join users u on journal.seller_id = u.id
                                    where date between str_to_date(?, \'%d.%m.%Y\') and str_to_date(?, \'%d.%m.%Y\')
                                    and login = ?');
        $query->execute([$from, $to, $login]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    //TODO: Продумать лучше!

    public function getPeriodTopItems($from, $to)
    {
        $query = $this->db->prepare('select name, count(j.item_id) "count" from items
                                    join journal j on items.id = j.item_id
                                    where date between str_to_date(?, \'%d.%m.%Y\') and str_to_date(?, \'%d.%m.%Y\')
                                    and seller_id is not null
                                    and buyer_id is not null
                                    group by name
                                    order by count(j.item_id) desc');
        $query->execute([$from, $to]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPeriodTopUsers($from, $to)
    {
        $query = $this->db->prepare('select login, sum(price) "revenue", sum(item_id) "items" from users
                                    join journal j on users.id = j.buyer_id
                                    where login = seller_id
                                    and date between str_to_date(?, \'%d.%m.%Y\') and str_to_date(?, \'%d.%m.%Y\')
                                    and seller_id is not null
                                    and buyer_id is not null
                                    group by login
                                    order by "revenue", "items"');
        $query->execute([$from, $to]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUser($userId)
    {
        $query = $this->db->prepare('select * from users where id = ?');
        $query->execute([$userId]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $query = $this->db->prepare('select * from inventories where user_id = ?');
        $query->execute([$userId]);
        $inventory = $query->fetchAll(PDO::FETCH_ASSOC);
        return array('user' => $user, 'inventory' => $inventory);
    }

    public function getUserByLogin($login){
        $query = $this->db->prepare('select * from users where login = ?');
        $query->execute([$login]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $query = $this->db->prepare('select * from inventories where user_id = ?');
        $query->execute([$user['id']]);
        $inventory = $query->fetchAll(PDO::FETCH_ASSOC);
        return array('user' => $user, 'inventory' => $inventory);
    }
}