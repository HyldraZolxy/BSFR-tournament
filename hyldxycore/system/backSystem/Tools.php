<?php
namespace hyldxycore\system\backSystem;

class Tools {
    public array $statusConfig     = array(
        "now"   => 0,
        "soon"  => 1,
        "ended" => 2
    );
    private array $scoresaberConfig = array(
        "URI"          => "https://cdn.scoresaber.com/avatars/",
        "oculusNumber" => array("2", "3", "4")
    );

    public function __construct() {}

    /**********
     * General
     **********/

    /**
     * @description Format date (Y-m-d H:i:s) into an array (Months, Weeks, Days, Hours), contains negative values if date is in the past
     * @param string $date
     * @return array
     */
    public function checkDaysLeft(string $date): array {
        $date = strtotime($date);
        $now  = strtotime("now");

        $months = floor(($date - $now) / (60 * 60 * 24 * 30));
        $weeks  = floor(($date - $now) / (60 * 60 * 24 * 7));
        $day    = floor(($date - $now) / (60 * 60 * 24));
        $hours  = round((($date - $now) - ($day * 60 * 60 * 24)) / (60 * 60));

        return array(
            "months" => $months,
            "weeks"  => $weeks,
            "days"   => $day,
            "hours"  => $hours
        );
    }
    /**
     * @description Check if date is in the past, now or in the future
     * @param array $date (starting_at, finishing_at, status)
     * @return int
     */
    public function checkDateStatus(array $date): int {
        if ($date["status"] === -1) return -1;

        if (strtotime($date["finishing_at"]) < time()) return $this->statusConfig["ended"];
        if (strtotime($date["starting_at"])  < time()) return $this->statusConfig["now"];
        if (strtotime($date["starting_at"])  > time()) return $this->statusConfig["soon"];

        return -1;
    }

    /******
     * URI
     ******/

    /**
     * @description Redirect to a specific URL
     * @param $url
     * @return void
     */
    public function redirect($url): void {
        if (!headers_sent()) header("Location:" . $url);
        else {
            echo '<script type="text/javascript">';
            echo 'window.location.href="'.$url.'";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
            echo '</noscript>';
            exit;
        }
    }

    /*********
     * Scoresaber
     *********/

    /**
     * @description Parse scoresaber ID into a valid scoresaber picture URI
     * @param string $scoresaberID
     * @return string
     */
    public function parseScoresaberPicture(string $scoresaberID): string {
        $firstChar = mb_substr($scoresaberID, 0, 1);
        if (in_array($firstChar, $this->scoresaberConfig["oculusNumber"])) return $this->scoresaberConfig["URI"] . "oculus.png";
        else                                                               return $this->scoresaberConfig["URI"] . $scoresaberID . ".jpg";
    }
}