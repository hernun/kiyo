<?php
class nqvAdvertisers extends nqvDbObject {
    protected ?string  $name = '';
    protected ?string  $lastname = '';
    protected ?string  $company_name = '';
    protected ?string  $email = '';
    protected ?string  $cellphone;
    protected ?string $created_at;
    protected int $created_by;
    
    protected static string $tablename = 'advertisers';
    protected static $main_field = 'fullname';

    public function get_fullname() {
        return $this->name . ' ' . $this->lastname;
    }

    public static function getMainSqlFieldProperty() {
        return 'company_name';
    }
}