<?php


namespace Repositories;


interface IOperatingExchange
{
    public function changeCommission($commission);

    public function getBalanceExchange();
}