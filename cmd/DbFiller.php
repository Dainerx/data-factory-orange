<?php

namespace Cmd;

require_once(__DIR__ . "/../vendor/autoload.php");

use DAO\StockDAO;
use DAO\CelanDAO;

class DbFiller extends Cmd
{

    private $DUMP_FILE = null;

    public function __construct()
    {
        parent::__construct("Starting database filling", "Database filling succeeded.");
    }

    public function usage()
    {
        echo " 
  Script to fill database with data dump from excel or text file. 
 
Usage : DbFiller.php [options] -c <csv_file>
        DbFiller.php [options] -t <text_file> 
  -c <csv_file>         csv file containing data
  -t <text_file>        text file containing data
";
    }


    public function readArgs()
    {
        global $argv;
        while (!empty($argv)) {
            $arg = array_shift($argv);
            switch ($arg) {
                case '--help':
                    return false;
                case '-c':
                    $this->DUMP_FILE = array_shift($argv);
                    break;
                case '-t':
                    $this->DUMP_FILE = array_shift($argv);
                    break;
            }
        }
        return true;
    }

    private function insertStockVariationLine($csv_line)
    {
        $dateSortie = $csv_line[1];
        $dateEntree = $csv_line[10];
        $team = $csv_line[2];
        $product = $csv_line[18];


        if (empty($dateEntree) || empty($team) || empty($product))
            return false;
        else {
            $dateEntreeFormatted = \DateTime::createFromFormat('m/d/Y', $dateEntree);
            $dateEntreeFormatted = $dateEntreeFormatted->format('Y-m-d');
            if (empty($dateSortie))
                $dateSortieFormatted = NULL;
            else {
                $dateSortieFormatted = \DateTime::createFromFormat('m/d/Y', $dateSortie);
                $dateSortieFormatted = $dateSortieFormatted->format('Y-m-d');
            }
            StockDAO::getSharedInstance()->add($team, $product, $dateEntreeFormatted, $dateSortieFormatted);
            return true;
        }
    }

    private function insertCelanVariationLine($csv_line)
    {
        $dateSortie = $csv_line[1];
        $dateEntree = $csv_line[10];
        $team = $csv_line[2];
        $product = $csv_line[18];
        $clean = $csv_line[22];

        if (empty($dateSortie) || empty($team) || empty($product) || empty($clean))
            return false;
        else {
            $dateSortieFormatted = \DateTime::createFromFormat('m/d/Y', $dateSortie);
            $dateEntreeFormatted = \DateTime::createFromFormat('m/d/Y', $dateEntree);
            CelanDAO::getSharedInstance()->add($product, $dateEntreeFormatted->format('Y-m-d'), $dateSortieFormatted->format('Y-m-d'), $team, $clean);
            return true;
        }
    }

    public function run($supportFlags = true)
    {
        if (!$this->readArgs()) {
            $this->usage();
            return true;
        }

        if ($this->DUMP_FILE == null) {
            $this->println("Aborting: needs to pass a file containing data.", self::ERROR);
        } else if (is_file($this->DUMP_FILE) == FALSE) {
            $this->println("Aborting: file not found.", self::ERROR);
        } else {
            $this->println("File found.", self::SUCCESS);
            $csv = array_map('str_getcsv', file($this->DUMP_FILE));

            $stockVariationInsertedCount = 0;
            $celanVariationInsertedCount = 0;
            for ($i = 1; $i < count($csv); $i++) {
                if ($this->insertStockVariationLine($csv[$i])) {
                    $stockVariationInsertedCount++;
                }

                if ($this->insertCelanVariationLine($csv[$i])) {
                    $celanVariationInsertedCount++;
                }
            }

            $stockVariationOmittedCount = count($csv) - $stockVariationInsertedCount;
            $celanVariationOmittedCount = count($csv) - $celanVariationInsertedCount;
            $this->println("Finished filling the database", self::SUCCESS);
            $this->println("Stock Variation: Inserted $stockVariationInsertedCount lines, omitted $stockVariationOmittedCount lines.", self::INFO);
            $this->println("CELAN Variation: Inserted $celanVariationInsertedCount lines, omitted $celanVariationOmittedCount lines.", self::INFO);
        }
    }
}

$dbFiller = new DbFiller();
$dbFiller->run();
