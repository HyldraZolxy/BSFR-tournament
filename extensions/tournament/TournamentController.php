<?php
namespace extensions\tournament;

use extensions\api\ApiController;
use extensions\login\LoginController;
use extensions\pool\PoolController;
use hyldxycore\system\frontSystem\ErrorController;
use hyldxycore\system\frontSystem\NavController;
use hyldxycore\system\frontSystem\PageController;
use hyldxycore\system\frontSystem\Template;

enum TournamentStatus {
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

class TournamentController {
    private PageController  $_pageController;
    private NavController   $_navController;
    private Template        $_template;
    private ApiController   $_apiController;
    private PoolController  $_poolController;
    private ErrorController $_errorController;
    private LoginController $_loginController;

    /** Page configuration */
    private array $pageConfig = [
        "tournaments" => [
            "pageName"        => "Tournaments",
            "linkNameActive"  => "Tournaments",
            "html"            => "",
            "backgroundHTML"  => ""
        ],
        "tournament" => [
            "pageName"        => "Tournament",
            "linkNameActive"  => "Tournaments",
            "html"            => "",
            "backgroundHTML"  => ""
        ]
    ];
    private array $moduleConfig = [
        "tournaments_titles" => [
            "link" => array(),
            "name" => "Tournaments"
        ],
        "tournament_titles" => [
            "link" => array(),
            "name" => "Tournaments"
        ]
    ];

    /** Tournaments */
    private array $boxTournaments = array(
        "Running"  => array(),
        "Upcoming" => array(),
        "Finished" => array(),
        "Unknown"  => array()
    );
    /** Tournament with ID */
    private array $tournament = array();
    private array $localTeam = array();

    public function __construct() {
        $this->_pageController  = PageController::getInstance();
        $this->_navController   = NavController::getInstance();
        $this->_template        = new Template();
        $this->_apiController   = new ApiController();
        $this->_poolController  = new PoolController();
        $this->_errorController = new ErrorController();
        $this->_loginController = new LoginController();

        $this->pageConfig["tournaments"]["html"] 		   = file_get_contents(join(DS, array(WWW, "html", "content.html")));
        $this->pageConfig["tournaments"]["backgroundHTML"] = file_get_contents(join(DS, array(WWW, "html", "background.html")));
        $this->pageConfig["tournament"]["html"]  		   = file_get_contents(join(DS, array(WWW, "html", "content.html")));
    }

    /**
     * @description Get module title
     * @param array $titles
     * @return string
     */
    private function getModuleTitle(array $titles): string {
        return $this->_template->getModuleTitle($titles);
    }

    /**
     * @description Set module title
     * @return void
     */
    private function setModuleTitle(): void {
        $this->moduleConfig["tournament_titles"]["link"] = array("Tournaments");
        $this->moduleConfig["tournament_titles"]["name"] = $this->tournament["name"];
    }

    /**
     * @description Set module background
     * @return void
     */
    private function setModuleBackground(): void {
        $this->pageConfig["tournament"]["backgroundHTML"] = "<div class=\"absolute background-after_3-4 full-device\">
                                                                 <img class=\"full-container img-fixed half-device blur\" src=\"{$this->tournament["picture"]}\"  alt=\"tournament background\"/>
                                                             </div>";
    }

    /**
     * @description Fetch tournaments
     * @param array $tournaments
     * @return void
     */
    private function fetchTournaments(array $tournaments): void {
        foreach($tournaments as $tournament) {
            if ($tournament["requireLogin"] && !$this->_loginController->isAuthenticated())                            continue;
            if ($tournament["requireRoles"] && !$this->_loginController->asAuthorization($tournament["requireRoles"])) continue;

            $boxTournament = $this->_template->makeTournamentBox($tournament);

            switch ($tournament["status"]) {
                case TournamentStatus::Running->toInt() : $this->boxTournaments[TournamentStatus::Running->name] [] = $boxTournament; break;
                case TournamentStatus::Upcoming->toInt(): $this->boxTournaments[TournamentStatus::Upcoming->name][] = $boxTournament; break;
                case TournamentStatus::Finished->toInt(): $this->boxTournaments[TournamentStatus::Finished->name][] = $boxTournament; break;
                default:                                  $this->boxTournaments[TournamentStatus::Unknown->name] [] = $boxTournament; break;
            }
        }
    }

