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
}