<?php

namespace Tests;

require_once(__DIR__ . "/../vendor/autoload.php");

use DAO\StockDAO;


var_dump(StockDAO::getSharedInstance()->getAll());