    /**
     * @description Making tournament box for tournaments page
     * @return string
     */
    private function makingContentTournament(): string {
        $html                      = "";
        $tournamentSeparatorActive = false;
        $tournamentSeparator       = "<div class=\"box-separator\"></div>";

        foreach ($this->boxTournaments as $value) {
            if (!empty($value)) {
                if ($tournamentSeparatorActive) $html .= $tournamentSeparator;
                $html .= implode("", $value);
            }

            $tournamentSeparatorActive = true;
        }

        return $html;
    }

    /**
     * @description Fetch tournament with ID
     * @param int $tournamentID
     * @return void
     */
    private function fetchTournament(int $tournamentID): void {
        $tournamentData = json_decode($this->_apiController->getTournament($tournamentID), true);
        if (!empty($tournamentData)) $this->tournament = $tournamentData[0];
    }

    /**
     * @description Fetch tournament team
     * @param string $team
     * @return void
     */
    private function fetchTournamentTeam(string $team): void {
        $this->localTeam = json_decode($this->_apiController->getTeam($team), true);
    }

    /**
     * @description Making content of the tournament
     * @return string
     */
    private function makingContentTournamentWithID(): string {
        $html  = $this->_template->createReturnButton("/tournaments/");
        $html .= $this->_template->makeInfoTournamentBox($this->tournament);
        $this->fetchTournamentTeam($this->tournament["team"]);

        $team = "<div class=\"box-full box-rounded box-team flex flex-spaced\">";
            $team .= $this->_template->makeTeamFullBox($this->localTeam);
        $team .= "</div>";

        $pool = "<div class=\"pool-content\">";
            $pool .= $this->_poolController->fetchPools($this->tournament["id"]);
        $pool .= "</div>";

        $html .= "<div class=\"tournament-pool-content flex\">$team$pool</div>";

        return $html;
    }

    public function tournaments(): string {
        $this->_navController ->setActiveNav	 ($this->pageConfig["tournaments"]["linkNameActive"]);
        $this->_pageController->setPageName		 ($this->pageConfig["tournaments"]["pageName"]);
        $this->_pageController->setPageBackground($this->pageConfig["tournaments"]["backgroundHTML"]);
        $this->_pageController->setPageContent	 ($this->pageConfig["tournaments"]["html"]);

        $this->fetchTournaments(json_decode($this->_apiController->getTournaments(), true));

        $this->_pageController->setModuleTitle	($this->getModuleTitle($this->moduleConfig["tournaments_titles"]));
        $this->_pageController->setModuleContent($this->makingContentTournament());

        return $this->_pageController->getPage();
    }
    public function tournament(int $tournamentID): string {
        $this->fetchTournament($tournamentID);
        if (empty($this->tournament)) return $this->_errorController->notFound();
        if ($this->tournament["requireLogin"]) {
            if (!$this->_loginController->isAuthenticated()) return $this->_errorController->needLogin();
            if ($this->tournament["requireRoles"]) {
                if (!$this->_loginController->asAuthorization($this->tournament["requireRoles"])) return $this->_errorController->needRoles();
            }
        }

        $this->setModuleTitle();
        $this->setModuleBackground();

        $this->_navController ->setActiveNav	 ($this->pageConfig["tournament"]["linkNameActive"]);
        $this->_pageController->setPageName		 ($this->tournament["name"]);
        $this->_pageController->setPageBackground($this->pageConfig["tournament"]["backgroundHTML"]);
        $this->_pageController->setPageContent	 ($this->pageConfig["tournament"]["html"]);

        $this->_pageController->setModuleTitle	($this->getModuleTitle($this->moduleConfig["tournament_titles"]));
        $this->_pageController->setModuleContent($this->makingContentTournamentWithID());

        return $this->_pageController->getPage();
    }
}