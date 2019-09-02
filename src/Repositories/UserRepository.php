<?php


namespace Repositories;


use PDO;
use PDOException;
use phpDocumentor\Reflection\Type;

class UserRepository extends AbstractUserRepository
{
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

    public function getBuyOrders()
    {
        $query = $this->db->prepare('select buyer_id "buyer", name "item", price from orders
                                    join buy_order bo on orders.id = bo.id
                                    join items i on bo.item_id = i.id');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBuyOrdersPrice($price, $item){
        $query = $this->db->prepare('select orders.id "id", buyer_id "buyer", name "item", price from orders
                                    join buy_order bo on orders.id = bo.id
                                    join items i on bo.item_id = i.id
                                    where price >= ?
                                    and item_id = ?');
        $query->execute([$price, $item]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getSellOrders()
    {
        $query = $this->db->prepare('select login "seller", name "item", price from orders 
                                    join sell_order so on orders.id = so.id
                                    join inventories i on so.inventory_id = i.id
                                    join users u on i.user_id = u.id
                                    join items i2 on i.item_id = i2.id');
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSellOrdersPrice($price, $item){
        $query = $this->db->prepare('select orders.id "id", login "seller", name "item", price from orders 
                                    join sell_order so on orders.id = so.id
                                    join inventories i on so.inventory_id = i.id
                                    join users u on i.user_id = u.id
                                    join items i2 on i.item_id = i2.id
                                    where price <= ?
                                    and item_id = ?');
        $query->execute([$price, $item]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrder($id){
        $query = $this->db->prepare('select * from orders where id = ?');
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getItemInfo(int $orderId)
    {
        $query = $this->db->prepare('select * from orders where id = ?');
        $query->execute([$orderId]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function buyItem($orderId, $buyerId)
    {
        try{
            $this->db->beginTransaction();
            /**
             * Получение данных об ордере
             */
            $query = $this->db->prepare('select inventory_id, price, user_id, item_id from sell_order
                                        join orders o on sell_order.id = o.id
                                        join inventories i on sell_order.inventory_id = i.id
                                        where o.id = ?');
            $query->execute([$orderId]);
            $order = $query->fetch(PDO::FETCH_ASSOC);
            $price = $order['price'];
            $item = $order['item_id'];
            $inventoryId = $order['inventory_id'];
            $sellerId = $order['user_id'];
            /**
             * Получение комиссии
             */
            $exchange = $this->getStatusExchange();
            $commission = $exchange['commission'];
            $priceWithCommission = $price * (1 - $commission);
            $residue = $price * $commission;
            /**
             * Удаление ордера
             */
            $query = $this->db->prepare('delete from orders 
                                        where id in (select id from sell_order where inventory_id = ?)');
            $query->execute([$inventoryId]);
            /**
             * Передача предмета
             */
            $query = $this->db->prepare('update inventories set user_id = ?, count = count + 1 
                                        where id = ?');
            $query->execute([$buyerId, $inventoryId]);
            /**
             * Уменьшение баланса у покупателя
             */
            $query = $this->db->prepare('update users set balance = balance - ? where id = ?');
            $query->execute([$price, $buyerId]);
            /**
             * Увеличение баланса у продавца
             */
            $query = $this->db->prepare('update users set balance = balance + ? where id = ?');
            $query->execute([$priceWithCommission, $sellerId]);
            /**
             * Запись в журнал
             */
            $query = $this->db->prepare('insert into journal (seller_id, buyer_id, item_id, price, date)
                                        values (?, ?, ?, ?, now())');
            $query->execute([$sellerId, $buyerId, $item, $price]);
            /**
             * Увеличение кол-ва денег у биржи
             */
            $query = $this->db->prepare('update exchanges set money = money + ?');
            $query->execute([$residue]);
            $this->db->commit();
            return true;
        }catch (PDOException $e){
            print_r($e->errorInfo);
            $this->db->rollBack();
            return false;
        }
    }

    //TODO: продажа конкретного предмета из инвентаря!
    public function createSellOrder($inventory_id, $price)
    {
        try{
            $this->db->beginTransaction();
            $query = $this->db->prepare('insert into orders (price) values (?)');
            $query->execute([$price]);
            $lastId = $this->db->lastInsertId();
            $query = $this->db->prepare('insert into sell_order (id, inventory_id) values (?, ?)');
            $query->execute([$lastId, $inventory_id]);
            $query = $this->db->prepare('select user_id, item_id from inventories where id = ?');
            $query->execute([$inventory_id]);
            $inventoryInfo = $query->fetch(PDO::FETCH_ASSOC);
            $seller = $inventoryInfo['user_id'];
            $item = $inventoryInfo['item_id'];
            $query = $this->db->prepare('insert into journal (seller_id, item_id, price, date) values (?, ?, ?, now())');
            $query->execute([$seller, $item, $price]);
            $this->db->commit();
            return true;
        } catch (PDOException $e){
            $this->db->rollBack();
            return false;
        }
    }

    public function createBuyOrder($buyerId, $itemId, $price)
    {
        try{
            $this->db->beginTransaction();
            $query = $this->db->prepare('insert into orders (price) values (?)');
            $query->execute([$price]);
            $lastId = $this->db->lastInsertId();
            $query = $this->db->prepare('insert into buy_order (id, buyer_id, item_id) values (?, ?, ?)');
            $query->execute([$lastId, $buyerId, $itemId]);
            $query = $this->db->prepare('insert into journal (buyer_id, item_id, price, date) values (?, ?, ?, now())');
            $query->execute([$buyerId, $itemId, $price]);
            $this->db->commit();
            return true;
        } catch (PDOException $e){
            print_r($e->errorInfo);
            $this->db->rollBack();
            return false;
        }
    }

    public function cancelOrder($idOrder)
    {
        try{
            $query = $this->db->prepare('delete from orders where id = ?');
            $query->execute([$idOrder]);
            return true;
        } catch (PDOException $e){
            return false;
        }
    }

    public function updateOrder($orderId, array $data)
    {

    }

    //TODO: прикрутить фильтр!
    public function getHistory($userId)
    {
        $query = $this->db->prepare('select * from journal where seller_id = ? or buyer_id = ?');
        $query->execute([$userId, $userId]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInventory($id){
        $query = $this->db->prepare('select * from inventories where id = ?');
        $query->execute([$id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function checkItem($item){
        $query = $this->db->prepare('select * from journal
                                    where hour(timediff(now(), date)) <= 24
                                    and item_id = ?');
        $query->execute([$item]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}