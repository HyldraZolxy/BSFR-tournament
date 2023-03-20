<?php
namespace hyldxycore\system;
use extensions\login\Login;
use extensions\qualification\DTierQualification;
use extensions\api\Api;
use extensions\grabber\Grabber;

class Application {
    protected string $_url;

    static private ?Application $_instance = null;

    private Template $_template;
    private Login $_login;
    private DTierQualification $_DTierQualification;
    private Api $_api;
    private Grabber $_grabber;

    public function __construct() {
        $this->_template = new Template();
        $this->_login = new Login();
        $this->_DTierQualification = new DTierQualification();
        $this->_api = Api::getInstance();
        $this->_grabber = new Grabber();
    }

    private function getURL(): false|string {
        if (isset($_SERVER["REQUEST_URI"])) $this->_url = $_SERVER["REQUEST_URI"];

        $URIExploded = explode("/", parse_url($this->_url, PHP_URL_PATH));

        $page = match ($URIExploded[1]) {
            "login" => $this->_login->login(),
            "logout" => $this->_login->endSession(),
            "DTierQualification" => $this->_DTierQualification->send(),
            "api" => $this->_api->runAPI((isset($URIExploded[2])) ? $URIExploded[2] : ""),
            "grabber" => $this->_grabber->send(),
            default => file_get_contents(join(DS, array(WWW, "html", "index.html")))
        };

        $page = $this->_template->replaceLoginButton($page);
        $page = $this->_template->replaceLoginLink($page);
        $page = $this->_template->replaceNavContentLink($page, parse_url($this->_url, PHP_URL_PATH));
        $page = $this->_template->replaceNavContent($page, substr(parse_url($this->_url, PHP_URL_PATH), 1));

        return $this->_template->cleanBalises($page);
    }

    static public function getInstance(): ?Application {
        if (is_null(self::$_instance)) self::$_instance = new self();

        return self::$_instance;
    }

    public function run(): bool|string {
        return $this->getURL();
    }
}