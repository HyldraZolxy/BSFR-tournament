<?php
namespace hyldxycore\system\frontSystem;

class PageController {
    static private ?PageController $_instance = null;
           private NavController   $_navController;

           private string $HTMLPage       = "";
           private array $tagsHTMLReplace = [
                "title"          => "{{PAGE_TITLE}}",
                "background"     => "{{PAGE_BACKGROUND}}",
                "content"        => "{{PAGE_CONTENT}}",
                "module_content" => "{{MODULE_CONTENT}}",
                "module_title"   => "{{MODULE_CONTENT_TITLE}}"
           ];

    public function __construct() {
        $this->_navController = NavController::getInstance();

        $this->HTMLPage = file_get_contents(join(DS, array(WWW, "html", "base.html")));
    }

    /*******
     * SET Page
     *******/
    /**
     * @description Set the page name
     * @param string $name
     * @return void
     */
    public function setPageName(string $name): void {
        $this->HTMLPage = str_replace($this->tagsHTMLReplace["title"], $name, $this->HTMLPage);
    }

    /**
     * @description Set the page background
     * @param string $background
     * @return void
     */
    public function setPageBackground(string $background): void {
        $this->HTMLPage = str_replace($this->tagsHTMLReplace["background"], $background, $this->HTMLPage);
    }

    /**
     * @description Set the page content
     * @param string $content
     * @return void
     */
    public function setPageContent(string $content): void {
        $this->HTMLPage = str_replace($this->tagsHTMLReplace["content"], $content, $this->HTMLPage);
    }

    /*********
     * SET Module
     *********/
    /**
     * @description Set the module title
     * @param string $title
     * @return void
     */
    public function setModuleTitle(string $title): void {
        $this->HTMLPage = str_replace($this->tagsHTMLReplace["module_title"], $title, $this->HTMLPage);
    }

    /**
     * @description Set the module content
     * @param string $content
     * @return void
     */
    public function setModuleContent(string $content): void {
        $this->HTMLPage = str_replace($this->tagsHTMLReplace["module_content"], $content, $this->HTMLPage);
    }

    /*********
     * GET Page
     *********/
    /**
     * @description Create the navigation and return the page
     * @return string
     */
    public function getPage(): string {
        $this->HTMLPage = $this->_navController->createNavs($this->HTMLPage);
        return $this->HTMLPage;
    }

    static public function getInstance(): PageController {
        if (self::$_instance === null) self::$_instance = new PageController();

        return self::$_instance;
    }
}