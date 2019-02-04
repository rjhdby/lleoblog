<?php //if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй

use db\DbFactory;

$starttime = time();

function set_ttl() { global $admin,$ttl,$jaajax,$MYPAGE,$MYPAGE_MD5;
	if($admin) { if($jaajax) $ttl=0; else {
            $MYPAGE_MD5 = md5($MYPAGE);
            $ttl        = (isset($_COOKIE['MYPAGE']) && $MYPAGE_MD5 == $_COOKIE['MYPAGE'] ? 0 : 60);
            setcoo('MYPAGE', $MYPAGE_MD5, time() + 20);
        }
    } else if (!isset($ttl)) $ttl = 60;
} set_ttl();

/*
ПОЛЕЗНЫЕ ПРИМЕРЫ

include_once $_SERVER['DOCUMENT_ROOT']."/dnevnik/_msq.php"; msq_open('lleo');

	$ara=array();
	$ara['name']=e($name);
	$ara['sc']=e($sc);
	$ara['ipipx']=e($_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_X_FORWARDED_FOR']);
	$ara['value']=e($value);

if(!msq_exist($db_,"WHERE `name`='".$ara['name']."' AND (`sc`='".$ara['sc']."' OR `ipipx`='".$ara['ipipx']."')"))
msq_add($db_,$ara);

$n=intval(msqn(msq("SELECT `value` FROM `$db_` WHERE `name`='$name' AND `value`='$l'")));

msq_update($tb,$ara,"WHERE `name`='lleo'");

msq_add_update($db_,array('name'=>$name,'text'=>implode("\n",$o)),'name');

msq_del($tb,$ara,$u='')
*/

// if(!isset($memcache)) cache_init();
$msqe = ''; // сюда пишем ошибки
ms_connect(); // соединиться с базой - эта процедура в _autorize.php

function ms_connect() {
    if (isset($GLOBALS['ms_connected'])) return;

    try {
        $GLOBALS['ms_connected'] = DbFactory::getDb();
    } catch (Exception $e) {
        logi('MSQ_ERRORS.txt', "\n" . date('Y-m-d H:i:s') . ' error');
        idie('<p>MySQL error!' . ($GLOBALS['admin'] ?
                 "Check config.php:<ul> \$msq_host = '{$GLOBALS['msq_host']}';<br>\$msq_login = '{$GLOBALS['msq_login']}';<br>\$msq_pass = [...]"
                 : 'May be it is a temporarry problem? Try to reload page in several seconds or minutes.'
             ));
    }
}

function msq_id() { return $GLOBALS['ms_connected']->insertId(); }

function e($s) { return $GLOBALS['ms_connected']->escape($s); }

function msq_exist($tb, $u) { return ms("SELECT COUNT(*) FROM $tb $u", '_l', 0); }

//function msqn($sql) { return mysql_num_rows($sql); }

function msq_add($tb, $ara) {
    $a=$b=''; foreach($ara as $n=>$m) { $a.="`$n`,"; $b.="'$m',"; } $a=trim($a,','); $b=trim($b,',');
    $s = "INSERT INTO $tb ($a) VALUES ($b)";
    return msq($s);
}

function msq_add1($tb, $ara) {
        $a=$b=''; foreach($ara as $n=>$m) { $a.="`$n`,"; $b.="$m,"; } $a=trim($a,','); $b=trim($b,',');
    $s = "INSERT INTO $tb ($a) VALUES ($b)";
    return msq($s);
}

function msq_update($tb, $ara, $u = '') {
    $a = '';
    foreach ($ara as $n => $m) $a .= "`$n`='$m',";
    $a = trim($a, ',');
    $s = "UPDATE $tb SET $a $u";

    return msq($s);
}

function msq_add_update($tb, $ara, $u = 'id') {
    if (!stristr($u, 'WHERE ')) {
        $keys = explode(' ', $u);
        $u    = array();
        foreach ($keys as $k) {
            if ($k == 'ANDC') break;
            $u[] = "`" . e($k) . "`='" . e($ara[ $k ]) . "'";
        }
        $u = "WHERE " . implode(' AND ', $u) . ($k == 'ANDC' ? ANDC() : '');
    }
    if (!msq_exist($tb, $u)) $s = msq_add($tb, $ara);
    else {
        if (count($keys)) {
            foreach ($keys as $k) unset($ara[ $k ]);
        }
        $s = msq_update($tb, $ara, $u);
    }

    return $s;
}

function msq_del($tb, $ara, $u = '') {
    $a = '';
    foreach ($ara as $n => $m) $a .= "`$n`='$m',";
    $a = trim($a, ',');
    $s = "DELETE FROM $tb WHERE $a $u";

    return msq($s);
}

