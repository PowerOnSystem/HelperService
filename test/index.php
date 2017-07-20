<?php
define('ROOT', dirname(dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);

require ROOT . DS . 'vendor' . DS . 'autoload.php';

$manager = new PowerOn\Helper\HelperManager();
$manager->loadHelper('custom', 'App\Helpers\\');

/* @var $url PowerOn\Helper\UrlHelper */
$url = $manager->getHelper('url');

d($url->build(['one', 'two', 'action' => 'peperoni', '?' => ['right' => 'toleft', 'miami' => 'beach'], '#' => 'tar.tgz']));
//$url->configure();
