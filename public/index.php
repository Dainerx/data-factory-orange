<?php

require_once(__DIR__ . "/../vendor/autoload.php");

use Controller\Controller;

const SUPPORTED_ACTIONS = [
    Controller::STOCK_VARIATION_ACTION,
    Controller::CELAN_VARIATION_ACTION
];

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// all of our endpoints start with /person
// everything else results in a 404 Not Found
$url = $uri[1];
if (!in_array($url, SUPPORTED_ACTIONS)) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$controller = new Controller($url);
$controller->processRequest();