$GLOBALS['msqe_last'] = '';

function msq($sql) {
    global $msqe;
    $GLOBALS['msqe_last'] = $sql;

    if (time() - $GLOBALS['starttime'] > 15) {
        logi('starttime.log', "\nerror: " . $GLOBALS['MYPAGE']);
        if ($GLOBALS['ajax']) idie('Timeout error');
        die('Timeout error');
    }

    try {
        $result = $GLOBALS['ms_connected']->query($sql);
    } catch (Exception $e) {
        $result = false;
        $msqe   .= "<p><font color=green>DB query (\"$sql\") </font><br><font color=red>{$e->getMessage()}</font>";
    }

    return $result;
}

function msq_pole($table, $field) { // проверить, существует ли такое поле в таблице $tb
    return $GLOBALS['ms_connected']->isFieldExists($table, $field);
}

function msq_table($table) { // проверить, существует ли такая таблица
    return $GLOBALS['ms_connected']->isTableExists($table);
}

function msq_index($tb, $index) { // проверить, существует ли такой индекс (если указан еще ,0 - то первичный)
    $name = $GLOBALS['ms_connected']->isIndexExists($tb, $index);
    if ($name === false) return false;

    return $name === 'PRIMARY' ? 1 : true; //bullshit
}

//function tos($e) { return str_replace(array("\\","'",'"',"\n","\r"),array("\\\\","\\'",'\\"',"\\n",""),$e); }

function ms($query, $mode = '_a', $ttl = 666) {
    $magic = '@' . $GLOBALS['blogdir'];
    if ($ttl === 666) $ttl = $GLOBALS['ttl'];

    if ($ttl < 0) {
        cache_rm($mode . $magic . $query);

        return true;
    } // сбросить кэш
    if ($ttl > 0) {
        $result = cache_get($mode . $magic . $query);
        if (false !== $result) {
            $GLOBALS['ms_ttl'] = 'cache';

            return $result;
        }
    }
    $GLOBALS['ms_ttl'] = 'new';
    $resultSet         = msq($query);
    if ($resultSet === false) return false;

    switch ($mode) {
        case '_1':
            $s = $GLOBALS['ms_connected']->fetch($resultSet);
            break;
        case '_l':
            $s = $GLOBALS['ms_connected']->fetchSingleValue($resultSet);
            break;
        default:
            $s = $GLOBALS['ms_connected']->fetchAll($resultSet);
    }
    $GLOBALS['ms_connected']->release($resultSet);

    if (empty($s)) $s = false;

    if ($ttl > 0) {
        cache_set($mode . $magic . $query, $s, $ttl);
    }

    return $s;
}

// function cache_init() { global $memcache; $memcache=memcache_connect('memcache_host', 11211); }
function cache_md5($k) { global $msq_host,$msq_basa; return substr(sha1("$msq_host $msq_basa $k"),0,8); }
function cache_set($k,$v,$e) { global $memcache; if(!$memcache) return false; return memcache_set($memcache,cache_md5($k),$v,MEMCACHE_COMPRESSED,$e); }
function cache_get($k) { global $memcache; if(!$memcache) return false; return memcache_get($memcache,cache_md5($k)); }
function cache_get_raw($k) { global $memcache; if(!$memcache) return false; return memcache_get($memcache,$k); }
function cache_rm($k) { global $memcache; if(!$memcache) return false; $k=cache_md5($k); memcache_set($memcache,$k,false,0,1); return memcache_delete($memcache,$k); }
function arae($ara){ $p=array(); foreach($ara as $n=>$l) $p[e($n)]=e($l); return $p; }

// утилиты работы с юзердатой
function userdata_load($basa, $name) { return ms("SELECT `data` FROM `" . get_dbuserdata() . "` WHERE `basa`='" . e($basa) . "' AND `name`='" . e($name) . "'" . ANDC(), "_l", 0); }

function userdata_save($basa, $name, $data) { return msq_add_update(get_dbuserdata(), array('data' => e($data), 'basa' => e($basa), 'name' => e($name), 'acn' => intval($GLOBALS['acn'])), "basa name ANDC"); }

function userdata_get($basa, $f = 0, $l = 99999) { return ms("SELECT `name`,`data` FROM `" . get_dbuserdata() . "` WHERE `basa`='" . e($basa) . "'" . ANDC() . " LIMIT " . intval($f) . "," . intval($l), "_a", 0); }

function get_dbuserdata() { return e(empty($GLOBALS['db_userdata']) ? 'userdata' : $GLOBALS['db_userdata']); }

?>