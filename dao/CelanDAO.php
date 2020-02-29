<?php

namespace DAO;

require_once(__DIR__ . "/../vendor/autoload.php");

use Database\DataBaseManager;
use JsonSerializable;

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
        $result = [];
        $teams = $this->parseDbArray($this->getDistinctTeams(), "team");
        $products = $this->parseDbArray($this->getDistinctProducts(), "product");
        foreach ($products as $product) {
            $result[$product] = [];
            foreach ($teams as $team) {
                $result[$product][$team] = [];
                $dates = $this->parseDbDoublesDatesArray($this
                    ->getDistinctTwoDatesForProduct($product), "date1", "date2");
                foreach ($dates as $date) {
                    $dateEntree = $date[0];
                    $dateSortie = $date[1];
                    $celanTotal = $this->parseDbArray($this->getSumCelanTwoDatesForTeam(
                        $product,
                        $team,
                        $dateEntree,
                        $dateSortie
                    ), "celan_total");
                    reset($celanTotal);
                    $celanTotal = ($celanTotal[0] == NULL) ? 0 : $celanTotal[0];
                    if ($celanTotal != 0) {
                        $entryCelan = new TotalCelan($celanTotal, $dateEntree, $dateSortie);
                        array_push($result[$product][$team], $entryCelan);
                    }
                }
            }
        }
        return $result;
    }

    private function getDistinctProducts()
    {
        return DataBaseManager::getSharedInstance()->getAll("SELECT DISTINCT(product) 
           FROM celan_variation");
    }

    private function getDistinctTeams()
    {
        return DataBaseManager::getSharedInstance()->getAll("SELECT DISTINCT(team) 
        FROM celan_variation");
    }

    private function getDistinctTwoDatesForProduct($product)
    {
        return DataBaseManager::getSharedInstance()->getAll("SELECT date1,date2 FROM 
        celan_variation WHERE product=? GROUP BY date1,date2", [$product]);
    }


    private function getSumCelanTwoDatesForTeam($product, $team, $date1, $date2)
    {
        return DataBaseManager::getSharedInstance()->getAll(
            "SELECT SUM(celan) as celan_total FROM 
        celan_variation WHERE product=? AND team = ? AND date1=? AND date2=?",
            [$product, $team, $date1, $date2]
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

class TotalCelan implements JsonSerializable
{
    private $total;
    private $dateEntree;
    private $dateSortie;
    public function __construct($total, $dateEntree, $dateSortie)
    {
        $this->total = $total;
        $this->dateEntree = $dateEntree;
        $this->dateSortie = $dateSortie;
    }

    public function jsonSerialize()
    {
        return [
            "dateEntree" => $this->dateEntree,
            "dateSortie" => $this->dateSortie,
            "total" => $this->total
        ];
    }
}
