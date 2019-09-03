<?php


namespace Repositories;


use PDO;
use PDOException;

class AbstractUserRepository implements IGettingInfo, IEnteringExchange
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

    public function registration($login, $password)
    {
        try {
            $query = $this->db->prepare('insert into users (login, password, type_user_id) 
                                        values (?, ?, ?)');
            return $query->execute([$login, $password, 1]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getStatusExchange()
    {
        $query = $this->db->prepare('select commission, count(distinct items.id) "items", count(distinct orders.id) "orders"
                                    from exchanges, items, orders
                                    group by commission');
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
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

    public function getItems()
    {
        $query = $this->db->prepare('select id, name "item" from items');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRevenuePeriod($from, $to)
    {
        $query = $this->db->prepare('select login, sum(price) "sum" from journal
                                    join users u on journal.seller_id = u.id
                                    where date between str_to_date(?, \'%d.%m.%Y\') and str_to_date(?, \'%d.%m.%Y\')');
        $query->execute([$from, $to]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getTopItemsPeriod($from, $to)
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

    public function getTopUsersPeriod($from, $to)
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

    public function getUserById($id)
    {
        $query = $this->db->prepare('select * from users where id = ?');
        $query->execute([$id]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        $query = $this->db->prepare('select * from inventories where user_id = ?');
        $query->execute([$id]);
        $inventory = $query->fetchAll(PDO::FETCH_ASSOC);
        return array('user' => $user, 'inventory' => $inventory);
    }
}