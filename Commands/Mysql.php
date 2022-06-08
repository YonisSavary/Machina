<?php

namespace Commands;

use Exception;
use Machina\CommandInterface;
use Machina\Console;
use Machina\Machina;
use PDO;
use Throwable;

class MySQLConnect implements CommandInterface
{
    static $connection = null;

    public static function getName(): string        { return "mysql-connect"; }
    public static function getDescription(): string { return "Create a connection to a mysql server"; }
    public static function getHelp(): string        { return ""; }

    public static function execute(...$args)
    {
        $keys = ["host", "port", "dbname", "username", "password"];
        foreach ($keys as $key) $$key = readline($key." ?");

        try
        {
            self::$connection = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
            echo "Connected !";
        }
        catch(Throwable $e)
        {
            self::$connection = null;
            throw $e;
        }
    }
}





class MySQLExec implements CommandInterface
{
    public static function getName(): string        { return "mysql-exec"; }
    public static function getDescription(): string { return "Execute a SQL Query and return its content"; }
    public static function getHelp(): string        { return ""; }

    public static function execute(...$args)
    {
        if (!count($args)) throw new Exception("[mysql-exec] need a query");

        if (MySQLConnect::$connection == null) throw new Exception("You need to be connected, see [mysql-connect] for more");
        $connection = MySQLConnect::$connection;

        try
        {
            $res = $connection->query($args[0], PDO::FETCH_ASSOC);
            $res->execute();
            Machina::$storage = $res->fetchAll();
            Machina::$storageType = Machina::TYPE_ARRAYS;
        }
        catch (Throwable $e)
        {
            echo "Cannot execute your query !\n";
            echo $e->getMessage()."\n";
            echo $args[0]."\n";
        }
    }
}














class MySQLSource implements CommandInterface
{
    public static function getName(): string        { return "mysql-source"; }
    public static function getDescription(): string { return "Execute a SQL Query for each Storage Lines"; }
    public static function getHelp(): string        { return ""; }

    public static function execute(...$args)
    {
        if (Machina::$storageType !== Machina::TYPE_TEXT) throw new Exception("[mysql-source] can only be executed with text content");
        if (MySQLConnect::$connection == null) throw new Exception("You need to be connected, see [mysql-connect] for more");
        $connection = MySQLConnect::$connection;

        foreach (Machina::$storage as $row)
        {
            try
            {
                $res = $connection->query($row, PDO::FETCH_ASSOC);
                $res->execute();
            }
            catch (Throwable $e)
            {
                echo "Cannot execute your query !\n";
                echo $e->getMessage()."\n";
                echo $row."\n";
            }
        }

    }
}