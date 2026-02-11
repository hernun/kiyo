<?php
    
    class nqvSessionType extends nqvDbObject {
        protected static string $tablename = 'session_types';
        protected $name;
        protected $world;
        
        public function get_name(): ?string {
            return $this->name;
        }
        
        public function get_world(): ?string {
            return $this->world;
        }

        public function get_main_field() {
            return 'name';
        }
    }