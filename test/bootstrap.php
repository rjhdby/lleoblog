<?php
/**
 * bootstrap file for testing purpose
 * Date: 05.02.2019
 */

spl_autoload_register(
    function ($class) {
        foreach (array('../class/', './') as $path) {
            $class = str_replace('\\', '/', $class);
            $file  = $path . $class . '.php';
            if (is_file($file)) {
                include_once $file;

                return;
            }
        }
    }
);

set_exception_handler(
    function ($e) {
        /** @var  Exception $e */
        var_dump($e->getTraceAsString());
    }
);

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null) {
        var_dump($error);
    }
});

$GLOBALS['msq_host']    = '127.0.0.1';
$GLOBALS['msq_login']   = 'root';
$GLOBALS['msq_pass']    = '';
$GLOBALS['msq_basa']    = 'dnevnik';
$GLOBALS['msq_charset'] = 'cp1251';
$GLOBALS['acn']         = 1;

/* Stub for _authorize.php:ANDC() */
function ANDC(){
    return " AND `acn`='".$GLOBALS['acn']."'";
}