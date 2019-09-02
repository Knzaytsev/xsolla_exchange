<?php


namespace Interactors;


class BuyOrder implements ICreateOrder
{

    public function check($userWithInventory, $price, $item)
    {
        $user = $userWithInventory['user'];
        $balance = $user['balance'];
        $inventory = $userWithInventory['inventory'];
        return $balance < $price && !in_array($item, $inventory);
    }
}