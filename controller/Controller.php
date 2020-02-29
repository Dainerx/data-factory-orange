<?php

namespace Controller;

require_once(__DIR__ . "/../vendor/autoload.php");

use DAO\StockDAO;
use DAO\CelanDAO;

class Controller
{
    public const STOCK_VARIATION_ACTION = "get_stock_variation";
    public const CELAN_VARIATION_ACTION = "get_celan_variation";

    private $response = [];
    private $action;

    public function __construct($action)
    {
        $this->action = $action;
    }

    public function processRequest()
    {
        // OK for all by default, changes if anything fails.
        $this->response['status_code_header'] = 'HTTP/1.1 200 OK';
        switch ($this->action) {
            case self::STOCK_VARIATION_ACTION:
                try {
                    $this->response['body'] = StockDAO::getSharedInstance()->getAll();
                } catch (\PDOException $exception) {
                    // log exception in system api
                    $this->response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
                    $this->response['body'] = null;
                }
                break;
            case self::CELAN_VARIATION_ACTION:
                try {
                    $this->response['body'] = CelanDAO::getSharedInstance()->getAll();
                } catch (\PDOException $exception) {
                    // log exception in system api
                    $this->response['status_code_header'] = 'HTTP/1.1 500 Internal Server Error';
                    $this->response['body'] = null;
                }
                break;
            default:
                $this->response['status_code_header'] = 'HTTP/1.1 404 Not Found';
                $this->response['body'] = null;
                break;
        }
        header($this->response['status_code_header']);
        if ($this->response['body']) {
            echo json_encode($this->response['body']);
        }
    }
}
