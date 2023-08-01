<?php
namespace extensions\pool;

use extensions\api\ApiController;
use extensions\login\LoginController;
use extensions\score\ScoreController;
use hyldxycore\system\frontSystem\ErrorController;
use hyldxycore\system\frontSystem\NavController;
use hyldxycore\system\frontSystem\PageController;
use hyldxycore\system\frontSystem\Template;

enum PoolStatus {
    case Running;
    case Upcoming;
    case Finished;
    case Unknown;

    public function toInt(): int {
        return match ($this) {
            self::Running  =>  0,
            self::Upcoming =>  1,
            self::Finished =>  2,
            default        => -1,
        };
    }
};

class PoolController {
    private PageController  $_pageController;
    private NavController   $_navController;
    private Template        $_template;
    private ApiController   $_apiController;
    private ScoreController $_scoreController;
    private ErrorController $_errorController;
    private LoginController $_loginController;

    private array $pageConfig = [
        "pool" => [
            "pageName"        => "Tournaments",
            "linkNameActive"  => "Tournaments",
            "html"            => "",
            "backgroundHTML"  => ""
        ]
    ];
    private array $moduleConfig = [
        "pool_titles" => [
            "link" => array(),
            "name" => "Tournaments"
        ]
    ];

    private array $boxPools = array(
        "Running"  => array(),
        "Upcoming" => array(),
        "Finished" => array(),
        "Unknown"  => array()
    );
    private array $pool = array();
    private array $localTeam = array();
    private array $visitorTeam = array();

    public function __construct() {
        $this->_pageController  = PageController::getInstance();
        $this->_navController   = NavController::getInstance();
        $this->_template        = new Template();
        $this->_apiController   = new ApiController();
        $this->_scoreController = new ScoreController();
        $this->_errorController = new ErrorController();
        $this->_loginController = new LoginController();

        $this->pageConfig["pool"]["html"] = file_get_contents(join(DS, array(WWW, "html", "content.html")));
    }

    /**
     * @description Get the module title
     * @param array $titles
     * @return string
     */
    private function getModuleTitle(array $titles): string {
        return $this->_template->getModuleTitle($titles);
    }

    /**
     * @description Set the module title
     * @return void
     */
    private function setModuleTitle(): void {
        $this->pool["tournamentName"] = json_decode($this->_apiController->getTournament($this->pool["tournamentID"], "name"), true)[0]["name"];

        $this->moduleConfig["pool_titles"]["link"] = array("Tournaments", $this->pool["tournamentName"]);
        $this->moduleConfig["pool_titles"]["name"] = $this->pool["name"];
    }

    /**
     * @description Set the module background
     * @return void
     */
    private function setModuleBackground(): void {
        $this->pageConfig["pool"]["backgroundHTML"] = "<div class=\"absolute background-after_3-4 full-device\">
                                                           <img class=\"full-container img-fixed half-device blur\" src=\"{$this->pool["picture"]}\"  alt=\"tournament background\"/>
                                                       </div>";
    }

    /**
     * @description Fetch all pools from a tournament
     * @param int $tournamentID
     * @return string
     */
    public function fetchPools(int $tournamentID): string {
        $pools = json_decode($this->_apiController->getTournamentPools($tournamentID), true);

        foreach($pools as $pool) {
            $boxPool = $this->_template->makePoolBox($pool);

            switch ($pool["status"]) {
                case PoolStatus::Running->toInt() : $this->boxPools[PoolStatus::Running->name] [] = $boxPool; break;
                case PoolStatus::Upcoming->toInt(): $this->boxPools[PoolStatus::Upcoming->name][] = $boxPool; break;
                case PoolStatus::Finished->toInt(): $this->boxPools[PoolStatus::Finished->name][] = $boxPool; break;
                default:                            $this->boxPools[PoolStatus::Unknown->name] [] = $boxPool; break;
            }
        }

        return $this->makingContentPool();
    }

