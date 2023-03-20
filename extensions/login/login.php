<?php
namespace extensions\login;
use hyldxycore\system\DBFactory;
use hyldxycore\system\SQLQuery;
use hyldxycore\system\Tools;

class Login {
    private DBFactory $_dbFactory;
    private SQLQuery $_sqlQuery;
    private Tools $_tools;

    public function __construct() {
        $this->_dbFactory = new DBFactory();
        $this->_sqlQuery = new SQLQuery($this->_dbFactory->getPDO());
        $this->_tools = new Tools();
    }

    public function startSession(): void {
        session_start();
    }
    public function endSession(): bool {
        session_destroy();

        $this->_tools->redirect("/");
        return true;
    }

    public function login(): bool {
        if (isset($_GET["code"]) && !empty($_GET["code"])) {
            $code = $_GET["code"];
            $state = $_GET["state"];

            if ($_SESSION["state"] === $state) {
                $url = $GLOBALS["baseURI"] . "/api/oauth2/token";

                $data = array(
                    "client_id" => $GLOBALS["clientID"],
                    "client_secret" => $GLOBALS["secretID"],
                    "grant_type" => "authorization_code",
                    "code" => $code,
                    "redirect_uri" => $GLOBALS["redirectURI"]
                );

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                curl_close($curl);

                $result = json_decode($response, true);

                $_SESSION["access_token"] = $result["access_token"];

                $this->getUserInfo();
                $this->getUserRoles();
                $this->getUserScoresaberID($_SESSION["user_id"]);

                $this->_tools->redirect("/");
                return true;
            }

            $this->_tools->redirect("/");
            return false;
        }

        $this->_tools->redirect("/");
        return false;
    }
    public function URILogin(): string {
        $state = $this->_tools->stateGenerator();
        return "https://discordapp.com/oauth2/authorize?response_type=code&client_id=" . $GLOBALS["clientID"] . "&redirect_uri=" . $GLOBALS["redirectURI"] . "&scope=" . $GLOBALS["scopes"] . "&state=" . $state;
    }
    public function getUserInfo(): void {
        $url = $GLOBALS["baseURI"] . "/api/users/@me";
        $headers = array("Content-Type: application/x-www-form-urlencoded", "Authorization: Bearer " . $_SESSION["access_token"]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        $results = json_decode($response, true);

        $_SESSION["user"] = $results;
        $_SESSION["username"] = $results["username"];
        $_SESSION["discriminator"] = $results["discriminator"];
        $_SESSION["user_id"] = $results["id"];
        $_SESSION["user_avatar"] = $results["avatar"];
    }
    public function getUserRoles(): void {
        $url = $GLOBALS["baseURI"] . "/api/users/@me/guilds/938516367555035136/member";
        $headers = array("Content-Type: application/x-www-form-urlencoded", "Authorization: Bearer " . $_SESSION["access_token"]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        $results = json_decode($response, true);
        $_SESSION["user_roles"] = $results["roles"];
    }
    public function getUserScoresaberID($discordID): void {
        $fields = array("*");
        $where = array("discordID" => $discordID);

        $this->_sqlQuery->sqlSelect($fields, "users", $where);

        if ($this->_sqlQuery->sqlCount() === 0) return;

        $result = $this->_sqlQuery->sqlFetch();

        $_SESSION["scoresaberID"] = $result["scoresaberID"];
    }

    public function isAuthenticated(): bool {
        return (isset($_SESSION["user"]) && !empty($_SESSION["user"])) && (isset($_SESSION["access_token"]) && !empty($_SESSION["access_token"]));
    }
    public function asAuthorization(): bool {
        if (isset($_SESSION["user_roles"])) {
            for ($i = 0; $i < count($_SESSION["user_roles"]); $i++) {
                if (in_array($_SESSION["user_roles"][$i], $GLOBALS["rolesAuthorized"])) {
                    return true;
                }
            }
        }

        return false;
    }
}