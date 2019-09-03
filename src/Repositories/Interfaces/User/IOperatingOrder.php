<?php


namespace Repositories;


interface IOperatingOrder
{
    public function buyItem($buyer, $order);

    public function createSaleOrder($inventoryId, $price);

    public function createBuyOrder($buyerId, $itemId, $price);

    public function cancelOrder($id);

    public function updateOrder($id, $params);
}