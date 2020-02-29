<?php

namespace DAO;

require_once(__DIR__ . "/../vendor/autoload.php");

use Database\DataBaseManager;

class CelanDAO
{
    private static $sharedInstance;
    private function __construct()
    {
    }
    public static function getSharedInstance()
    {
        if (!isset(self::$sharedInstance)) {
            self::$sharedInstance = new CelanDAO();
        }
        return self::$sharedInstance;
    }

    public function add($product, $dateEntree, $dateSortie, $team, $celan)
    {
        DataBaseManager::getSharedInstance()->exec("INSERT INTO celan_variation (product,date1,date2,team,celan)
         VALUES (?,?,?,?,?)", [$product, $dateEntree, $dateSortie, $team, $celan]);
    }

    public function getAll()
    {
    }

    private function getDistinctProducts()
    {
        return DataBaseManager::getSharedInstance()->getAll("SELECT DISTINCT(product) 
           FROM celan_variation");
    }
}
