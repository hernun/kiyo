<?php

class nqvSearch {
    protected string $tablename = '';

    public function __construct($tablename = null) {
        if($tablename) $this->tablename = $tablename;
    }

    protected function getSrcVarName() {
        return 'search' . ucfirst($this->tablename) . 'DataSrc';
    }

    protected function getData() {
        $classname = 'nqv' . ucfirst($this->getTablename());
        if(class_exists($classname)) return $classname::getDataToSearch();
    }

    protected function getTablename() {
        return $this->tablename;
    }

    public function __toString() {
        ob_start();
        include ADMIN_TEMPLATES_PATH . 'search-form.php';
        return ob_get_clean();
    }
}