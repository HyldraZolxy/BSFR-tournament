<?php
namespace hyldxycore\system\frontSystem;

class HomeController {
    private PageController $_pageController;
    private NavController  $_navController;

    private array $pageConfig = [
        "index" => [
            "pageName"       => "Home",
            "linkNameActive" => "Home",
            "html"           => "",
            "backgroundHTML" => ""
        ]
    ];

    public function __construct() {
        $this->_pageController = PageController::getInstance();
        $this->_navController  = NavController::getInstance();

        $this->pageConfig["index"]["html"]           = file_get_contents(join(DS, array(WWW, "html", "index.html")));
        $this->pageConfig["index"]["backgroundHTML"] = file_get_contents(join(DS, array(WWW, "html", "background.html")));
    }

    /**
     * @description Returns the home page
     * @return string
     */
    public function index(): string {
        $this->_navController->setActiveNav		 ($this->pageConfig["index"]["linkNameActive"]);
        $this->_pageController->setPageName		 ($this->pageConfig["index"]["pageName"]);
        $this->_pageController->setPageBackground($this->pageConfig["index"]["backgroundHTML"]);
        $this->_pageController->setPageContent	 ($this->pageConfig["index"]["html"]);

        return $this->_pageController->getPage();
    }
}