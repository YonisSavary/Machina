<?php

namespace Commands;

use Exception;
use Machina\CommandInterface;
use Machina\Machina;





class CSVRead implements CommandInterface
{
    const CONFIGURATION_HELP =
        "You can also redefine some parameters with Machina's config :"
        ."set separator ,    - Change the separator to ','";

    public static function getName(): string        { return "csv-read"; }
    public static function getDescription(): string { return "Read a CSV file Content"; }
    public static function getHelp(): string        { return
        "Read Content from a CSV File\n"
        ."\n"
        ."Ex\n"
        ."> csv-read myFile.csv      - Read myFile.csv and store its content"
        ."\n"
        .self::CONFIGURATION_HELP
    ; }

    public static function execute(...$args)
    {
        if (!count($args)) throw new Exception("[csv-read] need a file name");
        $file = $args[0];
        if (!file_exists($file)) throw new Exception("[$file] file does not exists !");

        $handle = fopen($file, "r");
        if (!$handle) throw new Exception("Cannot open [$file] !");

        $separator = Machina::$config["separator"] ?? ";";
        Machina::$storage = [];
        Machina::$storageType = Machina::TYPE_CSV;
        $count = 0;
        while ($data = fgetcsv($handle, 1000, $separator))
        {
            Machina::$storage[] = $data;
            $count++;
        }
        fclose($handle);
        echo "Read $count lines !";
    }
}












class CSVWrite implements CommandInterface
{
    public static function getName(): string        { return "csv-write"; }
    public static function getDescription(): string { return "Write Machina storage into a CSV file"; }
    public static function getHelp(): string        { return
        "Write Machina's CSV Content into a File\n"
        ."\n"
        ."Ex\n"
        ."> csv-write myFile.csv      - Write content into myFile.csv"
        ."\n"
        .CSVRead::CONFIGURATION_HELP
    ; }

    public static function execute(...$args)
    {
        if (!count($args)) throw new Exception("[csv-write] need a file name");
        $file = $args[0];

        $handle = fopen($file, "w");
        if (!$handle) throw new Exception("Cannot open [$file] !");

        $separator = Machina::$config["separator"] ?? ";";
        $count = 0;
        foreach (Machina::$storage as $value)
        {
            //foreach ($value as &$v) $v = iconv( mb_detect_encoding( $v ), 'Windows-1252//TRANSLIT', $v );
            fputcsv($handle, $value, $separator);
            $count++;
        }
        fclose($handle);
        echo "$count lines written !";
    }
}











class CSVMap implements CommandInterface
{
    public static function getName(): string        { return "csv-map"; }
    public static function getDescription(): string { return "Read a CSV file Content"; }
    public static function getHelp(): string        { return
        "Map Every CSV Lines to a string\n"
        ."You can use $0, $1, \$n... to bind CSV Values"
        ."\n"
        ."Ex\n"
        ."CSV inside myFile.csv: 'Hello;World'\n"
        ."\n"
        ."> csv-write myFile.csv      - Read content from myFile.csv\n"
        ."> csv-map \"Say $0 to the $1\"\n"
        ."\n"
        ."Machina's storage now contains \"Say Hello to the World\"\n"
    ; }

    public static function execute(...$args){
        if (!count($args)) throw new Exception("[csv-map] need a map, type 'help csv-map' for more");
        $map = $args[0];
        $regex = "/\\$[0-9]+/";

        $matches = [];
        preg_match_all($regex, $map, $matches);

        $presentBind = array_map(fn($bind) => [$bind, intval(str_replace("$", "", $bind))], $matches[0]);

        if (Machina::$storageType !== Machina::TYPE_CSV) throw new Exception("[csv-map] can only process CSV data");

        foreach (Machina::$storage as &$value)
        {
            $newVal = $map;
            foreach ($presentBind as $bind) $newVal = str_replace($bind[0], $value[$bind[1]]??'null', $newVal);
            $value = $newVal;
        }

        Machina::$storageType = Machina::TYPE_TEXT;
    }
}













class CSVConvert implements CommandInterface
{
    public static function getName(): string        { return "csv-convert"; }
    public static function getDescription(): string { return "Attempt to convert current storage content to csv"; }
    public static function getHelp(): string        { return
        "Simply try to convert current Machina's content to CSV \n"
        ."\n"
        ."Ex\n"
        ."> mysql-exec \"SELECT * FROM user\"      - Got Array of data inside Storage\n"
        ."> csv-convert                            - Convert it to CSV"
        ."> csv-map \"Hello $0\"                   - From here, we can work with it"
    ; }

    public static function execute(...$args){
        $file = uniqid();
        Machina::execute("csv-write $file");
        Machina::execute("csv-read $file");
        unlink($file);
    }
}