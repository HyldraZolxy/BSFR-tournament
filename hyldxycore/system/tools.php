<?php
namespace hyldxyCore\system;

class Tools {
    public function __construct() {}

    public function redirect($url): void {
        if (!headers_sent()) {
            header("Location:" . $url);
        } else {
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$url.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
            echo '</noscript>';
            exit;
        }
    }

    public function stateGenerator(): string {
        if (!isset($_SESSION["state"])) $_SESSION["state"] = bin2hex(openssl_random_pseudo_bytes(12));
        return $_SESSION["state"];
    }

    public function avatarURI(): string {
        $extension = substr($_SESSION["user_avatar"], 0, 2);
        if ($extension === "a_") $extension = ".gif";
        else $extension = ".png";

        return "https://cdn.discordapp.com/avatars/" . $_SESSION["user_id"] . "/" . $_SESSION["user_avatar"] . $extension;
    }

    public function isExistingAndNotEmpty($data, $element): mixed {
        if (isset($data[$element]) && !empty($data[$element])) return $data[$element];

        return null;
    }
}