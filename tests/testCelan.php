<?php

namespace Tests;

require_once(__DIR__ . "/../vendor/autoload.php");

use DAO\CelanDAO;

var_dump(CelanDAO::getSharedInstance()->getAll());
