<?php


namespace Repositories;


interface IOperatingUserBalance
{
    public function addToBalance($id, $sum);

    public function subtractFromBalance($id, $sum);
}