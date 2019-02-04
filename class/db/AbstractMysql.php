<?php

namespace db;

abstract class AbstractMysql extends AbstractDb
{
    protected function init() {
        $charset = $this->escape($GLOBALS['msq_charset']);

        $this->query('SET NAMES ' . $charset);
        $this->query('SET @@local . character_set_client = ' . $charset);
        $this->query('SET @@local . character_set_results = ' . $charset);
        $this->query('SET @@local . character_set_connection = ' . $charset);
    }

    /**
     * @param $result
     * @return mixed
     */
    public function fetchSingleValue($result) {
        $out = $this->fetch($result, self::FETCH_NUM);

        return $out === null ? false : $out[0];
    }

    /**
     * @param string $table
     * @return bool
     */
    public function isTableExists($table) {
        $table  = $this->escape($table);
        $result = $this->query("SHOW TABLES LIKE '$table'");

        return $this->fetch($result) !== false;
    }

    /**
     * @param string $table
     * @param string $field
     * @return bool|string
     */
    public function isFieldExists($table, $field) {
        if (!$this->isTableExists($table)) {
            return false;
        }
        $table  = $this->escape($table);
        $stmt   = $this->query("SHOW COLUMNS FROM $table WHERE field=:field", array(':field' => $field));
        $result = $this->fetch($stmt);

        return $result === false ? false : $result['Type'];
    }

    /**
     * @param string $table
     * @param string $field
     * @return bool
     */
    public function isIndexExists($table, $field) {
        if (!$this->isTableExists($table)) {
            return false;
        }
        $table  = $this->escape($table);
        $stmt   = $this->query("SHOW INDEX FROM $table WHERE column_name=:field", array(':field' => $field));
        $result = $this->fetch($stmt);

        return $result === false ? false : $result['Key_name'];
    }
}