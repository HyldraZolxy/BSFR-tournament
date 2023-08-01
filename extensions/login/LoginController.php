<?php
namespace extensions\login;

use hyldxycore\system\backSystem\DBFactory;
use hyldxycore\system\backSystem\SQLQuery;
use hyldxycore\system\backSystem\Tools;

class LoginController {
    private Tools     $_tools;
    private DBFactory $_dbFactory;
    private SQLQuery  $_sqlQuery;

    private array $_configuration;

    public function __construct() {
        $this->_tools     = new Tools();
        $this->_dbFactory = new DBFactory();
        $this->_sqlQuery  = new SQLQuery($this->_dbFactory->getPDO());

        $this->_configuration = json_decode(file_get_contents(join(DS, array(HYLDXYCONFIG, "discordconfig.json"))), true);
    }

    /**
     * @description Start session
     * @return void
     */
    public function startSession(): void {
        session_start();
    }

    /**
     * @description End session + destroy cookies
     * @return void
     */
    public function endSession(): void {
        session_destroy();

        setcookie("websiteID",    "", -1, "/", "");
        setcookie("websiteToken", "", -1, "/", "");

        $this->_tools->redirect("/");
    }

    /**
     * @description Make session + cookies
     * @param array $userInfo
     * @return void
     */
    public function makeSession(array $userInfo): void {
        setcookie("websiteID", $userInfo["scoresaberID"], time() + 60*60*24*2, "/", "", false, true); // hyldrazolxy.fr + true if https
        setcookie("websiteToken", $userInfo["websiteCode"], time() + 60*60*24*2, "/", "", false, true); // hyldrazolxy.fr + true if https

        $_SESSION["scoresaberID"] = $userInfo["scoresaberID"];
        $_SESSION["access_token"] = $userInfo["websiteCode"];
        $_SESSION["roles"]        = $userInfo["roles"];
    }

    /**
     * @description Return nav login button
     * @param string $device
     * @return array
     */
    public function setNavLogin(string $device): array {
        if ($this->isAuthenticated()) {
            if ($device === "desktop") return array(
                    "url"     => "/logout",
                    "class"   => "btn btn-border-default-v_light btn-default-v_light-hover",
                    "content" => "<img src=\"{$this->_tools->parseScoresaberPicture($_SESSION["scoresaberID"])}\" alt=\"Avatar picture\" /> Logout",
                    "title"   => "Logout button",
                    "active"  => false
            );
            else return array(
                    "url"     => "/logout",
                    "class"   => "btn btn-default-v_light",
                    "content" => "<img src=\"{$this->_tools->parseScoresaberPicture($_SESSION["scoresaberID"])}\" alt=\"Avatar picture\" /> Logout",
                    "title"   => "Logout button",
                    "onclick" => "mobileNavClose()",
                    "active"  => false
            );
        } else {
            if ($device === "desktop") return array(
                    "url"     => "{$this->URILogin()}",
                    "class"   => "btn btn-border-discord btn-discord-hover",
                    "content" => "<i class=\"fa-brands fa-discord\"></i> Login with Discord",
                    "title"   => "Login button",
                    "active"  => false
            );
            else return array(
                    "url"     => "{$this->URILogin()}",
                    "class"   => "btn btn-discord",
                    "content" => "<i class=\"fa-brands fa-discord\"></i> Login",
                    "title"   => "Login button",
                    "onclick" => "mobileNavClose()",
                    "active"  => false
            );
        }
    }

