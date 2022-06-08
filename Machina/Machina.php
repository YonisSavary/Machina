<?php

namespace Machina;

use Throwable;

class Machina
{
    const TYPE_NONE = "NONE";
    const TYPE_ARRAYS = "ARRAY";
    const TYPE_TEXT = "TEXT";
    const TYPE_CSV = "CSV";

    static $storageType = self::TYPE_NONE;
    static $storage = [];
    static $config = [];

    static $continue = true;
    static $commands = [];

    public static function execute(string $command)
    {
        $regex = "/(\".+?\"|\'.+?\'|.+?)( |$)/";
        $matches = [];
        preg_match_all($regex, $command, $matches);

        if (!count($matches)) return;
        $matches = $matches[1];

        $command = $matches[0];
        $args = array_splice($matches, 1);
        foreach ($args as &$value)
        {
            if (
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ||  (str_starts_with($value, "\"") && str_ends_with($value, "\""))
            ) $value = substr($value, 1, strlen($value)-2);
        }

        if (key_exists($command, self::$commands))
        {
            try
            {
                $class = self::$commands[$command];
                $class::execute(...$args);
            }
            catch(Throwable $e)
            {
                echo "Error while executing [$command] !\n";
                echo $e->getMessage();
            }
        }
        else
        {
            echo "Unkown command [$command] !";
        }
    }

    public static function prompt()
    {
        echo "\n\n";
        $command = readline("[".self::$storageType."-".count(self::$storage)."] > ");
        self::execute($command);
    }

    public static function registerCommands()
    {
        foreach (get_declared_classes() as $class)
        {
            $interfaces = class_implements($class);
            if (in_array(CommandInterface::class, $interfaces))
            {
                self::$commands[$class::getName()] = $class;
            }
        }
    }

    public static function load()
    {
        require_once "Machina/Loader.php";
        Loader::loadFiles();
        self::registerCommands();

        echo "\n--- Machina - Data Processing Tool for PHP ---";
        echo "\nType 'help' for more";
        while (self::$continue) self::prompt();
    }
}