<?php /** @noinspection PhpDeprecationInspection */

namespace db;

use exceptions\ErrorCodes;
use http\Exception\RuntimeException;

/*
 * Error hiding is used for avoid deprecation warnings
 */

/** @property resource $connection */
class DbMysqlLegacy extends AbstractDb
{
    protected function createConnection() {
        $host     = $GLOBALS['msq_host'];
        $db       = $GLOBALS['msq_basa'];
        $login    = $GLOBALS['msq_login'];
        $password = $GLOBALS['msq_pass'];

        $conn = @mysql_connect($host, $login, $password, $db);

        if (!$conn) {
            throw new RuntimeException('Error connecting database', ErrorCodes::DB_CONNECT_ERROR);
        }

        $charset = @mysql_real_escape_string($GLOBALS['msq_charset'], $conn);

        @mysql_query('SET NAMES ' . $charset, $conn);
        @mysql_query('SET @@local.character_set_client=' . $charset, $conn);
        @mysql_query('SET @@local.character_set_results=' . $charset, $conn);
        @mysql_query('SET @@local.character_set_connection=' . $charset, $conn);

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

        return @mysql_query($sql, $this->connection);
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
}