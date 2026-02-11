<?php
class nqvConfig extends nqvDbObject {
    protected int $id;
    protected $name;
    protected $value;
    protected ?string $slug;
    protected int $created_by;
    protected ?string $created_at;

    protected static string $tablename = 'config';
    protected static $main_field = 'name';
}