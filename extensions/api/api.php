<?php
namespace extensions\api;
use hyldxycore\system\DBFactory;
use hyldxycore\system\SQLQuery;
use hyldxycore\system\Tools;

class Api {
    static private ?Api $_instance = null;

    protected string $_url;

    private DBFactory $_dbFactory;
    private SQLQuery $_sqlQuery;
    private Tools $_tools;
    private $poolID = "1";

    public function __construct() {
        $this->_dbFactory = new DBFactory();
        $this->_sqlQuery = new SQLQuery($this->_dbFactory->getPDO());
        $this->_tools = new Tools();
    }

    private function getUser(): false|string {
        $fields = array("*");

        $this->_sqlQuery->sqlSelect($fields, "users");
        $result = $this->_sqlQuery->sqlFetchAll();

        return json_encode($result);
    }
    private function getMaps(int $poolID): false|string {
        $fields = array("*");

        if (!empty($poolID)) {
            $where = array(
                "poolID" => $poolID
            );
        } else $where = null;

        $this->_sqlQuery->sqlSelect($fields, "maps", $where);
        $result = $this->_sqlQuery->sqlFetchAll();

        return json_encode($result);
    }
    private function getMap(string $hash): bool|array {
        $fields = array("*");
        $where = array(
            "mapID" => $hash,
            "poolID" => $this->poolID
        );

        $this->_sqlQuery->sqlSelect($fields, "maps", $where, "AND");
        $result = $this->_sqlQuery->sqlFetch();

        return $result;
    }

    private function getScore(string $hash): bool|array {
        $fields = array("*");
        $where = array(
            "mapID" => $hash,
            "scoresaberID" => $_SESSION["scoresaberID"]
        );

        $this->_sqlQuery->sqlSelect($fields, "scores", $where, "AND");
        $result = $this->_sqlQuery->sqlFetch();

        return $result;
    }

    private function setScores($data): string {
        $mapHash            = $this->_tools->isExistingAndNotEmpty($data, "hash");
        $mapDifficulty      = ($this->_tools->isExistingAndNotEmpty($data, "difficulty") === "ExpertPlus") ? "Expert+" : $this->_tools->isExistingAndNotEmpty($data, "difficulty");
        $mapTotalNote       = $this->_tools->isExistingAndNotEmpty($data, "totalNote");
        $songLengthBool     = $this->_tools->isExistingAndNotEmpty($data, "songLength");
        $playerState        = $this->_tools->isExistingAndNotEmpty($data, "playerState");
        $mapModifiers       = $this->_tools->isExistingAndNotEmpty($data, "modifiers");
        $playerPerformance  = $this->_tools->isExistingAndNotEmpty($data, "performance");

        $mapData = $this->getMap($mapHash);

        if (!$mapData) return json_encode(array("error" => "The map isn't in the map pool !"));
        if ($mapDifficulty !== $mapData["difficulty"]) return json_encode(array("error" => "The map difficulty played isn't the good one !"));
        if ($playerState !== "Finish") {
            if ($mapTotalNote > $playerPerformance["notesPassed"]) return json_encode(array("error" => "Number of note passed is inferior to the total note of the map"));
            return json_encode(array("error" => "Map not finished"));
        }
        if (   $mapModifiers["disappearingArrows"]
            || $mapModifiers["ghostNotes"]
            || $mapModifiers["fasterSong"]
            || $mapModifiers["superFasterSong"]
            || $mapModifiers["noBombs"]
            || $mapModifiers["noWalls"]
            || $mapModifiers["noArrows"]
            || $mapModifiers["slowerSong"]
            || $mapModifiers["zenMode"]
            || $mapModifiers["proMode"]
        ) return json_encode(array("error" => "Modifiers not allowed"));
        if ($playerPerformance["paused"] > 0) return json_encode(array("error" => "Pause is not allowed on the run"));
        if (!$songLengthBool) return json_encode(array("error" => "Speed modification is not allowed"));

        $BDDScore = $this->getScore($mapHash);
        if ($BDDScore) {
            if ($BDDScore["score"] >= $playerPerformance["score"]) return json_encode(array("error" => "The score and the accuracy is not higher"));

            $PlayerScore = array(
                "score" => $playerPerformance["score"],
                "accuracy" => $playerPerformance["accuracy"],
                "miss" => $playerPerformance["miss"],
                "try" => $BDDScore["try"] + 1
            );

            $this->_sqlQuery->sqlUpdate($_SESSION["scoresaberID"], $mapHash, $PlayerScore, "scores");

            return json_encode(array("success" => "Score successfully updated !"));
        } else {
            $PlayerScore  = array(
                "scoresaberID" => $_SESSION["scoresaberID"],
                "mapID" => $mapHash,
                "difficulty" => $mapDifficulty,
                "score" => $playerPerformance["score"],
                "accuracy" => $playerPerformance["accuracy"],
                "miss" => $playerPerformance["miss"],
                "pause" => $playerPerformance["paused"],
                "try" => 1
            );
            $this->_sqlQuery->sqlAdd($PlayerScore, "scores");

            return json_encode(array("success" => "Score added into the database"));
        }
    }

    public function runAPI($URI): bool|string {
        if (isset($_SERVER["REQUEST_URI"])) $this->_url = $_SERVER["REQUEST_URI"];
        $INPUT = json_decode(file_get_contents("php://input"), true);

        $URIExploded = explode("/", parse_url($this->_url, PHP_URL_PATH));

        return match ($URI) {
            "getUser" => $this->getUser(),
            "getMaps" => $this->getMaps((isset($URIExploded[3])) ? $URIExploded[3] : 0),
            "setScores" => $this->setScores($INPUT),
            default => "Not implemented",
        };
    }

    static public function getInstance(): ?Api {
        if (is_null(self::$_instance)) self::$_instance = new self();

        return self::$_instance;
    }
}