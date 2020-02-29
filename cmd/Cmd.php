<?php

namespace Cmd;

require_once(__DIR__ . "/../vendor/autoload.php");

class Cmd
{
    const OUTPUT_OK = "OK.";
    const OUTPUT_NEWLINE = " \n";
    //output type constants
    const SUCCESS = "sucess";
    const INFO = "info";
    const WARNING = "warning";
    const ERROR = "error";
    //colors
    const GREEN = "0;32";
    const RED = "0;31";
    const YELLOW = "0;33";
    const WHITE = "0;37";

    protected $CMD_MESSAGE;
    protected $CMD_OUTPUT;

    protected function __construct($cmdMessage, $cmdOutput)
    {
        $this->CMD_MESSAGE = $cmdMessage;
        $this->CMD_OUTPUT = $cmdOutput;
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
    // Virtual
    protected function usage()
    {
    }
    // Virtual
    protected function readArgs()
    {
    }

    /**
     * Runs the cmd. Virtual each cmd has its own implementation of run.
     *
     * @param  bool $supportFlags
     *
     * @return TRUE|FALSE
     * Returns true if it ran successfully, false otherwise.
     */
    protected function run($supportFlags = true)
    {
    }
}
