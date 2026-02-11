<?php
    
    class nqvSession_types extends nqvDbObject {
        protected $name;
        protected ?string $slug;
        protected static $main_field = 'name';

        protected static string $tablename = 'session_types';

        public function __construct($data = null) {
            parent::__construct($data,$this->getTablename());
        }
        
        public function getTablename(): string {
            return 'session_types';
        }

        public function get_name(): ?string {
            return $this->name;
        }

        public function get_main_field() {
            return 'name';
        }

        public static function getMainSqlFieldProperty() {
            return 'name';
        }
    }