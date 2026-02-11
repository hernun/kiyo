<?php

class nqvDbTable {
    protected $isTable = false;
    protected string $tablename = '';

    public function __construct($tablename) {
        $this->tablename = $tablename;
        $this->isTable = nqvDB::isTable($tablename);
    }

    public function getTableFields(): array {
        try {
            $describe = nqvDB::describe($this->tablename);
            return $describe;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getTablename() {
        return $this->tablename;
    }

    public function getField($fieldname){
        $class = 'nqv' . ucfirst($this->tablename);
        $object = new $class;
        $fields = $object->getFields();
        return @$fields[$fieldname];
    }

    public function getStaticMainField() {
        $class = 'nqv' . ucfirst($this->tablename);
        $object = new $class;
        return $object::getStaticMainField();
    }

    public function getMainFieldName() {
        return array_values($this->getTableFields())[1]['Field'];
    }

    public function isTable() {
        return $this->isTable;
    }

    public function get($k) {
        $meth = 'get_' . strtolower($k);
        if (method_exists($this, $meth)) return $this->$meth();
        else return $this->$k;
    }
}