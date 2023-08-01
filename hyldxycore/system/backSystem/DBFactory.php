<?php
namespace hyldxycore\system\backSystem;

use PDO;

class DBFactory {
    private PDO $_PDOConnection;

    private array $_configuration = array();

    public function __construct() {
        $this->_configuration = json_decode(file_get_contents(join(DS, array(HYLDXYCONFIG, "dbconfig.json"))), true);
        $this->PDOConnection();
    }

    /**
     * @return void
     */
    private function PDOConnection(): void {
        $this->_PDOConnection = new PDO("mysql:host=" . $this->_configuration["host"] . ";dbname=" . $this->_configuration["dbName"] . ";charset=utf8", $this->_configuration["user"], $this->_configuration["pass"], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }

    /**
     * @return PDO
     */
    public function getPDO(): PDO {
        return $this->_PDOConnection;
    }
}