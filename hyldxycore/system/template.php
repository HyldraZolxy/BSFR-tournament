<?php
namespace hyldxycore\system;
use extensions\login\Login;

class Template {
    private Login $_login;
    private Tools $_tools;
    private array $balise = array("LOGIN_OR_LOGGED",
        "DISCORD_URI_LINK",
        "CONTENT",
        "MODULE_URI",
        "LOGIN_REQUIRED",
        "SCRIPT_JS_GRABBER"
    );

    public function __construct() {
        $this->_login = new Login();
        $this->_tools = new Tools();
    }

    public function regexBalise(string $HTMLPage, string $regexElement, string|int|float $str): string {
        $pattern = "{{" . $regexElement . "}}";
        return str_replace($pattern, $str, $HTMLPage);
    }

    public function replaceLoginLink(string $HTMLPage): string {
        return $this->regexBalise($HTMLPage, "DISCORD_URI_LINK", $this->_login->URILogin());
    }
    public function replaceLoginButton(string $HTMLPage): string {
        if ($this->_login->isAuthenticated()) {
            return $this->regexBalise($HTMLPage, "LOGIN_OR_LOGGED",
                '<div class="player">
                        <img class="playerAvatar" src="' . $this->_tools->avatarURI() . '" alt="Player Avatar"/>
                        <p class="playerName">' . $_SESSION["username"] . '</p>
                    </div>
                    <a href="/logout">Logout</a>'
            );
        } else {
            return $this->regexBalise($HTMLPage, "LOGIN_OR_LOGGED",
                '<a class="discord" href="{{DISCORD_URI_LINK}}">
                        Login with Discord
                    </a>'
            );
        }
    }
    public function replaceNavContent(string $HTMLPage, string $page = null): string {
        if (!empty($page)) {
            if ($this->_login->isAuthenticated() && $this->_login->asAuthorization())       $HTMLPage = $this->regexBalise($HTMLPage, "LOGIN_REQUIRED", "");
            else if (!$this->_login->asAuthorization() && isset($_SESSION["user_roles"]))   $HTMLPage = $this->regexBalise($HTMLPage, "LOGIN_REQUIRED", "You must be in the French Team!");
            else                                                                            $HTMLPage = $this->regexBalise($HTMLPage, "LOGIN_REQUIRED", "You must be logged in!");
        } else $HTMLPage = $this->regexBalise($HTMLPage, "LOGIN_REQUIRED", "");

        return $HTMLPage;
    }
    public function replaceNavContentLink(string $HTMLPage, string $page = null): string {
        if ($page !== null) return $this->regexBalise($HTMLPage, "MODULE_URI", '<a href="' . $page . '">' . substr($page, 1) . '</a>');
        else                return $this->regexBalise($HTMLPage, "MODULE_URI", "");
    }

    public function cleanBalises(string $HTMLPage): string {
        for ($i = 0; $i < count($this->balise); $i++) {
            $HTMLPage = $this->regexBalise($HTMLPage, $this->balise[$i], "");
        }

        return $HTMLPage;
    }
}