    /**
     * @description Fetch a pool from a tournament
     * @param int $tournamentID
     * @param int $poolID
     * @return void
     */
    public function fetchPool(int $tournamentID, int $poolID): void {
        $poolData = json_decode($this->_apiController->getTournamentPool($tournamentID, $poolID), true);
        if (!empty($poolData)) $this->pool = $poolData[0];
    }

    /**
     * @description Fetch teams from a pool
     * @param string $team
     * @param string $teamVS
     * @return void
     */
    private function fetchPoolTeam(string $team, string $teamVS): void {
        $this->localTeam   = json_decode($this->_apiController->getTeam($team), true);
        $this->visitorTeam = json_decode($this->_apiController->getTeam($teamVS), true);
    }

    /**
     * @description Making pools box for tournament page
     * @return string
     */
    private function makingContentPool(): string {
        $html                      = "";
        $tournamentSeparatorActive = false;
        $tournamentSeparator       = "<div class=\"box-separator\"></div>";

        foreach ($this->boxPools as $value) {
            if (!empty($value)) {
                if ($tournamentSeparatorActive) $html .= $tournamentSeparator;
                $html .= implode("", $value);

                $tournamentSeparatorActive = true;
            }
        }

        return $html;
    }

    /**
     * @description Making the content of the pool
     * @return string
     */
    private function makingContentPoolWithID(): string {
        $html  = $this->_template->createReturnButton("/tournament/" . $this->pool["tournamentID"]);
        $html .= $this->_template->makeInfoPoolBox($this->pool);
        $this->fetchPoolTeam($this->pool["team"], $this->pool["teamVS"]);

        $this->_scoreController->setTeams(
            array(
                "teamFR" => array("name" => $this->pool["team"]),
                "teamVS" => array("name" => $this->pool["teamVS"])
            ),
            $this->localTeam,
            $this->visitorTeam
        );
        $this->_scoreController->setMaps(        $this->pool["tournamentID"], $this->pool["poolID"]);
        $this->_scoreController->setScoresByMaps($this->pool["tournamentID"], $this->pool["poolID"]);
        $this->_scoreController->makeCalculus();

        $team  = "<div class=\"flex flex-column\">";
            $team .= "<div class=\"box-full box-rounded box-team flex flex-spaced\">";
                $team .= $this->_template->makeTeamFullBox($this->localTeam);
            $team .= "</div>";
            if ($this->pool["teamVS"] != "") {
                $team .= "<div class=\"box-full box-rounded box-team flex flex-spaced\">";
                    $team .= $this->_template->makeTeamFullBox($this->visitorTeam);
                $team .= "</div>";
            }
        $team .= "</div>";

        $pool = "<div class=\"pool-content\">";
            $pool .=  $this->_template->makeOverall(   $this->_scoreController->makePrimary());
            $pool .=  $this->_template->makeMapPrimary($this->_scoreController->makeMaps());
        $pool .= "</div>";

        $html .= "<div class=\"tournament-pool-content flex\">$team$pool</div>";

        return $html;
    }

    public function pool(int $tournamentID, int $poolID): string {
        $this->fetchPool($tournamentID, $poolID);
        if (empty($this->pool)) return $this->_errorController->notFound();
        if ($this->pool["requireLogin"]) {
            if (!$this->_loginController->isAuthenticated()) return $this->_errorController->needLogin();
            if ($this->pool["requireRoles"]) {
                if (!$this->_loginController->asAuthorization($this->pool["requireRoles"])) return $this->_errorController->needRoles();
            }
        }

        $this->setModuleTitle();
        $this->setModuleBackground();

        $this->_navController ->setActiveNav	 ($this->pageConfig["pool"]["linkNameActive"]);
        $this->_pageController->setPageName		 ($this->pool["name"]);
        $this->_pageController->setPageBackground($this->pageConfig["pool"]["backgroundHTML"]);
        $this->_pageController->setPageContent	 ($this->pageConfig["pool"]["html"]);

        $this->_pageController->setModuleTitle  ($this->getModuleTitle($this->moduleConfig["pool_titles"]));
        $this->_pageController->setModuleContent($this->makingContentPoolWithID());

        return $this->_pageController->getPage();
    }
}