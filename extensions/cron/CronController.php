<?php
namespace extensions\cron;

use extensions\api\ApiController;
use hyldxycore\system\backSystem\DBFactory;
use hyldxycore\system\backSystem\SQLQuery;
use hyldxycore\system\backSystem\Tools;

class CronController {
    private DBFactory     $_dbFactory;
    private SQLQuery      $_sqlQuery;
    private ApiController $_apiController;
    private Tools         $_tools;

    private string $_cronKey;

    public function __construct() {
        $this->_dbFactory     = new DBFactory();
        $this->_sqlQuery      = new SQLQuery($this->_dbFactory->getPDO());
        $this->_apiController = new ApiController();
        $this->_tools         = new Tools();

        $this->_cronKey = json_decode(file_get_contents(join(DS, array(HYLDXYCONFIG, "cronconfig.json"))), true);
    }

    /**
     * @description Check if the key is valid
     * @param $_GET["key"]
     * @return bool
     */
    private function checkCronKey(): bool {
        if (isset($_GET["key"]) && $_GET["key"] === $this->_cronKey) return true;

        return false;
    }

    /**
     * @description Update the status of the tournaments
     * @return string[]
     */
    private function cronUpdateTournaments(): array {
        $tournamentData = json_decode($this->_apiController->getTournaments(), true);
        if (empty($tournamentData)) return array("error" => "No tournaments found");

        foreach ($tournamentData as $tournament) {
            $parameters = array(
                "status" => $this->_tools->checkDateStatus($tournament)
            );
            $where = array(
                "id" => array(
                    "value" => $tournament["id"],
                    "operator" => "="
                )
            );
            $this->_sqlQuery->sqlUpdate("tournaments", $parameters, $where);
        }

        return array("success" => "Tournaments updated");
    }

    /**
     * @description Update the status of the pools
     * @return string[]
     */
    private function cronUpdatePools(): array {
        $poolData = json_decode($this->_apiController->getAllPools(), true);
        if (empty($poolData)) return array("error" => "No pools found");

        foreach ($poolData as $pool) {
            $parameters = array(
                "status" => $this->_tools->checkDateStatus($pool)
            );
            $where = array(
                "poolID" => array(
                    "value" => $pool["poolID"],
                    "operator" => "=",
                    "between" => "AND"
                ),
                "tournamentID" => array(
                    "value" => $pool["tournamentID"],
                    "operator" => "="
                )
            );
            $this->_sqlQuery->sqlUpdate("poolbytournament", $parameters, $where);
        }

        return array("success" => "Pools updated");
    }

    public function index(): string {
        if (!$this->checkCronKey()) return json_encode(array("error" => "Invalid key"));

        $resultTournament = $this->cronUpdateTournaments();
        $resultPool       = $this->cronUpdatePools();

        $array   = array();
        $array[] = $resultTournament;
        $array[] = $resultPool;

        return json_encode($array);
    }
}