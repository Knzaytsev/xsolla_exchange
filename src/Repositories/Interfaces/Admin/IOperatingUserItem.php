<?php


namespace Repositories;


interface IOperatingUserItem
{
    public function createItem($name);

    public function setItem($userId, $itemId);
}