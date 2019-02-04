<?php

namespace db;

abstract class AbstractDb
{
    const FETCH_NUM   = 1;
    const FETCH_ASSOC = 2;

    /** @var static */
    protected static $instance;
    protected        $connection;

    public static function getInstance() {
        if (static::$instance === null) {
            static::$instance = new static();
            static::$instance->init();
        }

        return static::$instance;
    }

    protected function __construct() {
        if ($this->connection === null) {
            $this->connection = $this->createConnection();
        }
    }

    abstract protected function createConnection();

    abstract protected function init();

    /**
     * @param string $sql
     * @param array $params
     * @return mixed
     *
     * $params array must be contains $key=>$value pairs where
     * - keys must point to placeholders inside SQL-query
     * prepended by colon.
     * - values must be correct casted. "string" for CHAR, VARCHAR etc.,
     * "int" for INTEGER, LONGINT etc., "float" for DECIMAL, FLOAT etc.
     *
     * Example for 'CREATE TABLE users(id INT, name VARCHAR(20))'
     * $sql = 'SELECT * FROM users WHERE id = :id AND name = :param';
     *
     * $params = array(':id' => (int)1, ':param' => (string)'Joe');
     *
     */
    abstract public function query($sql, $params = array());

    /**
     * @param $result
     * @param int $fetchType
     * @return mixed
     */
    abstract public function fetch($result, $fetchType = self::FETCH_ASSOC);

    /**
     * @param $result
     * @param int $fetchType
     * @return array
     */
    abstract public function fetchAll($result, $fetchType = self::FETCH_ASSOC);

    /**
     * @param $result
     * @return mixed
     */
    abstract public function fetchSingleValue($result);

    /**
     * @return int
     */
    abstract public function insertId();

    /**
     * @param string $string
     * @return string
     */
    abstract public function escape($string);

    /**
     * @param string $table
     * @return bool
     */
    abstract public function isTableExists($table);

    /**
     * @param string $table
     * @param string $field
     * @return bool|string
     */
    abstract public function isFieldExists($table, $field);

    /**
     * @param string $table
     * @param string $field
     * @return bool
     */
    abstract public function isIndexExists($table, $field);

    /**
     * @param $result
     * @return void
     */
    abstract public function release($result);
}