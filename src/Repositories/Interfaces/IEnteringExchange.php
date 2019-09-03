<?php


namespace Repositories;


interface IEnteringExchange
{
    public function registration($login, $password);

    public function authorization($login, $password);
}