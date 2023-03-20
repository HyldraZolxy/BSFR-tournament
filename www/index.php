<?php
use hyldxycore\Autoloader;
use extensions\login\Login;
use hyldxycore\system\Application;

define("ORIGIN_FOLDER", substr(__DIR__, 0, strlen(__DIR__)-4));

require_once join(DIRECTORY_SEPARATOR, array("..", "hyldxycore", "other", "global_constance.php"));
require_once join(DS, array(HYLDXYCORE, "autoloader.php"));
require_once join(DS, array(HYLDXYOTHER, "config.php"));

Autoloader::register();

$login = new Login();
$login->startSession();

$app = Application::getInstance();
echo $app->run();