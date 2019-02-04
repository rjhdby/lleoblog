<?php /** @noinspection PhpDeprecationInspection */

namespace db;

use exceptions\ErrorCodes;
use http\Exception\RuntimeException;

/*
 * Error hiding is used for avoid deprecation warnings
 */

/** @property resource $connection */
class DbMysqlLegacy extends AbstractMysql
{
    protected function createConnection() {
        $conn = @mysql_connect($GLOBALS['msq_host'], $GLOBALS['msq_login'], $GLOBALS['msq_pass'], $GLOBALS['msq_basa']);

        if (!$conn) {
            throw new RuntimeException('Error connecting database', ErrorCodes::DB_CONNECT_ERROR);
        }

        return $conn;
    }

    public function __destruct() {
        if ($this->connection) {
            mysql_close($this->connection);
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return resource
     */
    public function query($sql, $params = array()) {
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                $sql = str_replace($key, "'" . @mysql_real_escape_string($value) . "'", $sql);
            } elseif (is_int($value) || is_float($value)) {
                $sql = str_replace($key, $value, $sql);
            } else {
                throw new RuntimeException('Unsupported parameter type', ErrorCodes::DB_PARAMS_ERROR);
            }
        }
        $result = @mysql_query($sql, $this->connection);
        if (@mysql_errno()) {
            throw new RuntimeException(@mysql_error(), ErrorCodes::DB_QUERY_ERROR);
        }

        return $result;
    }

    /**
     * @param resource $result
     * @param int $fetchType
     * @return array|bool
     */
    public function fetch($result, $fetchType = self::FETCH_ASSOC) {
        switch ($fetchType) {
            case self::FETCH_ASSOC:
                return @mysql_fetch_assoc($result);
            case self::FETCH_NUM:
                return @mysql_fetch_row($result);
            default:
                throw new RuntimeException('Wrong fetch type', ErrorCodes::DB_FETCH_ERROR);
        }
    }

    /**
     * @param resource $result
     * @param int $fetchType
     * @return array
     */
    public function fetchAll($result, $fetchType = self::FETCH_ASSOC) {
        switch ($fetchType) {
            case self::FETCH_ASSOC:
                return @mysql_fetch_array($result, MYSQL_ASSOC);
            case self::FETCH_NUM:
                return @mysql_fetch_array($result, MYSQL_NUM);
            default:
                throw new RuntimeException('Wrong fetch type', ErrorCodes::DB_FETCH_ERROR);
        }
    }

    /**
     * @return int
     */
    public function insertId() {
        return mysql_insert_id($this->connection);
    }

    /**
     * @param string $string
     * @return string
     */
    public function escape($string) {
        return mysql_real_escape_string($string, $this->connection);
    }

    /**
     * @param $result
     * @return void
     */
    public function release($result) {
        @mysql_free_result($result);
    }
}