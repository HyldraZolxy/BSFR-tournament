<?php
namespace hyldxycore\system\frontSystem;

class ErrorController {
    private PageController $_pageController;
    private NavController  $_navController;

    private array $pageConfig = [
        "notFound" => [
            "pageName"       => "404",
            "linkNameActive" => "404",
            "html"           => "",
            "backgroundHTML" => ""
        ],
        "needLogin" => [
            "pageName"       => "Login required",
            "linkNameActive" => "needLogin",
            "html"           => "",
            "backgroundHTML" => ""
        ],
        "needRoles" => [
            "pageName"       => "Roles required",
            "linkNameActive" => "needRoles",
            "html"           => "",
            "backgroundHTML" => ""
        ]
    ];

    public function __construct() {
        $this->_pageController = PageController::getInstance();
        $this->_navController  = NavController::getInstance();

        $this->pageConfig["notFound"] ["html"] = file_get_contents(join(DS, array(WWW, "html", "404.html")));
        $this->pageConfig["needLogin"]["html"] = file_get_contents(join(DS, array(WWW, "html", "needLogin.html")));
        $this->pageConfig["needRoles"]["html"] = file_get_contents(join(DS, array(WWW, "html", "needRoles.html")));

        $defaultBackground = file_get_contents(join(DS, array(WWW, "html", "background.html")));
        $this->pageConfig["notFound"] ["backgroundHTML"] = $defaultBackground;
        $this->pageConfig["needLogin"]["backgroundHTML"] = $defaultBackground;
        $this->pageConfig["needRoles"]["backgroundHTML"] = $defaultBackground;
    }

    /**
     * @description Return the 404 page
     * @return string
     */
    public function notFound(): string {
        $this->_navController ->setActiveNav     ($this->pageConfig["notFound"]["linkNameActive"]);
        $this->_pageController->setPageName      ($this->pageConfig["notFound"]["pageName"]);
        $this->_pageController->setPageBackground($this->pageConfig["notFound"]["backgroundHTML"]);
        $this->_pageController->setPageContent   ($this->pageConfig["notFound"]["html"]);

        return $this->_pageController->getPage();
    }

    /**
     * @description Return the needLogin page
     * @return string
     */
    public function needLogin(): string {
        $this->_navController ->setActiveNav     ($this->pageConfig["needLogin"]["linkNameActive"]);
        $this->_pageController->setPageName      ($this->pageConfig["needLogin"]["pageName"]);
        $this->_pageController->setPageBackground($this->pageConfig["needLogin"]["backgroundHTML"]);
        $this->_pageController->setPageContent   ($this->pageConfig["needLogin"]["html"]);

        return $this->_pageController->getPage();
    }

    /**
     * @description Return the needRoles page
     * @return string
     */
    public function needRoles(): string {
        $this->_navController ->setActiveNav     ($this->pageConfig["needRoles"]["linkNameActive"]);
        $this->_pageController->setPageName      ($this->pageConfig["needRoles"]["pageName"]);
        $this->_pageController->setPageBackground($this->pageConfig["needRoles"]["backgroundHTML"]);
        $this->_pageController->setPageContent   ($this->pageConfig["needRoles"]["html"]);

        return $this->_pageController->getPage();
    }
}