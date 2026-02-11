<?php

class nqvStates {
    protected $name;
    protected $countries_id;
    protected $code;
    protected $is_active;

    public static function getMainSqlFieldProperty() {
        return 'name';
    }

}