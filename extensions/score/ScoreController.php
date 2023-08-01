<?php
namespace extensions\score;

use extensions\api\ApiController;

class ScoreController {
    private ApiController $_apiController;

    private array $teams = array();
    private array $maps  = array();
    private array $mapsByCategory = array(
        "Acc"     => array(),
        "Mid"     => array(),
        "Tech"    => array(),
        "Speed"   => array(),
        "Classic" => array()
    );
    private array $scoresByMaps = array();

    private array $teamsOverallByMaps      = array();
    private array $playerOverallByCategory = array();
    private array $teamsOverallByCategory  = array();
    private array $teamOverallPrimary      = array();

    public function __construct() {
        $this->_apiController = new ApiController();
    }

    /**
     * @description Set the teams
     * @param array $teams
     * @param $localTeam
     * @param $visitorTeam
     * @return void
     */
    public function setTeams(array $teams, $localTeam, $visitorTeam): void {
        $this->teams 		   = $teams;
        $this->teams["TeamFR"] = $localTeam;
        $this->teams["TeamVS"] = $visitorTeam;
    }

    /**
     * @description Set the maps
     * @param int $tournamentID
     * @param int $poolID
     * @return void
     */
    public function setMaps(int $tournamentID, int $poolID): void {
        $maps = json_decode($this->_apiController->getPoolMaps($tournamentID, $poolID), true);

        foreach ($maps as $map) {
            $this->maps[$map["id"]] = array(
                "name"   => $map["name"],
                "author" => $map["author"],
                "mapper" => $map["mapper"],
                "hash"   => $map["hash"],
                "tags"   => explode(",", $map["tags"])
            );

            if (array_key_exists($this->maps[$map["id"]]["tags"][0], $this->mapsByCategory)) {
                $this->mapsByCategory[$this->maps[$map["id"]]["tags"][0]]["maps"][] = $map["id"];
            }
        }
    }

