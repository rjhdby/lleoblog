<?php

namespace db;

use exceptions\ErrorCodes;
use http\Exception\RuntimeException;

class DbFactory
{
    private static $db;

    /**
     * @param array $order
     * @return AbstractDb
     */
    public static function getDb($order = array('pdo', 'mysqli', 'mysql')) {
        if (self::$db !== null) {
            return self::$db;
        }
        foreach ($order as $type) {
            try {
                switch ($type) {
                    case 'pdo':
                        $result = self::pdo();
                        break;
                    case 'mysqli':
                        $result = self::mysqli();
                        break;
                    case 'mysql':
                        $result = self::mysqlLegacy();
                        break;
                    default:
                        $result = false;
                }
            } catch (\RuntimeException $e) {
                $result = false;
            }
            if ($result !== false) {
                return $result;
            }
        }
        throw new RuntimeException('Database connection error', ErrorCodes::DB_CONNECT_ERROR);
    }

    /**
     * @return bool|AbstractDb
     */
    private static function pdo() {
        if (!extension_loaded('pdo_mysql')) {
            return false;
        }

        return DbMysqlPdo::getInstance();
    }

    private static function mysqli() {
        if (!extension_loaded('mysqli')) {
            return false;
        }

        return DbMysqli::getInstance();
    }

    private static function mysqlLegacy() {
        if (!extension_loaded('mysql')) {
            return false;
        }

        return DbMysqlLegacy::getInstance();
    }
}