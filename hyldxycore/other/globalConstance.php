<?php
namespace hyldxycore\other;

/** General */
define("DS", DIRECTORY_SEPARATOR);

/** Path */
define("HYLDXYCORE",   join(DS, array(ORIGIN_FOLDER, "hyldxycore")));
define("HYLDXYMODS",   join(DS, array(ORIGIN_FOLDER, "extensions")));
define("HYLDXYCONFIG", join(DS, array(HYLDXYCORE,    "other", "config")));
define("WWW",          join(DS, array(ORIGIN_FOLDER, "www")));