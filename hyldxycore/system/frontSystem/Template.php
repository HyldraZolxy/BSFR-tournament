<?php
namespace hyldxycore\system\frontSystem;

use hyldxycore\system\backSystem\Tools;

class Template {
    private Tools $_tools;

    /*************
     * Navigation
     *************/
    private string $active_link_class = "link-active";

    /**************
     * Page Content
     **************/
    /****************************
     * Page Content - Date
     ****************************/
    private string $dataDayLeftOnGoing = "text-green";
    private string $dataDayLeftSoon    = "text-orange";
    private string $dataDayLeftEnded   = "text-red";
    private string $dataDayLeftDefault = "text-default";

    public function __construct() {
        $this->_tools = new Tools();
    }

    /*************
     * Navigation
     *************/
    /**
     * @description Create all the navigation links
     * @param array $navs
     * @return string
     */
    public function createNavLink(array $navs): string {
        $navsHTML = "";

        foreach ($navs as $nav) {
            $navHTML = "<a href=\"{$nav["url"]}\"";

            $navHTML .= (isset($nav["target"])) ? " target=\"{$nav["target"]}\"" : "";

            $class    = $nav["class"] . (isset($nav["active"]) && $nav["active"] ? " {$this->active_link_class}" : "");
            $navHTML .= " class=\"$class\"";

            $navHTML .= " title=\"{$nav["title"]}\"";

            $navHTML .= (isset($nav["onclick"])) ? " onclick=\"{$nav["onclick"]}\"" : "";
            $navHTML .= ">";

            $navHTML .= $nav["content"];
            $navHTML .= "</a>";

            $navsHTML .= $navHTML;
        }

        return $navsHTML;
    }

    /**
     * @description Create the return button
     * @param string $link
     * @return string
     */
    public function createReturnButton(string $link): string {
        return "<a href=\"$link\" class=\"btn btn-default btn-return\"><i class=\"fa-solid fa-arrow-left\"></i> Retour</a>";
    }

    /**************
     * Page Content
     **************/
    /************************
     * Page Content - General
     ************************/
    /**
     * @description Styling the date with "On going", "Finished", "X days left", etc ...
     * @param array $data
     * @return array
     */
    public function makeDateStyle(array $data): array {
        $dataDayLeft      = "On going";
        $dataDayLeftEmote = "<i class=\"fa fa-clock-o\"></i>";
        $dataDayLeftClass = $this->dataDayLeftOnGoing;

        $startingDate  = $this->_tools->checkDaysLeft($data["starting_at"] );
        $finishingDate = $this->_tools->checkDaysLeft($data["finishing_at"]);

        $hoursLeftStart  = $startingDate ["hours"];
        $daysLeftStart   = $startingDate ["days"];
        $weeksLeftStart  = $startingDate ["weeks"];
        $monthsLeftStart = $startingDate ["months"];

        $hoursLeftEnd  = $finishingDate["hours"];
        $daysLeftEnd   = $finishingDate["days"];
        $weeksLeftEnd  = $finishingDate["weeks"];
        $monthsLeftEnd = $finishingDate["months"];

        if ($data["status"] === $this->_tools->statusConfig["ended"]) {
            $dataDayLeft      = "Ended";
            $dataDayLeftEmote = "<i class=\"fa-solid fa-lock\"></i>";
            $dataDayLeftClass = $this->dataDayLeftEnded;
        }

        elseif ($data["status"] === $this->_tools->statusConfig["soon"]) {
            if     ($monthsLeftStart > 0) $dataDayLeft = "Start in " . $monthsLeftStart . " month(s)";
            elseif ($weeksLeftStart > 0)  $dataDayLeft = "Start in " . $weeksLeftStart  . " week(s)";
            elseif ($daysLeftStart > 0)   $dataDayLeft = "Start in " . $daysLeftStart   . " day(s)";
            else                          $dataDayLeft = "Start in " . $hoursLeftStart  . " hour(s)";

            $dataDayLeftEmote = "<i class=\"fa-regular fa-calendar-days\"></i>";
            $dataDayLeftClass = $this->dataDayLeftDefault;
        }

        elseif ($data["status"] === $this->_tools->statusConfig["now"]) {
            if     ($monthsLeftEnd > 0) $dataDayLeft = "End in " . $monthsLeftEnd . " month(s)";
            elseif ($weeksLeftEnd > 0)  $dataDayLeft = "End in " . $weeksLeftEnd  . " week(s)";
            elseif ($daysLeftEnd > 0)   $dataDayLeft = "End in " . $daysLeftEnd   . " day(s)";
            else                        $dataDayLeft = "End in " . $hoursLeftEnd  . " hour(s)";

            if ($monthsLeftEnd <= 0 && $weeksLeftEnd <= 0) {
                $dataDayLeftEmote = "<i class=\"fa-solid fa-exclamation\"></i>";
                $dataDayLeftClass = $this->dataDayLeftSoon;
            }
        }

        return array(
            "dataDayLeft"      => $dataDayLeft,
            "dataDayLeftEmote" => $dataDayLeftEmote,
            "dataDayLeftClass" => $dataDayLeftClass
        );
    }

