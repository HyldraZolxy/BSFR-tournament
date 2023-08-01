<?php
namespace extensions\tools;

use hyldxycore\system\frontSystem\NavController;
use hyldxycore\system\frontSystem\PageController;

class ToolsController {
    private PageController $_pageController;
    private NavController  $_navController;

    private array $pageConfig = [
        "index" => [
            "pageName"       => "Tools",
            "linkNameActive" => "Tools",
            "html"           => "",
            "backgroundHTML" => ""
        ]
    ];

    public function __construct() {
        $this->_pageController = PageController::getInstance();
        $this->_navController  = NavController::getInstance();

        $this->pageConfig["index"]["html"] 			 = file_get_contents(join(DS, array(WWW, "html", "tools.html")));
        $this->pageConfig["index"]["backgroundHTML"] = file_get_contents(join(DS, array(WWW, "html", "background.html")));
    }

    public function index(): string|false {
        $this->_navController->setActiveNav		 ($this->pageConfig["index"]["linkNameActive"]);
        $this->_pageController->setPageName		 ($this->pageConfig["index"]["pageName"]);
        $this->_pageController->setPageBackground($this->pageConfig["index"]["backgroundHTML"]);
        $this->_pageController->setPageContent	 ($this->pageConfig["index"]["html"]);

        return $this->_pageController->getPage();
    }
}