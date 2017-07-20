<?php
/*
 * Archivo de testeo del framework
 */
session_start();

define('ROOT', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);

require ROOT . DS . 'vendor' . DS . 'autoload.php';

$manager = new PowerOn\Helper\HelperManager();
$html = $manager->getHelper('html');
echo $html->nestedList(['lista', 'lista2', 'lista3', ['otra lista', 'otra lista2'], 'lista5']);