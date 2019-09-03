<?php


namespace Repositories;


interface IGettingOrder
{
    public function getSaleOrders();

    public function getBuyOrders();

    public function getOrderInfo($id);
}