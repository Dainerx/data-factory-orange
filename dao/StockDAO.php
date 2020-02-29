<?php

namespace DAO;

require_once(__DIR__ . "/../vendor/autoload.php");

use Database\DataBaseManager;

class StockDAO
{
    private static $sharedInstance;
    private function __construct()
    {
    }
    public static function getSharedInstance()
    {
        if (!isset(self::$sharedInstance)) {
            self::$sharedInstance = new StockDAO();
        }
        return self::$sharedInstance;
    }

    public function add($team, $product, $date)
    {
        DataBaseManager::getSharedInstance()->exec("INSERT INTO stock_variation (team,product,date)
         VALUES (?,?,?)", [$team, $product, $date]);
    }

    public function getAll()
    {
        $result = [];
        $dates = $this->parseDbArray($this->getDistinctDates(), "date");
        $teams = $this->parseDbArray($this->getDistinctTeams(), "team");
        $products = $this->parseDbArray($this->getDistinctProducts(), "product");

        foreach ($dates as $date) {
            $result[$date] = [];
            foreach ($teams as $team) {
                array_push($result[$date], $team);
                $result[$date][$team] = [];
                foreach ($products as $product) {
                    array_push($result[$date][$team], $product);
                    $result[$date][$team][$product] = $this->countProductForDateAndTeam(
                        $date,
                        $team,
                        $product
                    );
                }
            }
        }
        return $result;
    }

    private function getDistinctDates()
    {
        return DataBaseManager::getSharedInstance()->getAll("SELECT DISTINCT(date) 
        FROM stock_variation");
    }

    private function getDistinctTeams()
    {
        return DataBaseManager::getSharedInstance()->getAll("SELECT DISTINCT(team) 
        FROM stock_variation");
    }

    private function getDistinctProducts()
    {
        return DataBaseManager::getSharedInstance()->getAll("SELECT DISTINCT(product) 
           FROM stock_variation");
    }

    private function countProductForDateAndTeam($date, $team, $product)
    {
        return DataBaseManager::getSharedInstance()->get(
            "SELECT COUNT(id) AS total
         FROM stock_variation WHERE date=? AND team=? AND product=?",
            [$date, $team, $product]
        );
    }

    private function parseDbArray($dbArray, $key)
    {
        $parsedArray = [];
        foreach ($dbArray as $entry) {
            array_push($parsedArray, $entry[$key]);
        }
        return $parsedArray;
    }
}
