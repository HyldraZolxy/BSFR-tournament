<?php
use hyldxycore\Autoloader;
use extensions\login\LoginController;
use hyldxycore\system\Application;

define("ORIGIN_FOLDER", realpath(__DIR__ . "/.."));

require_once join(DIRECTORY_SEPARATOR, array(ORIGIN_FOLDER, "hyldxycore", "other", "globalConstance.php"));
require_once join(DS,                  array(HYLDXYCORE,    "Autoloader.php"));

Autoloader::register();

$login = new LoginController();
$login->startSession();

$test = array(
    "scoresaberID" => "76561198235823594",
    "websiteCode" => "2",
    "roles" => 128
);
$login->makeSession($test);

$app = Application::getInstance();