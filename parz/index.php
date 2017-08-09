<?php

use Helpers\ParserHelper;

define('BASE_PATH', realpath(dirname(__FILE__)));

function my_autoloader($class)
{
    $filename = BASE_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';

    require($filename);
}

spl_autoload_register('my_autoloader');


/**
 * Call Parser Helper.
 */
$parsedData = ParserHelper::parse();

print_r($parsedData);