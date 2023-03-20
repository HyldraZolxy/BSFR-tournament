<?php
namespace extensions\grabber;

use extensions\login\Login;
use hyldxycore\system\Template;

class Grabber {
    private Login $_login;
    private Template $_template;
    private string $HTMLPage;
    private string $grabberScript;

    public function __construct() {
        $this->_template = new Template();
        $this->_login = new Login();
        $this->HTMLPage = file_get_contents(join(DS, array(WWW, "html", "grabber.html")));
        $this->grabberScript = file_get_contents(join(DS, array(WWW, "html", "grabberScript.html")));
    }

    public function send(): string {
        if ($this->_login->isAuthenticated() && $this->_login->asAuthorization()) $this->HTMLPage = $this->_template->regexBalise($this->HTMLPage, "SCRIPT_JS_GRABBER", $this->grabberScript);
        return $this->HTMLPage;
    }
}