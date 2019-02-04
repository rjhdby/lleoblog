<?php

namespace db;

use exceptions\ErrorCodes;
use http\Exception\RuntimeException;

/** @property \mysqli $connection */
class DbMysqli extends AbstractMysql
{
    protected function createConnection() {
        $conn = new \mysqli($GLOBALS['msq_host'], $GLOBALS['msq_login'], $GLOBALS['msq_pass'], $GLOBALS['msq_basa']);

        if (mysqli_connect_error()) {
            throw new RuntimeException('Error connecting database', ErrorCodes::DB_CONNECT_ERROR);
        }

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
        if ($this->connection->errno) {
            throw new RuntimeException($this->connection->error, ErrorCodes::DB_QUERY_ERROR);
        }

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
                $out = $result->fetch_assoc();
                break;
            case self::FETCH_NUM:
                $out = $result->fetch_row();
                break;
            default:
                throw new RuntimeException('Wrong fetch type', ErrorCodes::DB_FETCH_ERROR);
        }

        return $out === null ? false : $out;
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

    /**
     * @return int
     */
    public function insertId() {
        return $this->connection->insert_id;
    }

    /**
     * @param string $string
     * @return string
     */
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    /**
     * @param \mysqli_result $result
     * @return void
     */
    public function release($result) {
        $result->free();
    }
}