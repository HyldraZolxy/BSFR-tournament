<?php
namespace extensions\api;

use extensions\login\LoginController;
use hyldxycore\system\backSystem\DBFactory;
use hyldxycore\system\backSystem\SQLQuery;

class ApiController {
    private DBFactory       $_dbFactory;
    private SQLQuery        $_sqlQuery;
    private LoginController $_loginController;

    private int $rolesRequiredPlayer = 128;

    public function __construct() {
        $this->_dbFactory       = new DBFactory();
        $this->_sqlQuery        = new SQLQuery($this->_dbFactory->getPDO());
        $this->_loginController = new LoginController();
    }

    /********************
     * Tournament system
     ********************/
    /**
     * @description Get all tournaments
     * @return string
     */
    public function getTournaments(): string {
        $fields = array("*");
        $where  = array(
            "status" => array(
                "value"    => 0,
                "operator" => ">="
            )
        );
        $order  = array(
            "starting_at" => "DESC"
        );

        $this->_sqlQuery->sqlSelect("tournaments", $fields, $where, $order);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /**
     * @description Get a tournament
     * @param int $id
     * @param string|array|null $column
     * @return string
     */
    public function getTournament(int $id, string|array|null $column = null): string {
        $fields = ($column !== null) ? is_array($column) ? $column : array($column) : array("*");
        $where  = array(
            "id" => array(
                "value"    => $id,
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("tournaments", $fields, $where);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /*************
     * Pool system
     *************/
    /**
     * @description Get all pools
     * @return string
     */
    public function getAllPools(): string {
        $fields = array("*");
        $order  = array(
            "starting_at" => "DESC"
        );

        $this->_sqlQuery->sqlSelect("poolbytournament", $fields, array(), $order);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /**
     * @description Get all pools by tournament
     * @param int $tournamentID
     * @return string
     */
    public function getTournamentPools(int $tournamentID): string {
        $fields = array("p.*", "pt.*");
        $joins  = array(
            "poolByTournament" => array(
                "table" => "poolbytournament pt",
                "on"    => "pt.poolID = p.id"
            )
        );
        $where  = array(
            "pt.tournamentID" => array(
                "value"    => $tournamentID,
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("pools p", $fields, $where, array(), array(), $joins);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /**
     * @description Get a pool by tournament
     * @param int $tournamentID
     * @param int $poolID
     * @return string
     */
    public function getTournamentPool(int $tournamentID, int $poolID): string {
        $fields = array("p.*", "pt.*");
        $joins  = array(
            "poolByTournament" => array(
                "table" => "poolbytournament pt",
                "on"    => "pt.poolID = p.id"
            )
        );
        $where  = array(
            "pt.poolID" => array(
                "value"    => $poolID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "pt.tournamentID" => array(
                "value"    => $tournamentID,
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("pools p", $fields, $where, array(), array(), $joins);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /************
     * Map system
     ************/
    /**
     * @description Get all maps by tournament and pool
     * @param int $tournamentID
     * @param int $poolID
     * @return string
     */
    public function getPoolMaps(int $tournamentID, int $poolID): string {
        $fields = array("m.id", "m.hash", "m.name", "m.author", "m.mapper", "m.cover", "mp.difficulty", "mp.tags");
        $joins  = array(
            "mapsbypool" => array(
                "table" => "mapsbypool mp",
                "on"    => "mp.mapID = m.id"
            )
        );
        $where  = array(
            "mp.poolID" => array(
                "value"    => $poolID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "mp.tournamentID" => array(
                "value"    => $tournamentID,
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("maps m", $fields, $where, array(), array(), $joins);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /**
     * @description Get a map by tournament and pool
     * @param int $tournamentID
     * @param int $poolID
     * @param int $mapID
     * @return string
     */
    public function getPoolMap(int $tournamentID, int $poolID, int $mapID): string {
        $fields = array("m.*", "mp.difficulty", "mp.tags");
        $joins  = array(
            "mapsbypool" => array(
                "table" => "mapsbypool mp",
                "on"    => "mp.mapID = m.id"
            )
        );
        $where  = array(
            "mp.mapID" => array(
                "value"    => $mapID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "mp.poolID" => array(
                "value"    => $poolID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "mp.tournamentID" => array(
                "value"    => $tournamentID,
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("maps m", $fields, $where, array(), array(), $joins);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /**************
     * Score system
     **************/
    /**
     * @description Get all scores by tournament, pool, map and team
     * @param int $tournamentID
     * @param int $poolID
     * @param int $mapID
     * @param array $teams
     * @return string
     */
    public function getMapScores(int $tournamentID, int $poolID, int $mapID, array $teams): string {
        $fields = array("s.*", "u.name");
        $joins  = array(
            "users" => array(
                "table" => "users u",
                "on"    => "u.scoresaberID = s.scoresaberID"
            )
        );
        $where  = array(
            "s.tournamentID" => array(
                "value"    => $tournamentID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "s.poolID" => array(
                "value"    => $poolID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "s.mapID" => array(
                "value"    => $mapID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "s.team" => array(
                "value"    => $teams,
                "operator" => "IN"
            )
        );
        $order  = array(
            "s.accuracy" => "DESC"
        );

        $this->_sqlQuery->sqlSelect("scores s", $fields, $where, $order, array(), $joins);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /**
     * @description Get a score by tournament, pool, map and scoresaberID
     * @param int $tournamentID
     * @param int $poolID
     * @param int $mapID
     * @param string $scoresaberID
     * @return string
     */
    public function getMapScore(int $tournamentID, int $poolID, int $mapID, string $scoresaberID): string {
        $fields = array("*");
        $where  = array(
            "tournamentID" => array(
                "value"    => $tournamentID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "poolID" => array(
                "value"    => $poolID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "mapID" => array(
                "value"    => $mapID,
                "operator" => "=",
                "between"  => "AND"
            ),
            "scoresaberID" => array(
                "value"    => $scoresaberID,
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("scores s", $fields, $where);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    /**
     * @description Set a score by tournament, pool, map and scoresaberID
     * @param php://input
     * @return string
     */
    public function setScore(): string {
        if (!$this->_loginController->isAuthenticated())                           return json_encode(array("error" => "You are not authenticated"));
        if (!$this->_loginController->asAuthorization($this->rolesRequiredPlayer)) return json_encode(array("error" => "You are not authorized"));

        $INPUT = json_decode(file_get_contents("php://input"), true);
        if (empty($INPUT)) return json_encode(array("error" => "No data sent"));

        if (    empty($INPUT["tournamentID"])
            ||  empty($INPUT["poolID"]      )
            ||  empty($INPUT["mapID"]       )
        ) return json_encode(array("error" => "Missing data"));

        $tournamentID = $INPUT["tournamentID"];
        $poolID       = $INPUT["poolID"];
        $mapID        = $INPUT["mapID"];
        $data         = $INPUT["data"];

        $mapHash           = (empty($data["hash"]))        ? null : $data["hash"];
        $mapDifficulty     = (empty($data["difficulty"]))  ? null : $data["difficulty"];
        $mapTotalNote      = (empty($data["totalNote"]))   ? null : $data["totalNote"];
        $songLengthBool    = (empty($data["songLength"]))  ? null : $data["songLength"];
        $playerState       = (empty($data["playerState"])) ? null : $data["playerState"];
        $playerMod         = (empty($data["modifiers"]))   ? null : $data["modifiers"];
        $playerPerformance = (empty($data["performance"])) ? null : $data["performance"];

        if (empty($mapHash))           return json_encode(array("error" => "Missing map hash"));
        if (empty($mapDifficulty))     return json_encode(array("error" => "Missing map difficulty"));
        if (empty($mapTotalNote))      return json_encode(array("error" => "Missing map total note"));
        if (empty($songLengthBool))    return json_encode(array("error" => "Missing song length"));
        if (empty($playerState))       return json_encode(array("error" => "Missing player state"));
        if (empty($playerMod))         return json_encode(array("error" => "Missing player modifiers"));
        if (empty($playerPerformance)) return json_encode(array("error" => "Missing player performance"));

        $tournamentData = json_decode($this->getTournament($tournamentID), true);
        if (empty($tournamentData))                  return json_encode(array("error" => "Tournament not found"));
        if ((int)$tournamentData[0]["status"] === 2) return json_encode(array("error" => "Tournament is not ongoing"));

        $poolData = json_decode($this->getTournamentPool($tournamentID, $poolID), true);
        if (empty($poolData))                  return json_encode(array("error" => "Pool not found"));
        if ((int)$poolData[0]["status"] === 2) return json_encode(array("error" => "Pool is not ongoing"));

        $mapData = json_decode($this->getPoolMap($tournamentID, $poolID, $mapID), true);
        if (empty($mapData)) return json_encode(array("error" => "Map not found"));

        if ($mapDifficulty !== $mapData[0]["difficulty"])      return json_encode(array("error" => "Map difficulty is not the same"));
        if ($playerState !== "Finish")                         return json_encode(array("error" => "Player state is not finish"));
        if ($mapTotalNote > $playerPerformance["notesPassed"]) return json_encode(array("error" => "Player didn't pass the map"));

        if (   $playerMod["disappearingArrows"]
            || $playerMod["ghostNotes"]
            || $playerMod["fasterSong"]
            || $playerMod["superFasterSong"]
            || $playerMod["noBombs"]
            || $playerMod["noWalls"]
            || $playerMod["noArrows"]
            || $playerMod["slowerSong"]
            || $playerMod["zenMode"]
            || $playerMod["proMode"]
        ) return json_encode(array("error" => "Modifiers are not allowed"));

        if ($playerPerformance["paused"] > 0) return json_encode(array("error" => "Pause is not allowed"));
        if (!$songLengthBool)                 return json_encode(array("error" => "Speed song is not allowed"));

        $scoresaberID = $_SESSION["scoresaberID"];
        $scoreData    = json_decode($this->getMapScore($tournamentID, $poolID, $mapID, $scoresaberID), true);

        if (!empty($scoreData)) {
            $accuracy = $scoreData[0]["accuracy"];
            $score    = $scoreData[0]["score"];
            $miss     = $scoreData[0]["miss"];
            $try      = $scoreData[0]["try"] + 1;
            $date     = date("Y-m-d H:i:s");
            if ($accuracy < $playerPerformance["accuracy"]) {
                $score = $playerPerformance["score"];
                $miss  = $playerPerformance["miss"];
            }

            $accuracyBest  = $scoreData[0]["best"];
            $accuracyWorst = $scoreData[0]["worst"];
            if ($accuracyBest < $playerPerformance["accuracy"])  $accuracyBest = $playerPerformance["accuracy"];
            if ($accuracyWorst > $playerPerformance["accuracy"]) $accuracyWorst = $playerPerformance["accuracy"];
            $accuracyAverage = ($accuracyBest + $accuracyWorst) / 2;

            $parameters = array(
                "score"       => $score,
                "accuracy"    => $accuracyBest,
                "best"        => $accuracyBest,
                "average"     => $accuracyAverage,
                "worst"       => $accuracyWorst,
                "miss"        => $miss,
                "try"         => $try,
                "dateLastTry" => $date
            );
            $where = array(
                "scoresaberID" => array(
                    "value"    => $scoresaberID,
                    "operator" => "=",
                    "between"  => "AND"
                ),
                "mapID" => array(
                    "value"    => $mapID,
                    "operator" => "=",
                    "between"  => "AND"
                ),
                "poolID" => array(
                    "value"    => $poolID,
                    "operator" => "=",
                    "between"  => "AND"
                ),
                "tournamentID" => array(
                    "value"    => $tournamentID,
                    "operator" => "="
                )
            );

            $this->_sqlQuery->sqlUpdate("scores", $parameters, $where);

            if ($scoreData[0]["accuracy"] > $playerPerformance["accuracy"]) return json_encode(array("warning" => "Player already have a better score"));
            return json_encode(array("success" => "Score as been updated!"));
        } else {
            $parameters = array(
                "scoresaberID" => $scoresaberID,
                "score"        => $playerPerformance["score"],
                "accuracy"     => $playerPerformance["accuracy"],
                "best"         => $playerPerformance["accuracy"],
                "average"      => $playerPerformance["accuracy"],
                "worst"        => $playerPerformance["accuracy"],
                "miss"         => $playerPerformance["miss"],
                "try"          =>  1,
                "dateFirstTry" => date("Y-m-d H:i:s"),
                "dateLastTry"  => date("Y-m-d H:i:s"),
                "mapID"        => $mapID,
                "poolID"       => $poolID,
                "tournamentID" => $tournamentID,
                "team"         => "FR-2023"
            );

            $this->_sqlQuery->sqlAdd("scores", $parameters);
            return json_encode(array("success" => "Score as been added!"));
        }
    }

    /*************
     * Team system
     *************/
    /**
     * @description Get team by team name
     * @param string $team
     * @return string
     */
    public function getTeam(string $team): string {
        $fields = array("u.name", "u.scoresaberID", "t.isCaptain");
        $joins  = array(
            "teambyuser" => array(
                "table" => "teambyuser t",
                "on"    => "t.userID = u.scoresaberID"
            )
        );
        $where  = array(
            "t.team" => array(
                "value"    => $team,
                "operator" => "="
            )
        );
        $order  = array(
            "t.isCaptain" => "DESC"
        );

        $this->_sqlQuery->sqlSelect("users u", $fields, $where, $order, array(), $joins);
        return json_encode($this->_sqlQuery->sqlFetchAll());
    }

    public function error(): string {
        return json_encode(array(
            "error" => "404",
            "message" => "Not found"
        ));
    }
}