    /**
     * @description Create a box for an event
     * @param array $data
     * @param array $date
     * @param string $URI
     * @return string
     */
    public function makeBoxEvent(array $data, array $date, string $URI): string {
        return "<div class=\"box-full flex flex-align-center\">
                    <div class=\"box-picture-full\">
                        <img src=\"{$data["picture"]}\" alt=\"Picture represantation of the event\" />
                    </div>
                    <div class=\"box-information-full flex flex-column\">
                        <span class=\"box-title-full text-cut\">{$data["name"]}</span>
                        <span class=\"box-description-full text-cut\">{$data["description"]}</span>
                        <div class=\"box-time-slot-full text-cut {$date["dataDayLeftClass"]}\">
                            {$date["dataDayLeftEmote"]}
                            <span>{$date["dataDayLeft"]}</span>
                        </div>
                    </div>
                    <span class=\"box-button-full\">
                        <a href=\"$URI\" class=\"btn btn-default\">View</a>
                    </span>
                </div>";
    }

    /**
     * @description Create a box information for an event
     * @param array $data
     * @param array $date
     * @return string
     */
    public function makeInfoBoxEvent(array $data, array $date): string {
        $rankHTML = "";

        if (isset($data["rank"]) && $data["rank"] >= 0) {
            $rank     = ($data["rank"] === 0) ? "NC" : $data["rank"];
            $rankHTML = "<div class=\"box-rank box-full box-rounded flex flex-center flex-align-center\">
                             <p><i class=\"fa-solid fa-flag-checkered\"></i> $rank</p>
                         </div>";
        }
        elseif (isset($data["teamFRPoint"]) && $data["teamFRPoint"] >= 0) {
            $teamVS        = (!empty($data["teamVS"])) ? explode("-", $data["teamVS"])[0] : "";
            $teamVSPoint   = ($data["teamVSPoint"] >= 0) ? " - " . $data["teamVSPoint"] : "";
            $teamVSPicture = (!empty($teamVS)) ? "<img src=\"/pictures/countryFlags/$teamVS.png\" alt=\"Country Flag\" />" : "";

            $rankHTML = "<div class=\"box-rank box-full box-rounded flex flex-center flex-align-center\">
                            <img src=\"/pictures/countryFlags/FR.png\" alt=\"Country Flag\" />
                            <p>{$data["teamFRPoint"]}$teamVSPoint</p>
                            $teamVSPicture
                        </div>";
        }

        return "<div class=\"flex flex-row\">
                    <div class=\"box-full box-rounded box-information flex flex-column flex-\">
                        <div class=\"flex flex-row flex-spaced\" style=\"flex-grow: 2;\">
                            <p><i class=\"fa-solid fa-circle-info\"></i></p>
                            <p><span class=\"{$date["dataDayLeftClass"]}\">{$date["dataDayLeftEmote"]} {$date["dataDayLeft"]}</span></p>
                        </div>
                        <p><i class=\"fa fa-clock-o\"></i> <strong>Date:</strong> {$data["starting_at"]} - {$data["finishing_at"]}</p>
                        <p><i class=\"fa-solid fa-book\"></i> <strong>Description:</strong> {$data["description"]}</p>
                    </div>
                    $rankHTML
                </div>";
    }

