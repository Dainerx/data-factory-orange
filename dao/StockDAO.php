<?php

namespace DAO;

require_once(__DIR__ . "/../vendor/autoload.php");

use Database\DataBaseManager;

class StockDAO
{
    const IN = "Entree";
    const OUT = "Sortie";
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

    public function add($team, $product, $date1, $date2)
    {
        if ($date2 == NULL)
            DataBaseManager::getSharedInstance()->exec("INSERT INTO stock_variation (team,product,date1)
        VALUES (?,?,?)", [$team, $product, $date1]);
        else
            DataBaseManager::getSharedInstance()->exec("INSERT INTO stock_variation (team,product,date1,date2)
         VALUES (?,?,?,?)", [$team, $product, $date1, $date2]);
    }

    public function getAll()
    {
        $result = [];
        $dates = $this->parseDbDoublesDatesArray($this
            ->getDistinctTwoDates(), "date1", "date2");
        $teams = $this->parseDbArray($this->getDistinctTeams(), "team");
        $products = $this->parseDbArray($this->getDistinctProducts(), "product");
        $result[self::IN] = [];
        $result[self::OUT] = [];
        foreach ($dates as $d) {
            $d1 = $d[0];
            $d2 = $d[1];
            $result[self::IN][$d1] = [];
            if ($d2 != NULL)
                $result[self::OUT][$d2] = [];
            foreach ($teams as $team) {
                $result[self::IN][$d1][$team] = [];
                if ($d2 != NULL)
                    $result[self::OUT][$d2][$team] = [];
                foreach ($products as $product) {
                    $total1 = $this->countProductForDate1AndTeam(
                        $d1,
                        $team,
                        $product
                    )['total'];
                    if ($d2 != NULL)
                        $total2 = $this->countProductForDate2AndTeam(
                            $d2,
                            $team,
                            $product
                        )['total'];
                    $result[self::IN][$d1][$team][$product] = $total1;
                    if ($d2 != NULL)
                        $result[self::OUT][$d2][$team][$product] = $total2;
                }
            }
        }
        return $result;
    }

    private function getDistinctTwoDates()
    {
        return DataBaseManager::getSharedInstance()->getAll("SELECT date1,date2 FROM 
        stock_variation GROUP BY date1,date2");
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

    private function countProductForDate1AndTeam($date, $team, $product)
    {
        return DataBaseManager::getSharedInstance()->get(
            "SELECT COUNT(id) AS total
         FROM stock_variation WHERE date1=? AND team=? AND product=?",
            [$date, $team, $product]
        );
    }

    private function countProductForDate2AndTeam($date, $team, $product)
    {
        return DataBaseManager::getSharedInstance()->get(
            "SELECT COUNT(id) AS total
         FROM stock_variation WHERE date2=? AND team=? AND product=?",
            [$date, $team, $product]
        );
    }


    private function parseDbDoublesDatesArray($dbArray, $key1, $key2)
    {
        $parsedArray = [];
        foreach ($dbArray as $entry) {
            $couple = array($entry[$key1], $entry[$key2]);
            array_push($parsedArray, $couple);
        }
        return $parsedArray;
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