    /**
     * @description Return if user is authenticated + refresh session if needed
     * @return bool
     */
    public function isAuthenticated(): bool {
        if (   empty($_COOKIE["websiteID"]   )
            || empty($_COOKIE["websiteToken"])
        ) return false;

        if (   !empty($_SESSION["scoresaberID"])
            && !empty($_SESSION["access_token"])
            && !empty($_SESSION["roles"]       )
        ) return true;

        $userID = $_COOKIE["websiteID"];
        $token  = $_COOKIE["websiteToken"];

        $fields = array("u.*", "ul.roles", "ul.code");
        $joins  = array(
            "userslogin" => array(
                "table" => "userslogin ul",
                "on"    => "ul.userID = u.scoresaberID"
            )
        );
        $where  = array(
            "ul.websiteCode" => array(
                "value"    => $token,
                "operator" => "=",
                "between"  => "AND"
            ),
            "ul.userID" => array(
                "value"    => $userID,
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("users u", $fields, $where, array(), array(), $joins);
        $result = $this->_sqlQuery->sqlFetchAll();

        if (empty($result)) 			    return false;
        if (empty($result[0]["discordID"])) return false;

        $_SESSION["scoresaberID"] = $userID;
        $_SESSION["access_token"] = $token;
        $_SESSION["roles"]        = $this->convertRoles($this->getUserRoles($result[0]["code"]));

        return true;
    }

    /**
     * @description Create and return a state
     * @return string
     */
    public function stateGenerator(): string {
        if (empty($_SESSION["state"])) $_SESSION["state"] = bin2hex(openssl_random_pseudo_bytes(12));
        return $_SESSION["state"];
    }

    /**
     * @description Return login URI
     * @return string
     */
    public function URILogin(): string {
        $state = $this->stateGenerator();
        return "https://discordapp.com/oauth2/authorize?response_type=code&client_id=" . $this->_configuration["clientID"] . "&redirect_uri=" . $this->_configuration["redirectURI"] . "&scope=" . $this->_configuration["scopes"] . "&state=" . $state . "&prompt=none";
    }

    /**
     * @description Login user
     * @return void
     */
    public function login(): void {
        if (!empty($_GET["code"]) && !empty($_GET["state"])) {
            $code  = $_GET["code"];
            $state = $_GET["state"];

            if ($_SESSION["state"] === $state) {
                $uri = $this->_configuration["baseURI"] . "/api/oauth2/token";

                $data = array(
                    "client_id"     => $this->_configuration["clientID"],
                    "client_secret" => $this->_configuration["secretID"],
                    "grant_type"    => "authorization_code",
                    "code"          => $code,
                    "redirect_uri"  => $this->_configuration["redirectURI"]
                );

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL,                  $uri);
                curl_setopt($curl, CURLOPT_POST,           true);
                curl_setopt($curl, CURLOPT_POSTFIELDS,           http_build_query($data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                curl_close($curl);

                $result = json_decode($response, true);

                if (empty($result["access_token"])) $this->_tools->redirect("/");
                $userInfo = $this->getUserInfo($result["access_token"]);

                if (!$userInfo) $this->_tools->redirect("/");

                $this->makeSession($userInfo);

                $this->_tools->redirect("/");
            }

            $this->_tools->redirect("/");
        }

        $this->_tools->redirect("/");
    }

    /**
     * @description Get user info from Discord
     * @param string $accessToken
     * @return array|bool
     */
    public function getUserInfo(string $accessToken): array|bool {
        $url 	 = $this->_configuration["baseURI"] . "/api/users/@me";
        $headers = array("Content-Type: application/x-www-form-urlencoded", "Authorization: Bearer " . $accessToken);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,                  $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,           $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        $curlResults = json_decode($response, true);

        if (empty($curlResults["id"])) return false;

        $roles = $this->convertRoles($this->getUserRoles($accessToken));

        $fields = array("*");
        $where  = array(
            "discordID" => array(
                "value"    => $curlResults["id"],
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("users", $fields, $where);
        $resultUsers = $this->_sqlQuery->sqlFetchAll();

        if (empty($resultUsers)) return false;

        $where  = array(
            "userID" => array(
                "value"    => $resultUsers[0]["scoresaberID"],
                "operator" => "="
            )
        );

        $this->_sqlQuery->sqlSelect("userslogin", $fields, $where);
        $resultUsersLogin = $this->_sqlQuery->sqlFetchAll();

        $websiteCode = bin2hex(openssl_random_pseudo_bytes(12));
        $parameters  = array(
            "userID"      => $resultUsers[0]["scoresaberID"],
            "code"        => $accessToken,
            "roles"       => $roles,
            "websiteCode" => $websiteCode
        );

        if (empty($resultUsersLogin)) $this->_sqlQuery->sqlAdd("userslogin",    $parameters);
        else                          $this->_sqlQuery->sqlUpdate("userslogin", $parameters, $where);

        return array(
            "scoresaberID" => $resultUsers[0]["scoresaberID"],
            "roles"        => $roles,
            "websiteCode"  => $websiteCode
        );
    }

    /**
     * @description Get user roles from Discord
     * @param string $accessToken
     * @return array
     */
    public function getUserRoles(string $accessToken): array {
        $url 	 = $this->_configuration["baseURI"] . "/api/users/@me/guilds/938516367555035136/member";
        $headers = array("Content-Type: application/x-www-form-urlencoded", "Authorization: Bearer " . $accessToken);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,                  $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,           $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        $results = json_decode($response, true);
        return $results["roles"];
    }

    /**
     * @description Convert roles from binary to array or array to binary
     * @param array|int $roles
     * @return array|int
     */
    public function convertRoles(array|int $roles): array|int {
        $rolesBase = $this->_configuration["roles"];

        $rolesInDecimal = array();
        $firstIndex     = true;
        $index          = 1;

        foreach ($rolesBase as $value) {
            if ($firstIndex) {
                $rolesInDecimal[$value] = $index;
                $firstIndex 			= false;
            } else {
                $rolesInDecimal[$value] = pow(2, $index);
                $index++;
            }
        }

        if (is_int($roles)) {
            $rolesConverted = array();

            foreach ($rolesInDecimal as $role => $value) {
                if ($roles & $value) $rolesConverted[] = $role;
            }
        } else {
            $rolesConverted = 0;

            foreach ($roles as $role) {
                if (isset($rolesInDecimal[$role])) {
                    $rolesConverted |= $rolesInDecimal[$role];
                }
            }
        }

        return $rolesConverted;
    }

    /**
     * @description Check if user is authorized
     * @param string|null $roleStrict
     * @return bool
     */
    public function asAuthorization(string $roleStrict = null): bool {
        if (!isset($_SESSION["roles"])) return false;

        $rolesUser  = $this->convertRoles($_SESSION["roles"]);
        $roleStrict = ($roleStrict !== null) ? $this->convertRoles($roleStrict) : "";

        $rolesAuthorized = $this->_configuration["roles"];

        foreach ($rolesAuthorized as $value) {
            if ($roleStrict !== null) {
                foreach ($roleStrict as $requiredRole) {
                    if ((int)$requiredRole === (int)$value) {
                        foreach ($rolesUser as $roleUser) {
                            if ((int)$roleUser === (int)$value) {
                                return true;
                            }
                        }
                    }
                }
            } else {
                foreach ($rolesUser as $roleUser) {
                    if ((int)$roleUser === (int)$value) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}