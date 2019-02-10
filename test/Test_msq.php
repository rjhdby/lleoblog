<?php
include_once 'globals.php';
include_once ROOT . '/config.php.tmpl';
include_once 'bootstrap.php';
include_once ROOT . '/include_sys/_msq.php';

/*
 * Test suites for _msq.php
 *
 * Not covered functions:
 * set_ttl
 * cache_md5
 * cache_set
 * cache_get
 * cache_get_raw
 * cache_rm
 * userdata_load
 * userdata_save
 * userdata_get
 * get_dbuserdata
 *
 */

class Test_msq extends PHPUnit_Framework_TestCase
{
    /** @var TableTest $t */
    private $t;

    public function __construct() {
        $this->t = new TableTest();
        ms('CREATE TABLE IF NOT EXISTS test (id int NOT NULL AUTO_INCREMENT, text varchar(45), acn int, PRIMARY KEY (id), KEY value_idx (acn))');
    }

    public function testConnect() {
        $this->assertNotNull($GLOBALS['ms_connected']);
        $this->assertTrue($GLOBALS['ms_connected'] !== false);
    }

    // msq_add tested by $this->prepareData()
    public function test_msq_exists_msq_add() {
        $this->assertEquals(0, (int)msq_exist($this->t->table, "WHERE {$this->t->text} = 'test5'"));
        $this->assertEquals(1, (int)msq_exist($this->t->table, "WHERE {$this->t->text} = 'test1'"));
        $this->assertEquals(2, (int)msq_exist($this->t->table, "WHERE {$this->t->id} > 0"));
    }

    public function test_msq_id() {
        $id     = msq_id();
        $result = ms("SELECT MAX({$this->t->id}) FROM {$this->t->table}", '_l', 0);
        $this->assertEquals($id, $result);
    }

    public function test_msq_add1() {
        $result = msq_add1($this->t->table, array($this->t->text => "'test4'"));
        $this->assertTrue($result !== false);
        $result = msq_add1($this->t->table, array($this->t->text => 'test5'));
        $this->assertFalse($result);
        $this->assertEquals(1, msq_exist($this->t->table, "WHERE {$this->t->text} = 'test4'"));
        $this->assertEquals(0, msq_exist($this->t->table, "WHERE {$this->t->text} = 'test5'"));
    }

    public function test_msq_del() {
        $id = ms("SELECT {$this->t->id} FROM {$this->t->table} WHERE {$this->t->text}='test1'", '_l', 0);
        msq_del($this->t->table, array($this->t->id => $id, $this->t->text => 'test1'));
        msq_del($this->t->table, array($this->t->text => 'test2'), "AND {$this->t->id} > 0");
        $this->assertEquals(0, msq_exist($this->t->table, "WHERE {$this->t->text} = 'test1'"));
        $this->assertEquals(0, msq_exist($this->t->table, "WHERE {$this->t->text} = 'test2'"));
    }

    public function test_e() {
        $str = chr(10) . chr(0) . chr(13) . chr(26) . chr(34) . chr(39);
        $this->assertEquals('\n\0\r\Z\"\\\'', e($str));
    }

    public function test_arae() {
        $result = arae(array(chr(10) => chr(0), chr(13) => chr(26), chr(34) => chr(39)));
        $this->assertEquals(array('\n' => '\0', '\r' => '\Z', '\"' => "\'"), $result);
    }

    public function test_msq_update() {
        msq_update($this->t->table, array($this->t->text => 'test22', $this->t->value => 2), "WHERE {$this->t->text}='test2'");
        $text = ms("SELECT {$this->t->text} FROM {$this->t->table} WHERE {$this->t->value} = 2", '_l', 0);
        $this->assertEquals('test22', $text);
    }

