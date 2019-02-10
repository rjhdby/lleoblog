<?php

class TableTest
{
    public $table    = 'test';
    public $id       = 'id';          //int auto_increment field
    public $text     = 'text';        //text field
    public $textType = 'varchar(45)'; //text field type
    public $value    = 'acn';         //int field (MUST be acn)
    public $primary  = 'id';          //primary key field
    public $index    = 'acn';         //indexed field (not key)

    /**
     * TableTest constructor.
     */
    public function __construct() {
        /** @var mysqli $db */
        $db = $GLOBALS['ms_connected'];
        $db->query("
            CREATE TABLE IF NOT EXISTS {$this->table}(
              {$this->id} int NOT NULL AUTO_INCREMENT, 
              {$this->text} {$this->textType}, 
              {$this->value} int, 
              PRIMARY KEY ({$this->primary}), 
              KEY value_idx ({$this->index})
            )
        ");
    }
}