    /**
     * @description Format the team
     * @param array $players
     * @return string
     */
    public function makeTeamFullBox(array $players): string {
        $html            = "";
        $numberOfPlayers = count($players);

        foreach ($players as $player) {
            $playerAvatar = $this->_tools->parseScoresaberPicture($player["scoresaberID"]);
            $playerRole   = ($player["isCaptain"])? (($numberOfPlayers > 8)? "Captain" : "Captain | Player") : "Player";

            $html .= $this->makeTeamBoxEvent($player, $playerAvatar, $playerRole);
        }

        return $html;
    }

    /**
     * @description Create a team box for an event
     * @param array $player
     * @param string $playerAvatar
     * @param string $playerRole
     * @return string
     */
    public function makeTeamBoxEvent(array $player, string $playerAvatar, string $playerRole): string {
        return "<div class=\"box-team-player\">
                    <img src=\"$playerAvatar\" class=\"box-team-player-avatar\" alt=\"Player avatar\" />
                    <p class=\"box-team-player-name text-cut\">
                        <a href=\"https://scoresaber.com/u/{$player["scoresaberID"]}\" class=\"link-default-color\">{$player["name"]}</a>
                    </p>
                    <p class=\"box-team-player-role text-glow-cyan\">$playerRole</p>
                </div>";
    }

    /*************************
     * Page Content - Modules
     *************************/
    /**
     * @description Format the module title
     * @param array $module
     * @return string
     */
    public function getModuleTitle(array $module): string {
        $html = "";

        if ($module["link"] !== null) {
            $html .= "<p class=\"module-title-p text-cut\">";
            $html .= implode(" / ", $module["link"]);
            $html .= "</p>";
        }

        $html .= "<h1 class=\"module-title-h1 text-cut\">{$module["name"]}</h1>";

        return $html;
    }

    /****************************
     * Page Content - Tournaments
     ****************************/
    /**
     * @description Format the tournament
     * @param array $tournament
     * @return string
     */
    public function makeTournamentBox(array $tournament): string {
        $dateFetched = $this->makeDateStyle($tournament);
        $URI         = "/tournament/" . $tournament["id"];

        return $this->makeBoxEvent($tournament, $dateFetched, $URI);
    }

    /**
     * @description Format the tournament information
     * @param array $tournament
     * @return string
     */
    public function makeInfoTournamentBox(array $tournament): string {
        $dateFetched = $this->makeDateStyle($tournament);

        return $this->makeInfoBoxEvent($tournament, $dateFetched);
    }

    /**********************
     * Page Content - Pool
     *********************/
    /**
     * @description Format the pool
     * @param array $tournament
     * @return string
     */
    public function makePoolBox(array $pool): string {
        $dateFetched = $this->makeDateStyle($pool);
        $URI         = "/tournament/" . $pool["tournamentID"] . "/pool/" . $pool["poolID"] ;

        return $this->makeBoxEvent($pool, $dateFetched, $URI);
    }

    /**
     * @description Format the pool information
     * @param array $pool
     * @return string
     */
    public function makeInfoPoolBox(array $pool): string {
        $dateFetched = $this->makeDateStyle($pool);

        return $this->makeInfoBoxEvent($pool, $dateFetched);
    }

    /**********************************
     * Page Content - Scores - Overall
     **********************************/
    /**
     * @description Format the overall
     * @param array $overallData
     * @return string
     */
    public function makeOverall(array $overallData): string {
        $html = "";

        $teams = array();
        $teams["teamFR"] = $overallData["teamFR"];
        $teams["teamVS"] = $overallData["teamVS"];

        $html .= $this->makeOverallHeader($teams);
        $html .= $this->makeOverallContent($overallData["category"]);

        return $html;
    }

