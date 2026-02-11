<?php

class nqvPages extends nqvDbObject {

    protected int $id;
    protected ?string $title        = null;
    protected ?string $content      = null;
    protected ?string $description  = null;
    protected ?string $slug         = null;
    protected ?string $created_at   = null;
    protected ?string $modified_at  = null;
    protected int $created_by;
    protected static string $tablename = 'pages';

    public function get_name(): ?string {
        return $this->name;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function get_main_field() {
        return 'name';
    }
}
