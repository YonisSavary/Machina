<?php

namespace Commands;

use Exception;
use Machina\CommandInterface;
use Machina\Machina;






class Set implements CommandInterface
{
    public static function getName(): string           { return "set"; }
    public static function getDescription(): string    { return "Set a variable in the Machina configuration"; }
    public static function getHelp(): string           { return
        "You can change any variable in Machina configuration !\n"
       ."Simply call 'set <key> <value>' to do so"
       ."\n\n"
       ."Ex: \n"
       ."> set separator ,       - Set the CSV separator to ','";
    }

    public static function execute(...$args)
    {
        if (count($args) < 2) {
            throw new Exception("[set] command needs two parameters !");
        }
        $key = $args[0]; $value = $args[1];
        Machina::$config[$key] = $value;
        echo "[$key] set to '$value' !";
    }
}








class ExitCommand implements CommandInterface
{
    public static function getName(): string           { return "exit"; }
    public static function getDescription(): string    { return "Exit Machina Script"; }
    public static function getHelp(): string           { return
        "Type 'exit' to quit Machina";
    }

    public static function execute(...$args)
    {
        echo "Byebye ! \n\n";
        Machina::$continue = false;
    }
}







class DumpConfig implements CommandInterface
{
    public static function getName(): string           { return "dump-config"; }
    public static function getDescription(): string    { return "Dump Machina configuration"; }
    public static function getHelp(): string           { return
        "Calling 'dump-config' display the Machina configuration on screen"
        ;
    }

    public static function execute(...$args)
    {
        foreach (Machina::$config as $key => $value)
        {
            echo "- [$key] $value\n";
        }
    }
}







class DumpData implements CommandInterface
{
    public static function getName(): string           { return "dump-data"; }
    public static function getDescription(): string    { return "Dump Machina Storage"; }
    public static function getHelp(): string           { return
        "This command can help you get Machina's content"."\n"
        ."\n"
        ."Ex:"."\n"
        ."> dump-data            - Simply display Machina's content on screen"."\n"
        ."> dump-data myText.txt - Dump the content inside myText.txt"."\n"
    ; }

    public static function execute(...$args)
    {
        if (isset($args[0]))
        {
            // Write File
            $file = $args[0];
            file_put_contents($file, join("\n", Machina::$storage));
        }
        else
        {
            foreach (Machina::$storage as $value)
            {
                print_r($value);
                echo "\n";
            }
        }
    }
}







class ChangeDirectory implements CommandInterface
{
    public static function getName(): string           { return "cd"; }
    public static function getDescription(): string    { return "Change current directory"; }
    public static function getHelp(): string           { return
        "Change the current working directory to work with relative paths"."\n"
        ."\n"
        ."Ex:"."\n"
        ."> cd /home/foo       - Goto /home/foo directory"."\n"
    ; }

    public static function execute(...$args)
    {
        if (!isset($args)) throw new Exception("[cd] need a directory");
        $dir = $args[0];
        if (!is_dir($dir)) throw new Exception("[$dir] is not a directory");
        chdir($dir);
        echo "Changed Directory !";
    }
}






class PrintDirectory implements CommandInterface
{
    public static function getName(): string           { return "pwd"; }
    public static function getDescription(): string    { return "Print Working Directory"; }
    public static function getHelp(): string           { return
        "Print the current working directory path"."\n"
        ."\n"
        ."Ex:"."\n"
        ."> cd /home/foo"."\n"
        ."> pwd                - print /home/foo on screen"."\n"
    ; }

    public static function execute(...$args)
    {
        echo getcwd();
    }
}










class Help implements CommandInterface
{
    public static function getName(): string           { return "help"; }
    public static function getDescription(): string    { return "Display the help page of a command"; }
    public static function getHelp(): string           { return
        "Display global Machina's help or a specific command's help"."\n"
        ."\n"
        ."Ex"."\n"
        ."> help              - Display every known commands and their description"."\n"
        ."> help dump-data    - Display the specific help page of the 'dump-data' command"."\n"
    ; }

    public static function execute(...$args)
    {
        if (!count($args))
        {
            foreach (Machina::$commands as $className => $class)
            {
                echo $class::getName() .":\n\t- ". $class::getDescription() ."\n\n";
            }
        }
        else
        {
            $com = $args[0];
            if (!isset(Machina::$commands[$com])) throw new Exception("[$com] Command not found !");
            $class = Machina::$commands[$com];
            echo "\n---------- ". $class::getName() . " ---------------";
            echo "\n".$class::getDescription();
            echo "\n";
            echo "\n".$class::getHelp();
        }
    }
}