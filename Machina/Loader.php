<?php

namespace Machina;

class Loader
{
    public static function loadFiles()
    {
        $toLoad = [
            "Machina",
            "Commands",
            "Customs"
        ];

        $toLoad = array_filter($toLoad, "is_dir");
        $files = [];

        foreach ($toLoad as &$value)
        {
            $files = array_merge($files, array_map(fn($e)=>"./$value/$e", scandir($value)));
        }

        foreach ($files as $file)
        {
            if (!str_ends_with($file, ".php")) continue;
            require_once $file;
        }
    }
}