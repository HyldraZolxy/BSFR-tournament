<?php
namespace hyldxycore\system\backSystem;

use PDO;
use PDOStatement;

class SQLQuery {
    protected PDO          $_PDOConnection;
    protected PDOStatement $_result;

    public function __construct(PDO $PDOConnection) {
        $this->_PDOConnection = $PDOConnection;
    }

    /**
     * @description Return a WHERE clause with $key and $value formatted, it replace " ", ".", "-"  by "_" in $key
     * @param array $key
     * @param array $value
     * @return string
     */
    private function whereClause(array $key, array $value): string {
        return implode(" ", array_map(function($key, $value) {
            if ($value["operator"] === "IN") $sql = "$key {$value["operator"]} (" . implode(", ", array_map(fn($v) => ":" . str_replace(" ", "_", str_replace(".", "_", str_replace("-", "_", $v))), $value["value"])) . ")";
            else                             $sql = "$key {$value["operator"]} :" . str_replace(" ", "_", str_replace(".", "_", str_replace("-", "_", $key)));

            return $sql . (isset($value["between"]) ? " {$value["between"]}" : "");
        }, $key, $value));
    }

    /**
     * @description Return a JOIN clause with $joins formatted
     * @param array $joins
     * @return string
     */
    private function joinClause(array $joins): string {
        return implode(" ", array_map(function($join) {
            $type = (isset($join["type"])) ? $join["type"] : "INNER JOIN";
            return $type . " {$join["table"]} ON {$join["on"]}";
        }, $joins));
    }

    /**
     * @description Bind values in SQL query when $data["value"] IS NOT AN ARRAY
     * @param array $data
     * @param PDOStatement $sqlFinal
     * @return PDOStatement
     */
    private function foreachValue(array $data, PDOStatement $sqlFinal): PDOStatement {
        foreach ($data as $key => $value) {
            $sqlFinal->bindValue(":$key", $value);
        }

        return $sqlFinal;
    }

    /**
     * @description Bind values in SQL query when $data["value"] IS AN ARRAY
     * @param array $data
     * @param PDOStatement $sqlFinal
     * @return PDOStatement
     */
    private function foreachArrayValues(array $data, PDOStatement $sqlFinal): PDOStatement {
        foreach ($data as $key => $value) {
            if (is_array($value["value"])) {
                foreach ($value["value"] as $v) {
                    $sqlFinal->bindValue(":" . str_replace(" ", "_", str_replace(".", "_", str_replace("-", "_", $v))), $v);
                }
            } else $sqlFinal->bindValue(":" . str_replace(" ", "_", str_replace(".", "_", str_replace("-", "_", $key))), $value["value"]);
        }

        return $sqlFinal;
    }

    /**
     * @description Add data's in database, Result is stored in $this->_result
     * @param string $table
     * @param array $parameters
     * @return void
     */
    public function sqlAdd(string $table, array $parameters): void {
        $keys = array_keys($parameters);

        $sqlKeys   = "INSERT INTO $table (" . implode(", ", $keys) . ")";
        $sqlValues = " VALUES (:" . implode(", :", $keys) . ")";

        $sqlFinal = $this->_PDOConnection->prepare($sqlKeys . $sqlValues);

        $this->foreachValue($parameters, $sqlFinal)->execute();
        $this->_result = $sqlFinal;
    }

    /**
     * @description Delete data's in database, Result is stored in $this->_result
     * @param string $table
     * @param array $where
     * @return void
     */
    public function sqlDelete(string $table, array $where): void {
        $keysWhere   = array_keys($where);
        $valuesWhere = array_values($where);

        $whereClause = $this->whereClause($keysWhere, $valuesWhere);

        $sqlFinal = $this->_PDOConnection->prepare("DELETE FROM $table WHERE $whereClause");

        $this->foreachArrayValues($where, $sqlFinal)->execute();
        $this->_result = $sqlFinal;
    }

    /**
     * @description Update data's in database, Result is stored in $this->_result
     * @param string $table
     * @param array $parameters
     * @param array $where
     * @return void
     */
    public function sqlUpdate(string $table, array $parameters, array $where): void {
        $keysParams  = array_keys  ($parameters);
        $keysWhere   = array_keys  ($where);
        $valuesWhere = array_values($where);

        $setClause = implode(", ", array_map(function($key) {
            return "$key = :$key";
        }, $keysParams));

        $whereClause = $this->whereClause($keysWhere, $valuesWhere);

        $sqlFinal = $this->_PDOConnection->prepare("UPDATE $table SET $setClause WHERE $whereClause");

        $this->foreachValue      ($parameters, $sqlFinal);
        $this->foreachArrayValues($where,      $sqlFinal)->execute();
        $this->_result = $sqlFinal;
    }

    /**
     * @description: Select data's in database, Result is stored in $this->_result
     * @param string $table
     * @param array $column
     * @param array $where
     * @param array $order
     * @param array $limit
     * @param array $joins
     * @return void
     */
    public function sqlSelect(string $table, array $column, array $where = array(), array $order = array(), array $limit = array(), array $joins = array()): void {
        $valuesColumn = array_values($column);
        $keysWhere    = array_keys  ($where);
        $valuesWhere  = array_values($where);
        $keysOrder    = array_keys  ($order);
        $valuesOrder  = array_values($order);

        $columnClause = implode(", ", array_map(function($value) {
            return $value;
        }, $valuesColumn));

        $joinClause  = $this->joinClause($joins);
        $whereClause = $this->whereClause($keysWhere, $valuesWhere);

        $orderClause = implode(", ", array_map(function($key, $value) {
            return "$key $value";
        }, $keysOrder, $valuesOrder));

        $limitClause = implode(", ", array_map(function($value) {
            return $value;
        }, $limit));

        $sqlFinal = $this->_PDOConnection->prepare("SELECT $columnClause FROM $table" . ($joinClause != NULL ? " $joinClause" : "") . ($whereClause != NULL ? " WHERE $whereClause" : "") . ($orderClause != NULL ? " ORDER BY $orderClause" : "") . ($limitClause != NULL ? " LIMIT $limitClause" : ""));

        $this->foreachArrayValues($where, $sqlFinal)->execute();
        $this->_result = $sqlFinal;
    }

    /**
     * @description Fetching first data
     * @return array|bool
     */
    public function sqlFetch(): array|bool {
        return $this->_result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @description Fetching all data's
     * @return array|bool
     */
    public function sqlFetchAll(): array|bool {
        return $this->_result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @description Count data's of $this->_result
     * @return int
     */
    public function sqlCount(): int {
        return $this->_result->rowCount();
    }
}