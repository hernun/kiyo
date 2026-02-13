<?php

class nqvPages extends nqvDbObject {

    protected int $id;
    protected ?string $title        = null;
    protected ?string $content      = null;
    protected ?string $description  = null;
    protected ?string $slug         = null;
    protected ?array $properties    = [];
    protected ?string $created_at   = null;
    protected ?string $modified_at  = null;
    protected int $created_by;
    protected static string $tablename = 'pages';

    public function __construct($data = 0, string $tbl_name = '') {
        parent::__construct($data, $tbl_name);
        $this->parseProperties();
        return $this;
    }

    public function get_name(): ?string {
        return $this->name;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function get_main_field() {
        return 'name';
    }

    protected function parseProperties() {
        $default = nqv::getConfig('pagesdefaultproperties');
        $this->properties = array_merge((array) $default, (array) $this->properties);
    }

    public function getProperties() {
        if(empty($this->properties)) $this->parseProperties();
        return $this->properties;
    }

    public function getShowtitleInput(string $value): nqvDbField {
        $atts = [
            'Field' => 'showtitle',
            'Default' => '',
            'Null' => 'YES',
            'Type' => 'bool'
        ];
        $input = new nqvDbField($atts, 'pages');
        $input->setLabel('Mostrar título');
        $input->setHtmlInputType('switch');
        $input->setValue($value);
        return $input;
    }
}
