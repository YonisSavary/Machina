<?php

namespace Machina;

interface CommandInterface
{
    /** Defines command's name (that the user will type in the console) */
    public static function getName(): string;

    /** Gives a short description of the Command for the Global Help */
    public static function getDescription(): string;

    /** Gives a complete help that can understand how to use the command */
    public static function getHelp(): string;

    public static function execute(...$args);
}