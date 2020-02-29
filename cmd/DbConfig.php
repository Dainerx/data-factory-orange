<?php

namespace Cmd;

require_once(__DIR__ . "/../vendor/autoload.php");

class DbConfig extends Cmd
{
    private $DB_HOST;
    private $DB_PORT;
    private $DB_NAME;
    private $DB_USER;
    private $DB_PASS;

    public function __construct()
    {
        parent::__construct("Starting database configuration", "Database configuration:\n");
    }

    public function usage()
    {
        echo " 
  Script to generate database configuration. 
  Will set environment variables DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PWD.
 
Usage : db.php [options] -h <hostname>
        db.php [options] -h <hostname> -p <port> 
  -h <host>         database host
  -p <port>         database connection port
  -n <name>         database name
  -u <user>         database user's username
  -pass <pass>      database user's password 
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
                case '-h':
                    $this->DB_HOST = array_shift($argv);
                    break;
                case '-p':
                    $this->DB_PORT = array_shift($argv);
                    break;
                case '-n':
                    $this->DB_NAME = array_shift($argv);
                    break;
                case '-u':
                    $this->DB_USER = array_shift($argv);
                    break;
                case '-pass':
                    $this->DB_PASS = array_shift($argv);
                    break;
            }
        }
        return true;
    }


    /**
     * Prints a message with a color depending on the message's type. 
     *
     * @param  mixed $message message to print
     * @param  mixed $type type of message to print
     *
     * @return void
     */
    protected function println($message, $type)
    {
        $color = null;
        if ($type == self::SUCCESS)
            $color = self::GREEN;
        else if ($type == self::INFO)
            $color = self::WHITE;
        else if ($type == self::WARNING)
            $color = self::YELLOW;
        else
            $color = self::RED;

        $output = "\033[" . $color . "m" . $message . "\033[0m";
        echo $output . self::OUTPUT_NEWLINE;
    }
    /**
     * Sets environment variables passed as argument in the .env file.
     *
     * @param  mixed $vars array of env variables keys and values.
     *
     * @return TRUE|FALSE
     * Returns TRUE if the environment variables were successfully set, FALSE otherwise.
     */
    protected function setEnvVars($vars)
    {
        $handle = @fopen(__DIR__ . "/../.env", "r");
        if ($handle == false) {
            $this->println("Failed to read .env variable", self::ERROR);
            return false;
        } else {
            $envVars = [];
            while (($line = fgets($handle)) !== false) {
                $linesSplited = explode("=", $line);
                $envVars[$linesSplited[0]] = trim($linesSplited[1]);
            }
            fclose($handle);

            foreach ($vars as $varKey => $varValue) {
                $envVars[$varKey] = $varValue;
            }

            $payload = "";
            $entriesCount = count($envVars);
            $i = 0;
            foreach ($envVars as $varKey => $varValue) {
                if ($i != $entriesCount - 1) {
                    $payload .= $varKey . "=" . $varValue . self::OUTPUT_NEWLINE;
                } else {
                    $payload .= $varKey . "=" . $varValue;
                }
                $i++;
            }
            if (@file_put_contents(__DIR__ . "/../.env", $payload . PHP_EOL, LOCK_EX) == false) {
                $this->println("Failed to write to .env variable", self::ERROR);
                return false;
            }
            return true;
        }
    }

    /**
     * Reads database configuration from flags' values if set else terminal input.
     *
     * @return void
     */
    public function readConfig()
    {
        $db = [];

        if (empty($this->DB_HOST)) {
            $dataBaseHost = readline("Enter database host (default [localhost]): ");
            $this->DB_HOST = ($dataBaseHost === "") ? "localhost" : $dataBaseHost;
        }
        $db['host'] = $this->DB_HOST;
        $this->println("database host: " . $this->DB_HOST, self::INFO);

        if (empty($this->DB_PORT)) {
            $dataBasePort = readline("Enter database port (default [3306]): ");
            $this->DB_PORT = ($dataBasePort === "") ? "3306" : $dataBasePort;
        }
        $db['port'] = $this->DB_PORT;
        $this->println("database port: " . $this->DB_PORT, self::INFO);

        if (empty($this->DB_NAME)) {
            $dataBaseName = readline("Enter database name (default [data-factory]): ");
            $this->DB_NAME = ($dataBaseName === "") ? "data-factory" : $dataBaseName;
        }
        $db['name'] = $this->DB_NAME;
        $this->println("database name: " . $this->DB_NAME, self::INFO);

        if (empty($this->DB_USER)) {
            $dataBaseUser = readline("Enter database user (default [root]): ");
            $this->DB_USER = ($dataBaseUser === "") ? "root" : $dataBaseUser;
        }
        $db['user'] = $this->DB_USER;
        $this->println("database user: " .  $this->DB_USER, self::INFO);
        if (empty($this->DB_PASS)) {
            $dataBaseUserPassword = readline("Password (default []): ");
            $this->DB_PASS = ($dataBaseUserPassword === "") ? "" : $dataBaseUserPassword;
        }
        $db['password'] = $this->DB_PASS;
        $this->println("database password: " .  $this->DB_PASS, self::INFO);

        $this->println(self::OUTPUT_OK, self::SUCCESS);
        $this->println($this->CMD_OUTPUT . json_encode($db), self::INFO);
    }

    public function run($supportFlags = true)
    {
        if ($supportFlags == true) {
            // If flag mode is activated read flag values first.
            if (!$this->readArgs()) {
                $this->usage();
                return true;
            }
        }
        $this->println($this->CMD_MESSAGE, self::INFO);
        $this->readConfig();
        return $this->setEnvVars(array(
            'DB_HOST' => $this->DB_HOST,
            'DB_PORT' => $this->DB_PORT,
            'DB_NAME' => $this->DB_NAME,
            'DB_USER' => $this->DB_USER,
            'DB_PWD' => $this->DB_PASS
        ));
    }
}

$dbconfig = new DbConfig();
$dbconfig->run();
