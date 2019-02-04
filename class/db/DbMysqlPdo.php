<?php

namespace db;

use exceptions\ErrorCodes;
use RuntimeException;

/** @property \PDO $connection */
class DbMysqlPdo extends AbstractMysql
{
    protected function createConnection() {
        $dns = 'mysql:host=' . $GLOBALS['msq_host'] . ';dbname=' . $GLOBALS['msq_basa'];

        try {
            $conn = new \PDO($dns, $GLOBALS['msq_login'], $GLOBALS['msq_pass']);
        } catch (\PDOException $e) {
            throw new RuntimeException('Error connecting database', ErrorCodes::DB_CONNECT_ERROR);
        }

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

        if ($stmt->errorCode() !== '00000') {
            $error = $stmt->errorInfo();
            throw new RuntimeException($error[1] . ':' . $error[2], ErrorCodes::DB_QUERY_ERROR);
        }

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

    /**
     * @return int
     */
    public function insertId() {
        return (int)$this->connection->lastInsertId();
    }

    /**
     * @param string $string
     * @return string
     */
    public function escape($string) {
        return $this->connection->quote($string);
    }

    /**
     * @param \PDOStatement $result
     * @return void
     */
    public function release($result) {
        $result->closeCursor();
    }
}