    /**
     * @description Set the scores by maps
     * @param int $tournamentID
     * @param int $poolID
     * @return void
     */
    public function setScoresByMaps(int $tournamentID, int $poolID): void {
        if (empty($this->teams["teamVS"]["name"])) $teams = array($this->teams["teamFR"]["name"]);
        else                                       $teams = array($this->teams["teamFR"]["name"], $this->teams["teamVS"]["name"]);

        foreach ($this->maps as $mapID => $map) {
            $scores = json_decode($this->_apiController->getMapScores($tournamentID, $poolID, $mapID, $teams), true);

            if (!empty($scores)) {
                $rank = 1;

                foreach ($scores as $score) {
                    if ($score["team"] === $this->teams["teamFR"]["name"]) {
                        foreach ($this->teams["TeamFR"] as $team) {
                            if ($score["scoresaberID"] === $team["scoresaberID"]) {
                                $this->scoresByMaps[$mapID][$rank] = array(
                                    "name"         => $score["name"],
                                    "scoresaberID" => $score["scoresaberID"],
                                    "country"      => explode("-", $score["team"])[0],
                                    "score"        => $score["accuracy"],
                                    "best"         => $score["best"],
                                    "avg"          => $score["average"],
                                    "worst"        => $score["worst"],
                                    "miss"         => $score["miss"],
                                    "try"          => $score["try"],
                                    "mapLaunched"  => $score["mapLaunched"]
                                );

                                $rank++;
                            }
                        }
                    }


                    if ($score["team"] === $this->teams["teamVS"]["name"]) {
                        foreach ($this->teams["TeamVS"] as $team) {
                            if ($score["scoresaberID"] === $team["scoresaberID"]) {
                                $this->scoresByMaps[$mapID][$rank] = array(
                                    "name" 		   => $score["name"],
                                    "scoresaberID" => $score["scoresaberID"],
                                    "country"      => explode("-", $score["team"])[0],
                                    "score"        => $score["accuracy"],
                                    "best"         => $score["best"],
                                    "avg"          => $score["average"],
                                    "worst"        => $score["worst"],
                                    "miss"         => $score["miss"],
                                    "try"          => $score["try"],
                                    "mapLaunched"  => $score["mapLaunched"]
                                );

                                $rank++;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @description Set the overall of the teams and players
     * @return void
     */
    private function setOverall(): void {
        foreach ($this->maps as $mapID => $map) {
            $teamFRScoreNumber = 0;
            $teamVSScoreNumber = 0;

            $mapIsInCategory = false;

            $this->teamsOverallByMaps[$mapID] = array(
                "teamFR" => array(
                    "name"    => explode("-",$this->teams["teamFR"]["name"])[0],
                    "overall" => 0
                ),
                "teamVS" => array(
                    "name"    => explode("-",$this->teams["teamVS"]["name"])[0],
                    "overall" => 0
                )
            );
            if (in_array($mapID, $this->mapsByCategory[$map["tags"][0]]["maps"])) $mapIsInCategory = true;

            if (isset($this->scoresByMaps[$mapID])) {
                foreach ($this->scoresByMaps[$mapID] as $score) {
                    if ($score["country"] === $this->teamsOverallByMaps[$mapID]["teamFR"]["name"] && $teamFRScoreNumber < 4) {
                        $this->teamsOverallByMaps[$mapID]["teamFR"]["overall"] += $score["score"];
                        $teamFRScoreNumber++;
                    }
                    if ($score["country"] === $this->teamsOverallByMaps[$mapID]["teamVS"]["name"] && $teamVSScoreNumber < 4) {
                        $this->teamsOverallByMaps[$mapID]["teamVS"]["overall"] += $score["score"];
                        $teamVSScoreNumber++;
                    }

                    if ($mapIsInCategory) {
                        if (!isset($this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]])) {
                            if ($score["country"] === explode("-", $this->teams["teamFR"]["name"])[0]) {
                                foreach ($this->teams["TeamFR"] as $player) {
                                    if ($player["scoresaberID"] === $score["scoresaberID"]) {
                                        $this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]] = array(
                                            "overall" => $score["score"],
                                        );

                                        $this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]]["name"]    = $player["name"];
                                        $this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]]["country"] = $score["country"];
                                        break;
                                    }
                                }
                            }

                            if ($score["country"] === explode("-", $this->teams["teamVS"]["name"])[0]) {
                                foreach ($this->teams["TeamVS"] as $player) {
                                    if ($player["scoresaberID"] === $score["scoresaberID"]) {
                                        $this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]] = array(
                                            "overall"   => $score["score"],
                                            "mapNumber" => 1
                                        );

                                        $this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]]["name"]    = $player["name"];
                                        $this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]]["country"] = $score["country"];
                                        break;
                                    }
                                }
                            }
                        } else {
                            $this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]]["overall"] += $score["score"];
                            if (isset($this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]]["mapNumber"])) $this->playerOverallByCategory[$map["tags"][0]][$score["scoresaberID"]]["mapNumber"]++;
                        }
                    }
                }
            }
            $teamVSScoreNumber = ($teamVSScoreNumber < 4) ? $teamVSScoreNumber : 4;

            if ($teamFRScoreNumber > 0) $this->teamsOverallByMaps[$mapID]["teamFR"]["overall"] = round((($this->teamsOverallByMaps[$mapID]["teamFR"]["overall"] * 100) / 4), 2);
            if ($teamVSScoreNumber > 0) $this->teamsOverallByMaps[$mapID]["teamVS"]["overall"] = round((($this->teamsOverallByMaps[$mapID]["teamVS"]["overall"] * 100) / $teamVSScoreNumber), 2);
        }

        $this->setPlayerByCategoryOverall();
        $this->setTeamByCategoryOverall();
        $this->setTeamOverallPrimary();
    }

    /**
     * @description Set the overall of the players by category
     * @return void
     */
    private function setPlayerByCategoryOverall(): void {
        foreach ($this->playerOverallByCategory as $category => $players) {
            foreach ($players as $playerID => $score) {
                if (isset($score["mapNumber"])) $this->playerOverallByCategory[$category][$playerID]["overall"] = round((($score["overall"] * 100) / $score["mapNumber"]), 2);
                else                            $this->playerOverallByCategory[$category][$playerID]["overall"] = round((($score["overall"] * 100) / count($this->mapsByCategory[$category]["maps"])), 2);
            }

            uasort($this->playerOverallByCategory[$category], function($a, $b) {
                return $b["overall"] <=> $a["overall"];
            });
        }
    }

    /**
     * @description Set the overall of the teams by category
     * @return void
     */
    private function setTeamByCategoryOverall(): void {
        foreach ($this->playerOverallByCategory as $category => $players) {
            $teamFRScoreNumber = 0;
            $teamVSScoreNumber = 0;

            $this->teamsOverallByCategory[$category]["teamFR"] = array(
                    "name"    => explode("-", $this->teams["teamFR"]["name"])[0],
                    "overall" => 0
            );
            $this->teamsOverallByCategory[$category]["teamVS"] = array(
                "name"    => explode("-", $this->teams["teamVS"]["name"])[0],
                "overall" => 0
            );

            foreach ($players as $score) {
                if ($score["country"] === $this->teamsOverallByCategory[$category]["teamFR"]["name"] && $teamFRScoreNumber < 4) {
                    $this->teamsOverallByCategory[$category]["teamFR"]["overall"] += $score["overall"];
                    $teamFRScoreNumber++;
                }
                if ($score["country"] === $this->teamsOverallByCategory[$category]["teamVS"]["name"] && $teamVSScoreNumber < 4) {
                    $this->teamsOverallByCategory[$category]["teamVS"]["overall"] += $score["overall"];
                    $teamVSScoreNumber++;
                }
            }

            if ($teamFRScoreNumber > 0) $this->teamsOverallByCategory[$category]["teamFR"]["overall"] = round(($this->teamsOverallByCategory[$category]["teamFR"]["overall"] / 4), 2);
            if ($teamVSScoreNumber > 0) $this->teamsOverallByCategory[$category]["teamVS"]["overall"] = round(($this->teamsOverallByCategory[$category]["teamVS"]["overall"] / 4), 2);
        }
    }

    /**
     * @description Set the overall of the teams
     * @return void
     */
    private function setTeamOverallPrimary(): void {
        $teamFRScoreNumber = 0;
        $teamVSScoreNumber = 0;

        $this->teamOverallPrimary["teamFR"] = array(
            "name"    => explode("-", $this->teams["teamFR"]["name"])[0],
            "overall" => 0
        );
        $this->teamOverallPrimary["teamVS"] = array(
            "name"    => explode("-", $this->teams["teamVS"]["name"])[0],
            "overall" => 0
        );

        foreach ($this->teamsOverallByCategory as $teams) {
            foreach ($teams as $score) {
                if ($score["name"] === $this->teamOverallPrimary["teamFR"]["name"]) {
                    $this->teamOverallPrimary["teamFR"]["overall"] += $score["overall"];
                }
                if ($score["name"] === $this->teamOverallPrimary["teamVS"]["name"]) {
                    $this->teamOverallPrimary["teamVS"]["overall"] += $score["overall"];
                }
            }

            $teamFRScoreNumber++;
            if ($teams["teamVS"]["overall"] > 0) $teamVSScoreNumber++;
        }

        if ($teamFRScoreNumber > 0) $this->teamOverallPrimary["teamFR"]["overall"] = round(($this->teamOverallPrimary["teamFR"]["overall"] / $teamFRScoreNumber), 2);
        if ($teamVSScoreNumber > 0) $this->teamOverallPrimary["teamVS"]["overall"] = round(($this->teamOverallPrimary["teamVS"]["overall"] / $teamVSScoreNumber), 2);
    }

    /**
     * @description Make the calculus
     * @return void
     */
    public function makeCalculus(): void {
        $this->setOverall();
    }

    /**
     * @description Make the primary content
     * @return array
     */
    public function makePrimary(): array {
        $primary 			 = array();
        $primary 			 += $this->teamOverallPrimary;
        $primary["category"] = array();
        $primary["category"] += $this->teamsOverallByCategory;

        foreach ($this->playerOverallByCategory as $category => $players) {
            $primary["category"][$category]["leaderboard"] = array();
            $primary["category"][$category]["leaderboard"] += $players;
        }

        return $primary;
    }

    /**
     * @description Make the maps content
     * @return array
     */
    public function makeMaps(): array {
        $maps = array();
        $maps += $this->maps;

        foreach ($this->teamsOverallByMaps as $mapID => $teams) {
            $maps[$mapID]["teams"] = array();
            $maps[$mapID]["teams"] += $teams;
        }

        foreach ($this->scoresByMaps as $mapID => $scores) {
            $maps[$mapID]["leaderboard"] = array();
            $maps[$mapID]["leaderboard"] += $scores;
        }

        return $maps;
    }
}