    /**
     * @description Format the overall header
     * @param array $teams
     * @return string
     */
    public function makeOverallHeader(array $teams): string {
        $backgroundClass  = "collapsible-background";
        $backgroundImages = "<img src=\"/pictures/countryFlags/{$teams["teamFR"]["name"]}.png\"  alt=\"country flag\"/>";
        $htmlScores       = "<p class=\"flex-grow-2\">{$teams["teamFR"]["overall"]}%</p>";

        if (!empty($teams["teamVS"]["name"])) {
            $backgroundClass  = "collapsible-background-dual";
            $backgroundImages = "<img src=\"/pictures/countryFlags/{$teams["teamFR"]["name"]}.png\"  alt=\"country flag\"/>
                                 <img src=\"/pictures/countryFlags/{$teams["teamVS"]["name"]}.png\"  alt=\"country flag\"/>";

            $htmlScores = "<p class=\"flex-grow-2\">{$teams["teamFR"]["overall"]}%</p>
                           <p>--</p>
                           <p class=\"flex-grow-2\">{$teams["teamVS"]["overall"]}%</p>";
        }

        return "<div class=\"wrapper collapsible\">
                    <div class=\"$backgroundClass\">
                        $backgroundImages
                    </div>
                    <div class=\"collapsible-content\">
                        <p class=\"collapsible-content-title\">Overall</p>
                        <div class=\"collapsible-content-information flex flex-row flex-spaced flex-align-center\">
                            $htmlScores
                        </div>
                    </div>
                </div>";
    }

    /**
     * @description Format the overall content
     * @param array $categories
     * @return string
     */
    public function makeOverallContent(array $categories): string {
        $html = "<div class=\"wrapper collapsible-content-line\">";

        foreach ($categories as $category => $scores) {
            $html .= $this->makeOverallSecondaryHeader($category, $scores);
            $html .= $this->makeOverallSecondaryContent($scores["leaderboard"]);
        }

        $html .= "</div>";

        return $html;
    }

    /**
     * @description Format the overall secondary header
     * @param string $category
     * @param array $scores
     * @return string
     */
    public function makeOverallSecondaryHeader(string $category, array $scores): string {
        $htmlScoreVS = "<p>{$scores["teamFR"]["overall"]}%</p>";

        if (!empty($scores["teamVS"]["name"])) {
            $htmlScoreVS = "<p class=\"flex-grow-2\">{$scores["teamFR"]["overall"]}%</p>
                            <p>--</p>
                            <p class=\"flex-grow-2\">{$scores["teamVS"]["overall"]}%</p>";
        }

        return "<div class=\"wrapper collapsible-secondary collapsible-secondary-line\">
                    <p class=\"collapsible-secondary-line-title\">$category</p>
                    <div class=\"collapsible-secondary-line-information flex flex-row flex-spaced flex-align-center\">
                        $htmlScoreVS
                    </div>
                </div>";
    }

    /**
     * @description Format the overall secondary content
     * @param array $leaderboard
     * @return string
     */
    public function makeOverallSecondaryContent(array $leaderboard): string {
        $html = "<div class=\"wrapper collapsible-content-line\">";

        $rank = 1;
        foreach ($leaderboard as $player) {
            $html .= "<div class=\"wrapper collapsible-secondary-line background-gray\">
                        <p class=\"collapsible-secondary-line-title\">
                            <img class=\"full-container\" src=\"/pictures/countryFlags/{$player["country"]}.png\"  alt=\"country flag\"/>
                        </p>
                        <div class=\"collapsible-secondary-line-information flex flex-row flex-spaced flex-align-center\">
                            <p class=\"collapsible-secondary-line-information-player-rank\">#$rank</p>
                            <p class=\"collapsible-secondary-line-information-player-username text-cut\">{$player["name"]}</p>
                            <p class=\"flex-grow-2 text-right\">{$player["overall"]}%</p>
                        </div>
                    </div>";

            $rank++;
        }

        $html .= "</div>";

        return $html;
    }

    /******************************
     * Page Content - Scores - Maps
     ******************************/
    /**
     * @description Format the maps
     * @param array $maps
     * @return string
     */
    public function makeMapPrimary(array $maps): string {
        $html = "";

        if (count($maps) > 0) {
            foreach ($maps as $map) {
                $html .= $this->makeMapHeader($map);

                $html .= "<div class=\"wrapper collapsible-content-line\">";
                if (isset($map["teams"]) && count($map["teams"]) > 0) $html .= $this->makeMapOverallContent($map["teams"]);
                if (isset($map["leaderboard"]) && count($map["leaderboard"]) > 0) {
                    foreach ($map["leaderboard"] as $rank => $player) {
                        $html .= $this->makeMapLeaderboardContent($rank, $player);
                    }
                }
                $html .= "</div>";
            }
        }

        return $html;
    }

