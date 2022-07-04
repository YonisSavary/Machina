<?php

use Machina\Machina;

require_once "./Machina/Machina.php";

Machina::load();

$options = getopt("", ["script::", "command::", "help::"]);

if      ($options["script"]??false) Machina::execute("source ".$options["script"]);
else if ($options["command"]??false) Machina::execute($options["command"]);
else if ($options["command"]??false) Machina::execute($options["command"]);
else if (isset($options["help"])) Machina::displayHelp();
else    Machina::startPrompt();