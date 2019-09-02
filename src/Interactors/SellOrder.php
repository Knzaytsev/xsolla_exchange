<?php


namespace Interactors;


class SellOrder implements ICreateOrder
{

    public function check($userWithInventory, $price, $item)
    {
        $items = array();
        $inventory = $userWithInventory['inventory'];
        foreach ($inventory as $item){
            array_push($items, $item['item_id']);
        }
        print_r($items);
        return in_array($item, $inventory);
    }
}