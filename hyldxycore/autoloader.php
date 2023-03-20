<?php
namespace hyldxycore;

class Autoloader {

    static public function register(): void {
        spl_autoload_register(array(__CLASS__, "autoload"));
    }

    static public function autoload($class): void {

        // If the file is a file of hyldxycore system
        if (str_starts_with($class, __NAMESPACE__ . "\\")) {
            $class = str_replace(__NAMESPACE__ . "\\", "", $class);
            $class = str_replace("\\",                 DS, $class);

            if(file_exists(HYLDXYCORE . DS . strtolower($class) . ".php")) require_once strtolower($class) . ".php";
        }

        // So it's an extension
        if (str_starts_with($class, "extensions" . "\\")) {
            $class = str_replace("extensions" . "\\", "", $class);
            $class = str_replace("\\",                DS, $class);

            if(file_exists(HYLDXYMODS . DS . strtolower($class) . ".php")) require_once HYLDXYMODS . DS . strtolower($class) . ".php";
        }
    }
}