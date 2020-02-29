<?php

namespace Database;

require(__DIR__ . "/../vendor/autoload.php");

use Dotenv\Dotenv;
use ErrorException;
use PDOException;

class DataBaseManager
{
    private static $sharedInstance;
    private $pdo;

    /**
     * Get singelton instance of the DatabaseManager.
     * @return DatabaseManager
     */
    public static function getSharedInstance()
    {
        if (!isset(self::$sharedInstance)) {
            self::$sharedInstance = new DatabaseManager();
        }
        return self::$sharedInstance;
    }

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . "/../");
        $dotenv->load();
        $this->pdo = new \PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';port=' . $_ENV['DB_PORT'], $_ENV['DB_USER'], $_ENV['DB_PWD']);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Turns off autocommit mode. 
     * Changes on database are only committed by calling commit().
     * Changes can be discarded by calling rollBack().
     * @throws PDOException
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commits changes to the database.
     * @throws PDOException
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * Rollback database to its latest state, ignore transaction changes. 
     * @throws PDOException
     */
    public function rollBack()
    {
        $this->pdo->rollBack();
    }

    /**
     * Get data from database using query and parameters passed as arguments.
     * 
     * @param string $query
     * @param array $params
     * @return array Data in form of an array
     * @throws PDOException
     */
    public function getAll($query, $params = [])
    {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Error $error) {
            echo ($error->getMessage());
        } catch (ErrorException $e) {
            echo ($e->getMessage());
        }
    }

    /**
     * Get one column from database using query and parameters passed as arguments.
     * 
     * @param string $query
     * @param array $params
     * @return array Data in form of an array
     * @throws PDOException
     */
    public function get($query, $params = [])
    {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
            return $statement->fetch(\PDO::FETCH_ASSOC);
        } catch (\Error $error) {
            echo ($error->getMessage());
        } catch (ErrorException $e) {
            echo ($e->getMessage());
        }
    }

    /**
     * Execute a query.
     *
     * @param string $query
     * @param array $params
     * @return bool Wether the query has been successfully executed
     * @throws PDOException
     */
    public function exec($sql, $params = [])
    {
        $statement = $this->pdo->prepare($sql);
        $result =  $statement->execute($params);
        return $result;
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
