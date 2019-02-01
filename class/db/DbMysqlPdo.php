<?php

namespace db;

use exceptions\ErrorCodes;
use RuntimeException;

/** @property \PDO $connection */
class DbMysqlPdo extends AbstractDb
{
    protected function createConnection() {
        $host     = $GLOBALS['msq_host'];
        $db       = $GLOBALS['msq_basa'];
        $login    = $GLOBALS['msq_login'];
        $password = $GLOBALS['msq_pass'];

        $dns = 'mysql:host=' . $host . ';dbname=' . $db;

        try {
            $conn = new \PDO($dns, $login, $password);
        } catch (\PDOException $e) {
            throw new RuntimeException('Error connecting database', ErrorCodes::DB_CONNECT_ERROR);
        }
        $charset = $conn->quote($GLOBALS['msq_charset']);

        $conn->exec('SET NAMES ' . $charset);
        $conn->exec('SET @@local . character_set_client = ' . $charset);
        $conn->exec('SET @@local . character_set_results = ' . $charset);
        $conn->exec('SET @@local . character_set_connection = ' . $charset);

        return $conn;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    public function query($sql, $params = array()) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * @param \PDOStatement $result
     * @param int $fetchType
     * @return array|bool
     */
    public function fetch($result, $fetchType = self::FETCH_ASSOC) {
        switch ($fetchType) {
            case self::FETCH_ASSOC:
                return $result->fetch(\PDO::FETCH_ASSOC);
            case self::FETCH_NUM:
                return $result->fetch(\PDO::FETCH_NUM);
            default:
                throw new RuntimeException('Wrong fetch type', ErrorCodes::DB_FETCH_ERROR);
        }
    }

    /**
     * @param \PDOStatement $result
     * @param int $fetchType
     * @return array
     */
    public function fetchAll($result, $fetchType = self::FETCH_ASSOC) {
        switch ($fetchType) {
            case self::FETCH_ASSOC:
                return $result->fetchAll(\PDO::FETCH_ASSOC);
            case self::FETCH_NUM:
                return $result->fetchAll(\PDO::FETCH_NUM);
            default:
                throw new RuntimeException('Wrong fetch type', ErrorCodes::DB_FETCH_ERROR);
        }
    }
}