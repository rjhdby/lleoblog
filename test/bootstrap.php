<?php
/**
 * bootstrap file for testing purpose
 * Date: 05.02.2019
 */

function autoloader($class)
{
    foreach (array(ROOT.'/class/', __DIR__) as $path) {
        $class = str_replace('\\', '/', $class);
        $file  = $path . $class . '.php';
        echo 'Trying '.$file.PHP_EOL;
        if (is_file($file)) {
            include_once $file;

            return;
        }
    }
}

spl_autoload_register('autoloader');

function exception_handler($e)
{
    /** @var  Exception $e */
    var_dump($e->getTraceAsString());
}

set_exception_handler('exception_handler');

function shutdown()
{
    $error = error_get_last();
    if ($error !== null) {
        var_dump($error);
    }
}

register_shutdown_function('shutdown');

$GLOBALS['msq_host']    = '127.0.0.1';
$GLOBALS['msq_login']   = 'root';
$GLOBALS['msq_pass']    = '';
$GLOBALS['msq_basa']    = 'dnevnik';
$GLOBALS['msq_charset'] = 'cp1251';
$GLOBALS['acn']         = 1;

date_default_timezone_set('Europe/Moscow');

/* Stubs */
function ANDC()
{
    return " AND `acn`='" . $GLOBALS['acn'] . "'";
}

function logi($str)
{
    return false;
}

function idie()
{
    die('Error');
}