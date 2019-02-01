<?php

namespace db;

use exceptions\ErrorCodes;
use http\Exception\RuntimeException;

/** @property \mysqli $connection */
class DbMysqli extends AbstractDb
{
    protected function createConnection() {
        $host     = $GLOBALS['msq_host'];
        $db       = $GLOBALS['msq_basa'];
        $login    = $GLOBALS['msq_login'];
        $password = $GLOBALS['msq_pass'];

        $conn = new \mysqli($host, $login, $password, $db);

        if (mysqli_connect_error()) {
            throw new RuntimeException('Error connecting database', ErrorCodes::DB_CONNECT_ERROR);
        }

        $charset = $conn->real_escape_string($GLOBALS['msq_charset']);

        $conn->query('SET NAMES ' . $charset);
        $conn->query('SET @@local.character_set_client=' . $charset);
        $conn->query('SET @@local.character_set_results=' . $charset);
        $conn->query('SET @@local.character_set_connection=' . $charset);

        return $conn;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return \mysqli_result
     */
    public function query($sql, $params = array()) {
        $stmt = $this->connection->prepare($sql);

        if (!empty($params)) {
            $template     = '';
            $substitution = array();
            foreach ($params as $key => $value) {
                $sql = str_replace($key, '?', $sql);
                if (is_string($value)) {
                    $template .= 's';
                } elseif (is_int($value)) {
                    $template .= 'i';
                } elseif (is_float($value)) {
                    $template .= 'd';
                } else {
                    throw new RuntimeException('Unsupported parameter type', ErrorCodes::DB_PARAMS_ERROR);
                }
                $substitution[] = $value;
            }

            array_unshift($substitution, $template);
            array_unshift($substitution, $stmt);
            call_user_func_array('mysqli_stmt_bind_param', $substitution);
        }
        $stmt->execute();

        return $stmt->get_result();
    }

    /**
     * @param \mysqli_result $result
     * @param int $fetchType
     * @return array|bool
     */
    public function fetch($result, $fetchType = self::FETCH_ASSOC) {
        switch ($fetchType) {
            case self::FETCH_ASSOC:
                return $result->fetch_assoc();
            case self::FETCH_NUM:
                return $result->fetch_row();
            default:
                throw new RuntimeException('Wrong fetch type', ErrorCodes::DB_FETCH_ERROR);
        }
    }

    /**
     * @param \mysqli_result $result
     * @param int $fetchType
     * @return array
     */
    public function fetchAll($result, $fetchType = self::FETCH_ASSOC) {
        switch ($fetchType) {
            case self::FETCH_ASSOC:
                return $result->fetch_all(MYSQLI_ASSOC);
            case self::FETCH_NUM:
                return $result->fetch_all();
            default:
                throw new RuntimeException('Wrong fetch type', ErrorCodes::DB_FETCH_ERROR);
        }
    }
}