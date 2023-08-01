<?php
namespace hyldxycore\system\frontSystem;

use extensions\login\LoginController;

class NavController {
    static private ?NavController  $_instance = null;
           private Template        $_template;
           private LoginController $_login;

           private string $navMobileHTML  = "";
           private string $navPcHTML      = "";
           private string $navFooterHTML  = "";
           private array $tagsHTMLReplace = [
                "mobile" => "{{NAV_MOBILE}}",
                "pc"     => "{{NAV_PC}}",
                "footer" => "{{NAV_FOOTER}}"
           ];

           private array $navs = [
                "mobile" => [
                    "Close" => [
                        "url"     => "javascript:void(0)",
                        "class"   => "btn btn-close",
                        "content" => "<i class=\"fa-solid fa-xmark\"></i>",
                        "title"   => "close button for mobile navigation",
                        "onclick" => "mobileNavClose()",
                        "active"  => false
                    ],
                    "Home" => [
                        "url"     => "/",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Home",
                        "title"   => "Home button",
                        "onclick" => "mobileNavClose()",
                        "active"  => false
                    ],
                    "Tournaments" => [
                        "url"     => "/tournaments",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Tournaments",
                        "title"   => "Tournaments button",
                        "onclick" => "mobileNavClose()",
                        "active"  => false
                    ],
                    "Tools" => [
                        "url"     => "/tools",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Tools",
                        "title"   => "Tools button",
                        "onclick" => "mobileNavClose()",
                        "active"  => false
                    ],
                    "Discord" => []
                ],
                "pc"     => [
                    "Home" => [
                        "url"     => "/",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Home",
                        "title"   => "Home button",
                        "active"  => false
                    ],
                    "Tournaments" => [
                        "url"     => "/tournaments",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Tournaments",
                        "title"   => "Tournaments button",
                        "active"  => false
                    ],
                    "Tools" => [
                        "url"     => "/tools",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Tools",
                        "title"   => "Tools button",
                        "active"  => false
                    ],
                    "Login" => []
                ],
                "footer" => [
                    "Discord" => [
                        "url"     => "https://discord.gg/aNBemRhc2X",
                        "class"   => "btn btn-border-discord btn-discord-hover",
                        "content" => "<i class=\"fa-brands fa-discord\"></i>",
                        "title"   => "discord invitation button",
                        "target"  => "_blank",
                        "active"  => false
                    ],
                    "Home" => [
                        "url"     => "/",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Home",
                        "title"   => "Home button",
                        "active"  => false
                    ],
                    "Tournaments" => [
                        "url"     => "/tournaments",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Tournaments",
                        "title"   => "Tournaments button",
                        "active"  => false
                    ],
                    "Tools" => [
                        "url"     => "/tools",
                        "class"   => "btn btn-light-v_light-hover",
                        "content" => "Tools",
                        "title"   => "Tools button",
                        "active"  => false
                    ]
                ]
           ];

    public function __construct() {
        $this->_template = new Template();
        $this->_login    = new LoginController();

        $this->navMobileHTML = file_get_contents(join(DS, array(WWW, "html", "navigation_mobile.html")));
        $this->navPcHTML     = file_get_contents(join(DS, array(WWW, "html", "navigation_pc.html")));
        $this->navFooterHTML = file_get_contents(join(DS, array(WWW, "html", "navigation_footer.html")));

        $this->navs["mobile"]["Discord"] += $this->_login->setNavLogin("mobile" );
        $this->navs["pc"]["Login"]       += $this->_login->setNavLogin("desktop");
    }

    /**
     * @description Make the link active
     * @param string $name
     * @return void
     */
    public function setActiveNav(string $name): void {
        foreach ($this->navs as &$navs) {
            foreach ($navs as $key => &$nav) {
                if (isset($nav["active"])) $nav["active"] = ($key === $name);
            }
        }
    }

    /**
     * @description Create the navigation
     * @param string $htmlPage
     * @return string
     */
    public function createNavs(string $htmlPage): string {
        $this->navMobileHTML = str_replace($this->tagsHTMLReplace["mobile"], $this->_template->createNavLink($this->navs["mobile"]), $this->navMobileHTML);
        $this->navPcHTML     = str_replace($this->tagsHTMLReplace["pc"],     $this->_template->createNavLink($this->navs["pc"]),     $this->navPcHTML);
        $this->navFooterHTML = str_replace($this->tagsHTMLReplace["footer"], $this->_template->createNavLink($this->navs["footer"]), $this->navFooterHTML);

        $htmlPage = str_replace($this->tagsHTMLReplace["mobile"], $this->navMobileHTML, $htmlPage);
        $htmlPage = str_replace($this->tagsHTMLReplace["pc"],     $this->navPcHTML,     $htmlPage);
        return      str_replace($this->tagsHTMLReplace["footer"], $this->navFooterHTML, $htmlPage);
    }

    static public function getInstance(): NavController {
        if (self::$_instance === null) self::$_instance = new NavController();

        return self::$_instance;
    }
}