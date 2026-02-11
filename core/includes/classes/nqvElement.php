<?php

class nqvElement {
    protected $mainData = [];
    protected string $tablename = '';
    protected $data = [];
    protected $table = null;

    public function __construct(nqvDbTable $table, array $data) {
        $this->data = $data;
        $this->tablename = $table->getTablename();
        $this->mainData = [$table->getMainFieldName()];
        $this->table = $table;
    }

    protected function getMainData() {
        return $this->mainData;
    }

    public function getTablename() {
        return $this->tablename;
    }

    public function getData($k = '') {
        if(is_array($k)){
            $output = [];
            foreach($k as $m) {
                $field = $this->table->getField($m);
                $field->setValue(@$this->data[$m]);
                $output[] = $field->getHumanValue();
            }
            return $output;
        }
        elseif(!empty($k)) return @$this->data[$k];
        else return $this->data;
    }

    public function getFullname() {
        $output = [];
        $fields = $this->getMainData();
        foreach($fields as $k) {
            $field = $this->getData($k);
            if(is_array($field)) {
                $field = implode(' ',$field);
            }
            $output[] = $field;
        }
        return implode(' ',$output);
    }

    public function getDescription() {
        $data = $this->getData();
        if(isset($data['company_description'])) $output = $data['company_description'];
        elseif(isset($data['description'])) $output = $data['description'];
        else $output = '';
        return $output;
    }

    public function getMainPhoto() {
        $mainImage = nqvMainImages::getByElementId($this->tablename,$this->getData('id'));
        $src = $mainImage->getSrc();
        if(empty($src)) {
            $data = $this->getData();
            if(isset($data['image'])) $output = $data['image'];
            elseif(isset($data['photos'])) $output = $data['photos'];
            elseif(isset($data['services_photos'])) $output = $data['services_photos'];
            elseif(isset($data['images'])) $output = $data['images'];
            $output = implode(',',array_filter(explode(';',$output)));
            $output = explode(',',$output);
            return @array_filter((array) $output)[0];
        } else {
            return $src;
        }
        
    }

    public function getSocial() {
        $output = [];
        $data = $this->getData();
        if(!empty($data['instagram'])) $output['fa-brands fa-instagram'] = $data['instagram'];
        if(!empty($data['facebook'])) $output['fa-brands fa-facebook'] = $data['facebook'];
        if(!empty($data['youtube'])) $output['fa-brands fa-youtube'] = $data['youtube'];
        if(!empty($data['spotify'])) $output['fa-brands fa-spotify'] = $data['spotify'];
        if(!empty($data['bandcamp'])) $output['fa-brands fa-bandcamp'] = $data['bandcamp'];
        if(!empty($data['website'])) $output['fa-solid fa-globe'] = $data['website'];
        return $output;
    }

    public function __toString() {
        return $this->getFullname();
    }
}