<?php

namespace Tests;

require_once(__DIR__ . "/../vendor/autoload.php");

use Controller\Controller;

const SUPPORTED_ACTIONS = [
    Controller::STOCK_VARIATION_ACTION,
    Controller::CELAN_VARIATION_ACTION
];


$url = "get_stock_variation";
if (!in_array($url, SUPPORTED_ACTIONS)) {
    var_dump("not found");
} else {
    var_dump("found");
}