    // Eternal pain
    // $u contains 'WHERE '
    public function test_msq_add_update_u_where() {
        msq_add_update($this->t->table, array($this->t->text => 'test22', $this->t->value => 2), "WHERE {$this->t->text}='test2'");
        $this->assertEquals(1, (int)msq_exist($this->t->table, "WHERE {$this->t->text} = 'test22'"));
        $this->assertEquals(2, (int)msq_exist($this->t->table, "WHERE {$this->t->id} > 0"));

        msq_add_update($this->t->table, array($this->t->text => 'test33', $this->t->value => 2), "WHERE {$this->t->text}='test3'");
        $this->assertEquals(1, (int)msq_exist($this->t->table, "WHERE {$this->t->text} = 'test33'"));
        $this->assertEquals(3, (int)msq_exist($this->t->table, "WHERE {$this->t->id} > 0"));
    }

    // If using default $u then $arr MUST contains 'id' key
    // If using $u with keys, all those keys MUST be in $arr
    // $u contains keys
    public function test_msq_add_update_u_keys() {
        msq_add_update($this->t->table, array($this->t->text => 'test2', $this->t->value => 10), $this->t->text);
        $this->assertEquals(10, (int)ms("SELECT {$this->t->value} FROM {$this->t->table} WHERE {$this->t->text} = 'test2'", '_l', 0));
        msq_add_update($this->t->table, array($this->t->text => 'test3', $this->t->value => 10), $this->t->text);
        $this->assertEquals(3, (int)msq_exist($this->t->table, "WHERE {$this->t->id} > 0"));
    }

    // If using $u with ANDC then $u MUST contains at least two keys
    // $u contains ANDC
    public function test_msq_add_update_u_ANDC() {
        $id = ms("SELECT MAX({$this->t->id}) FROM {$this->t->table}", '_l', 0);
        msq_add_update($this->t->table, array($this->t->text => 'test22', $this->t->id => $id, $this->t->value => 1), $this->t->id . ' ANDC');
        $this->assertEquals(1, (int)msq_exist($this->t->table, "WHERE {$this->t->text} = 'test22'"));
        $this->assertEquals(2, (int)msq_exist($this->t->table, "WHERE {$this->t->id} > 0"));
    }

    public function test_msq_table() {
        $result = msq_table($this->t->table);
        $this->assertTrue($result);
        $result = msq_table('missed_table');
        $this->assertFalse($result);
    }

    public function test_msq_pole() {
        $result = msq_pole($this->t->table, $this->t->text);
        $this->assertEquals(mb_strtolower($result), mb_strtolower($this->t->textType));
        $result = msq_pole('missed_table', $this->t->text);
        $this->assertFalse($result);
        $result = msq_pole($this->t->table, 'missed_field');
        $this->assertFalse($result);
    }

    public function test_msq_index() {
        $result = msq_index($this->t->table, $this->t->primary);
        $this->assertSame($result, 1);
        $result = msq_index($this->t->table, $this->t->index);
        $this->assertTrue($result);
        $result = msq_index($this->t->table, $this->t->text);
        $this->assertFalse($result);
        $result = msq_index('missed_table', $this->t->primary);
        $this->assertFalse($result);
        $result = msq_index($this->t->table, 'missed_field');
        $this->assertFalse($result);
    }

    public function test_ms() {
        $result = ms("SELECT * FROM {$this->t->table}", '_1', 0);
        $this->assertEquals(3, count($result));
        $this->assertEquals(array($this->t->id, $this->t->text, $this->t->value), array_keys($result));

        $result = ms("SELECT * FROM {$this->t->table}", '_a', 0);
        $this->assertEquals(2, count($result));
        $this->assertEquals(array(0, 1), array_keys($result));

        $result = ms("SELECT {$this->t->text}, {$this->t->value} FROM {$this->t->table} ORDER BY {$this->t->text}", '_l', 0);
        $this->assertEquals('test1', $result);
    }

    /**
     * @before
     */
    public function prepareData() {
        msq("DELETE FROM {$this->t->table}");
        msq_add($this->t->table, array($this->t->text => 'test1'));
        $result = msq_add($this->t->table, array($this->t->text => 'test2', $this->t->value => 1));
        $this->assertTrue($result !== false);
    }
}