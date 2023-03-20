<?php
namespace hyldxycore\other;

// General
define("DS", DIRECTORY_SEPARATOR);

// Folder
define("ORIGIN", substr(__DIR__, 0, strlen(__DIR__) - 17)); // -17 = \hyldxycore\other
define("HYLDXYCORE", join(DS, array(ORIGIN, "hyldxycore")));
define("HYLDXYMODS", join(DS, array(ORIGIN, "extensions")));
define("HYLDXYOTHER", join(DS, array(HYLDXYCORE, "other")));
define("WWW", join(DS, array(ORIGIN, "www")));