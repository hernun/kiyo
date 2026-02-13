<?php
class nqvConfig extends nqvDbObject {
    protected int $id;
    protected ?string  $name;
    protected ?string  $value;
    protected ?string $slug;
    protected int $created_by;
    protected ?string $created_at;

    protected static string $tablename = 'config';
    protected static $main_field = 'name';
}