    /**
     * @description Format the map header
     * @param array $map
     * @return string
     */
    public function makeMapHeader(array $map): string {
        $map["hash"] = strtolower($map["hash"]);

        return "<div class=\"wrapper collapsible\">
                    <div class=\"collapsible-background\">
                        <img src=\"https://eu.cdn.beatsaver.com/{$map["hash"]}.jpg\"  alt=\"cover song\"/>
                    </div>
                    <div class=\"collapsible-content\">
                        <p class=\"collapsible-content-title\">{$map["author"]} - [{$map["mapper"]}]</p>
                        <div class=\"collapsible-content-information flex flex-row flex-spaced flex-align-center\">
                            <p class=\"flex-grow-2 text-left text-cut\">{$map["name"]}</p>
                            <p>
                                <span class=\"songTags\">
                                    <span class=\"songTag {$map["tags"][0]}\">{$map["tags"][0]}</span>
                                    <span class=\"songTag {$map["tags"][1]}\">{$map["tags"][1]}</span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>";
    }

    /**
     * @description Format the map overall content
     * @param array $teams
     * @return string
     */
    public function makeMapOverallContent(array $teams): string {
        $html = "<img class=\"full-container\" src=\"/pictures/countryFlags/{$teams["teamFR"]["name"]}.png\"  alt=\"country flag\"/>
                 <p>{$teams["teamFR"]["overall"]}%</p>";

        if (!empty($teams["teamVS"]["name"])) {
            $html = "<img class=\"full-container\" src=\"/pictures/countryFlags/{$teams["teamFR"]["name"]}.png\"  alt=\"country flag\"/>
                     <p class=\"flex-grow-2\">{$teams["teamFR"]["overall"]}%</p>
                     <p>--</p>
                     <p class=\"flex-grow-2\">{$teams["teamVS"]["overall"]}%</p>
                     <img class=\"full-container\" src=\"/pictures/countryFlags/{$teams["teamVS"]["name"]}.png\"  alt=\"country flag\"/>";
        }

        return "<div class=\"wrapper collapsible-secondary-line\">
                    <p class=\"collapsible-secondary-line-title\">Overall</p>
                    <div class=\"collapsible-secondary-line-information flex flex-row flex-spaced flex-align-center\">
                        $html
                    </div>
                </div>";
    }

    /**
     * @description Format the map leaderboard content
     * @param int $rank
     * @param array $player
     * @return string
     */
    public function makeMapLeaderboardContent(int $rank, array $player): string {
        $player["score"] = round(($player["score"] * 100), 2);
        $player["best"]  = round(($player["best"]  * 100), 2);
        $player["avg"]   = round(($player["avg"]   * 100), 2);
        $player["worst"] = round(($player["worst"] * 100), 2);

        return "<div class=\"wrapper collapsible-secondary collapsible-secondary-line collapsible-secondary-line-information-player background-gray\">
                    <p class=\"collapsible-secondary-line-title\">
                        <img class=\"full-container\" src=\"/pictures/countryFlags/{$player["country"]}.png\"  alt=\"country flag\"/>
                    </p>
                    <div class=\"collapsible-secondary-line-information flex flex-row flex-spaced flex-align-center\">
                        <p class=\"collapsible-secondary-line-information-player-rank\">#$rank</p>
                        <p class=\"collapsible-secondary-line-information-player-username text-cut\">{$player["name"]}</p>
                        <p class=\"collapsible-secondary-line-information-player-score flex-grow-2 text-right\">{$player["score"]}%</p>
                    </div>
                </div>
                <div class=\"wrapper collapsible-content-line\">
                    <div class=\"wrapper collapsible-secondary-line background-gray\">
                        <div class=\"collapsible-secondary-line-information info-scores flex flex-row flex-spaced flex-align-center\">
                            <div class=\"score-by flex flex-row\">
                                <p class=\"best-score\">Best: {$player["best"]}%</p>
                                <p class=\"average-score\">Avg: {$player["avg"]}%</p>
                                <p class=\"lowest-score\">Low: {$player["worst"]}%</p>
                            </div>
                            <p class=\"missed-notes\">Miss: {$player["miss"]}</p>
                            <p class=\"map-try\">Try: {$player["try"]}</p>
                            <p class=\"map-launched\">Launched: {$player["mapLaunched"]}</p>
                        </div>
                    </div>
                </div>";
    }
}