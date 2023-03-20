<?php
namespace extensions\qualification;
use extensions\login\Login;
use hyldxycore\system\Template;
use extensions\api\Api;

class DTierQualification {
    private string $HTMLPage;
    private string $leaderboardHTML;
    private Login $_login;
    private Template $_template;
    private Api $_api;
    private int $poolID = 1;
    private int $mapNumberInCategory = 3;
    private int $mapNumberForAllCategory = 12;

    public function __construct() {
        $this->HTMLPage = file_get_contents(join(DS, array(WWW, "html", "index.html")));
        $this->leaderboardHTML = file_get_contents(join(DS, array(WWW, "html", "leaderboard.html")));
        $this->_template = new Template();
        $this->_login = new Login();
        $this->_api = new Api();

        $this->_template->replaceNavContent($this->HTMLPage, "DTierQualification");
    }

    private function getLeaderboard() {
        $allMapsScores = $this->_api->getAllScore($this->poolID);
        $allMaps = json_decode($this->_api->getMaps($this->poolID), true);
        $allUsers = json_decode($this->_api->getUser(), true);

        usleep(25);

        $leaderboardAccuracy = [];
        $leaderboardMidspeed = [];
        $leaderboardTechnical = [];
        $leaderboardSpeed = [];
        $leaderboardOverall = [];

        for ($i = 0; $i < count($allMapsScores); $i++) {
            foreach($allMaps as $allMapsValues) {
                if ($allMapsValues["mapID"] === $allMapsScores[$i]["mapID"]) {
                    if (in_array($allMapsValues["mapStyle"], $GLOBALS["accuracy"], true)) {
                        $leaderboardAccuracy[$allMapsScores[$i]["scoresaberID"]][$allMapsValues["mapID"]] = $allMapsScores[$i];
                    }
                    if (in_array($allMapsValues["mapStyle"], $GLOBALS["midspeed"], true)) {
                        $leaderboardMidspeed[$allMapsScores[$i]["scoresaberID"]][$allMapsValues["mapID"]] = $allMapsScores[$i];
                    }
                    if (in_array($allMapsValues["mapStyle"], $GLOBALS["technical"], true)) {
                        $leaderboardTechnical[$allMapsScores[$i]["scoresaberID"]][$allMapsValues["mapID"]] = $allMapsScores[$i];
                    }
                    if (in_array($allMapsValues["mapStyle"], $GLOBALS["speed"], true)) {
                        $leaderboardSpeed[$allMapsScores[$i]["scoresaberID"]][$allMapsValues["mapID"]] = $allMapsScores[$i];
                    }

                    $leaderboardOverall[$allMapsScores[$i]["scoresaberID"]][$allMapsValues["mapID"]] = $allMapsScores[$i];
                }
            }
        }

        foreach($leaderboardAccuracy as $playersIDKeys => $mapsIDValues) {
            $accuracyGlobal = 0;

            foreach($mapsIDValues as $mapsIDKeys => $scoresValues) {
                if ($accuracyGlobal === 0) {
                    $accuracyGlobal = $scoresValues["accuracy"] * 100;
                    continue;
                }

                $accuracyGlobal = $accuracyGlobal + ($scoresValues["accuracy"] * 100);
            }

            $accuracyGlobal = $accuracyGlobal / $this->mapNumberInCategory;
            $leaderboardAccuracy[$playersIDKeys]["accuracyGlobal"] = $accuracyGlobal;
        }

        foreach($leaderboardMidspeed as $playersIDKeys => $mapsIDValues) {
            $accuracyGlobal = 0;

            foreach($mapsIDValues as $mapsIDKeys => $scoresValues) {
                if ($accuracyGlobal === 0) {
                    $accuracyGlobal = $scoresValues["accuracy"] * 100;
                    continue;
                }

                $accuracyGlobal = $accuracyGlobal + ($scoresValues["accuracy"] * 100);
            }

            $accuracyGlobal = $accuracyGlobal / $this->mapNumberInCategory;
            $leaderboardMidspeed[$playersIDKeys]["accuracyGlobal"] = $accuracyGlobal;
        }

        foreach($leaderboardTechnical as $playersIDKeys => $mapsIDValues) {
            $accuracyGlobal = 0;

            foreach($mapsIDValues as $mapsIDKeys => $scoresValues) {
                if ($accuracyGlobal === 0) {
                    $accuracyGlobal = $scoresValues["accuracy"] * 100;
                    continue;
                }

                $accuracyGlobal = $accuracyGlobal + ($scoresValues["accuracy"] * 100);
            }

            $accuracyGlobal = $accuracyGlobal / $this->mapNumberInCategory;
            $leaderboardTechnical[$playersIDKeys]["accuracyGlobal"] = $accuracyGlobal;
        }

        foreach($leaderboardSpeed as $playersIDKeys => $mapsIDValues) {
            $accuracyGlobal = 0;

            foreach($mapsIDValues as $mapsIDKeys => $scoresValues) {
                if ($accuracyGlobal === 0) {
                    $accuracyGlobal = $scoresValues["accuracy"] * 100;
                    continue;
                }

                $accuracyGlobal = $accuracyGlobal + ($scoresValues["accuracy"] * 100);
            }

            $accuracyGlobal = $accuracyGlobal / $this->mapNumberInCategory;
            $leaderboardSpeed[$playersIDKeys]["accuracyGlobal"] = $accuracyGlobal;
        }

        foreach($leaderboardOverall as $playersIDKeys => $mapsIDValues) {
            $accuracyGlobal = 0;

            foreach($mapsIDValues as $mapsIDKeys => $scoresValues) {
                if ($accuracyGlobal === 0) {
                    $accuracyGlobal = $scoresValues["accuracy"] * 100;
                    continue;
                }

                $accuracyGlobal = $accuracyGlobal + ($scoresValues["accuracy"] * 100);
            }

            $accuracyGlobal = $accuracyGlobal / $this->mapNumberForAllCategory;
            $leaderboardOverall[$playersIDKeys]["accuracyGlobal"] = $accuracyGlobal;
        }

        uasort($leaderboardAccuracy, function($a, $b) {
            return $b["accuracyGlobal"] <=> $a["accuracyGlobal"];
        });
        uasort($leaderboardMidspeed, function($a, $b) {
            return $b["accuracyGlobal"] <=> $a["accuracyGlobal"];
        });
        uasort($leaderboardTechnical, function($a, $b) {
            return $b["accuracyGlobal"] <=> $a["accuracyGlobal"];
        });
        uasort($leaderboardSpeed, function($a, $b) {
            return $b["accuracyGlobal"] <=> $a["accuracyGlobal"];
        });
        uasort($leaderboardOverall, function($a, $b) {
            return $b["accuracyGlobal"] <=> $a["accuracyGlobal"];
        });

        $leaderboardHTMLAccuracy = "";
        $leaderboardHTMLMidspeed = "";
        $leaderboardHTMLTechnical = "";
        $leaderboardHTMLSpeed = "";
        $leaderboardHTMLOverall = "";

        foreach($leaderboardAccuracy as $playersIDKeys => $mapsIDValues) {
            $leaderboardHTMLAccuracy .= "<tr>";

            for ($i = 0; $i < count($allUsers); $i++) {
                if ($allUsers[$i]["scoresaberID"] === strval($playersIDKeys)) {
                    $extension = substr($allUsers[$i]["profilPicture"], 0, 2);
                    if ($extension === "a_") $extension = ".gif";
                    else $extension = ".png";

                    $leaderboardHTMLAccuracy .= "<td class=\"leaderboardAvatar\">
                        <img src=\"https://cdn.discordapp.com/avatars/" . $allUsers[$i]["discordID"] . "/" . $allUsers[$i]["profilPicture"] . $extension . "\"  alt=\"PlayerAvatar\"/>
                    </td>";
                    $leaderboardHTMLAccuracy .= "<td class=\"leaderboardUsername\">
                        " . $allUsers[$i]["pseudo"] . "
                    </td>";
                }
            }

            $leaderboardHTMLAccuracy .= "<td class=\"leaderboardAccuracy\">
                " . number_format($mapsIDValues["accuracyGlobal"], 2) . "%
            </td>";
            $leaderboardHTMLAccuracy .= "<td class=\"leaderboardWeightedAccuracy\"></td>";
            $leaderboardHTMLAccuracy .= "</tr>";
        }

        foreach($leaderboardMidspeed as $playersIDKeys => $mapsIDValues) {
            $leaderboardHTMLMidspeed .= "<tr>";

            for ($i = 0; $i < count($allUsers); $i++) {
                if ($allUsers[$i]["scoresaberID"] === strval($playersIDKeys)) {
                    $extension = substr($allUsers[$i]["profilPicture"], 0, 2);
                    if ($extension === "a_") $extension = ".gif";
                    else $extension = ".png";

                    $leaderboardHTMLMidspeed .= "<td class=\"leaderboardAvatar\">
                        <img src=\"https://cdn.discordapp.com/avatars/" . $allUsers[$i]["discordID"] . "/" . $allUsers[$i]["profilPicture"] . $extension . "\"  alt=\"PlayerAvatar\"/>
                    </td>";
                    $leaderboardHTMLMidspeed .= "<td class=\"leaderboardUsername\">
                        " . $allUsers[$i]["pseudo"] . "
                    </td>";
                }
            }

            $leaderboardHTMLMidspeed .= "<td class=\"leaderboardAccuracy\">
                " . number_format($mapsIDValues["accuracyGlobal"], 2) . "%
            </td>";
            $leaderboardHTMLMidspeed .= "<td class=\"leaderboardWeightedAccuracy\"></td>";
            $leaderboardHTMLMidspeed .= "</tr>";
        }

        foreach($leaderboardTechnical as $playersIDKeys => $mapsIDValues) {
            $leaderboardHTMLTechnical .= "<tr>";

            for ($i = 0; $i < count($allUsers); $i++) {
                if ($allUsers[$i]["scoresaberID"] === strval($playersIDKeys)) {
                    $extension = substr($allUsers[$i]["profilPicture"], 0, 2);
                    if ($extension === "a_") $extension = ".gif";
                    else $extension = ".png";

                    $leaderboardHTMLTechnical .= "<td class=\"leaderboardAvatar\">
                        <img src=\"https://cdn.discordapp.com/avatars/" . $allUsers[$i]["discordID"] . "/" . $allUsers[$i]["profilPicture"] . $extension . "\"  alt=\"PlayerAvatar\"/>
                    </td>";
                    $leaderboardHTMLTechnical .= "<td class=\"leaderboardUsername\">
                        " . $allUsers[$i]["pseudo"] . "
                    </td>";
                }
            }

            $leaderboardHTMLTechnical .= "<td class=\"leaderboardAccuracy\">
                " . number_format($mapsIDValues["accuracyGlobal"], 2) . "%
            </td>";
            $leaderboardHTMLTechnical .= "<td class=\"leaderboardWeightedAccuracy\"></td>";
            $leaderboardHTMLTechnical .= "</tr>";
        }

        foreach($leaderboardSpeed as $playersIDKeys => $mapsIDValues) {
            $leaderboardHTMLSpeed .= "<tr>";

            for ($i = 0; $i < count($allUsers); $i++) {
                if ($allUsers[$i]["scoresaberID"] === strval($playersIDKeys)) {
                    $extension = substr($allUsers[$i]["profilPicture"], 0, 2);
                    if ($extension === "a_") $extension = ".gif";
                    else $extension = ".png";

                    $leaderboardHTMLSpeed .= "<td class=\"leaderboardAvatar\">
                        <img src=\"https://cdn.discordapp.com/avatars/" . $allUsers[$i]["discordID"] . "/" . $allUsers[$i]["profilPicture"] . $extension . "\"  alt=\"PlayerAvatar\"/>
                    </td>";
                    $leaderboardHTMLSpeed .= "<td class=\"leaderboardUsername\">
                        " . $allUsers[$i]["pseudo"] . "
                    </td>";
                }
            }

            $leaderboardHTMLSpeed .= "<td class=\"leaderboardAccuracy\">
                " . number_format($mapsIDValues["accuracyGlobal"], 2) . "%
            </td>";
            $leaderboardHTMLSpeed .= "<td class=\"leaderboardWeightedAccuracy\"></td>";
            $leaderboardHTMLSpeed .= "</tr>";
        }

        foreach($leaderboardOverall as $playersIDKeys => $mapsIDValues) {
            $leaderboardHTMLOverall .= "<tr>";

            for ($i = 0; $i < count($allUsers); $i++) {
                if ($allUsers[$i]["scoresaberID"] === strval($playersIDKeys)) {
                    $extension = substr($allUsers[$i]["profilPicture"], 0, 2);
                    if ($extension === "a_") $extension = ".gif";
                    else $extension = ".png";

                    $leaderboardHTMLOverall .= "<td class=\"leaderboardAvatar\">
                        <img src=\"https://cdn.discordapp.com/avatars/" . $allUsers[$i]["discordID"] . "/" . $allUsers[$i]["profilPicture"] . $extension . "\"  alt=\"PlayerAvatar\"/>
                    </td>";
                    $leaderboardHTMLOverall .= "<td class=\"leaderboardUsername\">
                        " . $allUsers[$i]["pseudo"] . "
                    </td>";
                }
            }

            $leaderboardHTMLOverall .= "<td class=\"leaderboardAccuracy\">
                " . number_format($mapsIDValues["accuracyGlobal"], 2) . "%
            </td>";
            $leaderboardHTMLOverall .= "</tr>";
        }

        $this->HTMLPage = $this->_template->regexBalise($this->HTMLPage, "LEADERBOARD_ACCURACY", $leaderboardHTMLAccuracy);
        $this->HTMLPage = $this->_template->regexBalise($this->HTMLPage, "LEADERBOARD_MIDSPEED", $leaderboardHTMLMidspeed);
        $this->HTMLPage = $this->_template->regexBalise($this->HTMLPage, "LEADERBOARD_TECHNICAL", $leaderboardHTMLTechnical);
        $this->HTMLPage = $this->_template->regexBalise($this->HTMLPage, "LEADERBOARD_SPEED", $leaderboardHTMLSpeed);
        $this->HTMLPage = $this->_template->regexBalise($this->HTMLPage, "LEADERBOARD_OVERALL", $leaderboardHTMLOverall);
    }

    public function send(): string {
        if ($this->_login->isAuthenticated() && $this->_login->asAuthorization()) $this->HTMLPage = $this->_template->regexBalise($this->HTMLPage, "CONTENT", $this->leaderboardHTML);
        if ($this->_login->isAuthenticated() && $this->_login->asAuthorization()) $this->getLeaderboard();
        return $this->HTMLPage;
    }
}