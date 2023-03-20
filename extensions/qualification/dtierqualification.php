<?php
namespace extensions\qualification;
use extensions\login\Login;
use hyldxycore\system\Template;

class DTierQualification {
    private string $HTMLPage;
    private string $leaderboardHTML;
    private Login $_login;
    private Template $_template;

    public function __construct() {
        $this->HTMLPage = file_get_contents(join(DS, array(WWW, "html", "index.html")));
        $this->leaderboardHTML = file_get_contents(join(DS, array(WWW, "html", "leaderboard.html")));
        $this->_template = new Template();
        $this->_login = new Login();

        $this->_template->replaceNavContent($this->HTMLPage, "DTierQualification");
    }

    public function send(): string {
        if ($this->_login->isAuthenticated() && $this->_login->asAuthorization()) $this->HTMLPage = $this->_template->regexBalise($this->HTMLPage, "CONTENT", $this->leaderboardHTML);
        return $this->HTMLPage;
    }
}