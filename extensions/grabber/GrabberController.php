<?php
namespace extensions\grabber;

use extensions\login\LoginController;
use hyldxycore\system\frontSystem\ErrorController;
use hyldxycore\system\frontSystem\NavController;
use hyldxycore\system\frontSystem\PageController;

class GrabberController {
    private PageController  $_pageController;
    private NavController   $_navController;
    private ErrorController  $_errorController;
    private LoginController $_loginController;

    private int $rolesRequired = 128;

    private array $pageConfig = [
        "index" => [
            "pageName"       => "Score Grabber",
            "linkNameActive" => "Tools",
            "html"           => "",
            "backgroundHTML" => "<div class=\"absolute background-after_3-4 full-device\">
                                    <img class=\"full-container img-fixed half-device blur\" src=\"/pictures/tools/scoregrabber.png\"  alt=\"tournament background\"/>
                                 </div>"
        ]
    ];

    public function __construct() {
        $this->_pageController  = PageController::getInstance();
        $this->_navController   = NavController::getInstance();
        $this->_errorController = new ErrorController();
        $this->_loginController = new LoginController();

        $this->pageConfig["index"]["html"] = file_get_contents(join(DS, array(WWW, "html", "scoreGrabber.html")));
    }

    public function index(): string|false {
        if (!$this->_loginController->isAuthenticated()) return $this->_errorController->needLogin();
        if (!$this->_loginController->asAuthorization($this->rolesRequired)) return $this->_errorController->needRoles();

        $this->_navController->setActiveNav		 ($this->pageConfig["index"]["linkNameActive"]);
        $this->_pageController->setPageName		 ($this->pageConfig["index"]["pageName"]);
        $this->_pageController->setPageBackground($this->pageConfig["index"]["backgroundHTML"]);
        $this->_pageController->setPageContent	 ($this->pageConfig["index"]["html"]);

        return $this->_pageController->getPage();
    }
}