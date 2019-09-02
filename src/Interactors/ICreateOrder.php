<?php


namespace Interactors;


interface ICreateOrder
{
    public function check($userWithInventory, $price, $item);
}