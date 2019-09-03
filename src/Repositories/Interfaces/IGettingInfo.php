<?php


namespace Repositories;


interface IGettingInfo
{
    public function getItems();

    public function getStatusExchange();

    public function getRevenuePeriod($from, $to);

    public function getTopItemsPeriod($from, $to);

    public function getTopUsersPeriod($from, $to);

    public function getUserById($id);

    public function getUserByLogin($login);
}