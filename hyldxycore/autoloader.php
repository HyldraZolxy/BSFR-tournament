<?php
namespace hyldxycore;

class Autoloader {
    static public function autoload($class): void {
        // If the file is a file of hyldxycore system
        if (str_starts_with($class, __NAMESPACE__ . "\\")) {
            $class = str_replace(__NAMESPACE__ . "\\", "", $class);
            $class = str_replace("\\",                 DS, $class);

            $file = HYLDXYCORE . DS . $class . ".php";
            if(file_exists($file)) require_once $file;
        }

        // So it's an extension
        elseif (str_starts_with($class, "extensions" . "\\")) {
            $class = str_replace("extensions" . "\\", "", $class);
            $class = str_replace("\\",                DS, $class);

            $file = HYLDXYMODS . DS . $class . ".php";
            if(file_exists($file)) require_once $file;
        }
    }
    static public function register(): void {
        spl_autoload_register(array(__CLASS__, "autoload"));
    }
}