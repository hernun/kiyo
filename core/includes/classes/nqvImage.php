<?php

class nqvImage {
    protected $fields = [];
    protected $table;

    protected int $id;
    protected $filepath;
    protected string $tablename;
    protected $element_id;
    protected ?string $created_at;
    protected int $created_by;

    protected $error;

    public function __construct($input) {
        $this->table = new nqvDbTable('images');
        $this->fields = $this->table->getTableFields();
        if(is_string($input)) $input = ['filepath'=>$input];
        $this->parseData($input); 
    }

    protected function parseData($input): nqvImage{
        foreach($input as $k => $v) {
            if(property_exists($this,$k)) $this->$k = $v;
        }
        return $this;
    }

    public function get($k) {
        return @$this->$k;
    }

    public function set($k,$v): nqvImage {
        if(property_exists($this,$k)) $this->$k = $v;
        return $this;
    }
    
    public function exists() {
        return !empty($this->id);
    }

    public function save(?array $data = []) {
        $types = '';
        $vars = [];
        $fields = [];
        foreach($this->fields as $k => $v) {
            if(key_exists($k,$data)) $this->$k = $data[$k];
            if($k === 'id') continue;
            elseif($k === 'created_at') {
                if(empty($this->$k)) $this->$k = date('Y-m-d h:i:s');
                else continue;
            } elseif($k === 'created_by') {
                if(empty($this->$k)) $this->$k = nqv::getCurrentUserId();
                else continue;
            } elseif($k === 'filepath') {
                $this->$k = str_replace(ROOT_PATH,'',$this->$k);
            }
            $fields[] = $k . ' = ?';
            $field = new nqvDbField($v,$this->tablename);
            $types .= $field->getTypeLetter();
            $vars[] = $this->$k;
        }
        $sql = empty($this->id) ? 'INSERT INTO ':'UPDATE ';
        $sql .= $this->table->getTablename() . ' SET ';
        $sql .= implode(',',$fields);
        if(!empty($this->id)) {
            $types .= 'i';
            $vars[] = $this->id;
            $sql .= ' WHERE `id` = ?';
        }
        $stmt = nqvDB::prepare($sql);
        $stmt->bind_param($types,...$vars);
        $stmt->execute();
        if(!empty($stmt->error)) _log($stmt->error,'image-saving-error');
        if(empty($this->id)) {
            $this->id = $stmt->insert_id;
            return $stmt->insert_id;
        } else return empty($stmt->error);
    }

    public function setFileName($name) {
        $parts = pathinfo($this->filepath);
        $this->filepath = $parts['dirname'] . '/' . $name;
        return $this;
    }

    public function upload(string $tmp_name,?string $fileToDelete = null) {
        $dirname = dirname($this->get('filepath'));
        if(!is_dir($dirname)) mkdir($dirname,0775,true);
        $moved = move_uploaded_file($tmp_name,$this->get('filepath'));
        if($moved && $fileToDelete) {
            $fileToDelete = str_replace(ROOT_PATH,'',$fileToDelete);
            $fileToDelete = ROOT_PATH . $fileToDelete;
            if($fileToDelete !== $this->get('filepath')) {
                if(is_file($fileToDelete) ) unlink($fileToDelete);
            }
        } elseif(!$moved) {
            _log('No se pudo subir ' . $tmp_name . ' como  ' . $this->get('filepath'));
        }
        return $moved;
    }

    public function getSrc(): string {
        return empty($this->filepath) ? '':url(ROOT_PATH . $this->filepath);
    }

    public static function getImageById($id): nqvImage {
        $stmt = nqvDB::prepare('SELECT * FROM `images` WHERE `id` = ? LIMIT 1');
        $stmt->bind_param('i',$id);
        $result = nqvDB::parseSelect($stmt);
        return new nqvImage((array) @$result[0]);
    }

    public static function getByElementId($tablename,$id): array {
        $output = [];
        $stmt = nqvDB::prepare('SELECT * FROM `images` WHERE `element_id` = ? and `tablename` = ?');
        $stmt->bind_param('is',$id,$tablename);
        $result = nqvDB::parseSelect($stmt);
        if(!empty($result)) foreach($result as $input) $output[] = new nqvImage((array) $input);
        return $output;
    }

    public function delete() {
        $filepath = $this->get('filepath');
        if(empty($filepath)) {
            $stmt = nqvDB::prepare('select * from ' . $this->table->get('tablename') . ' where id = ?');
            $stmt->bind_param('i',$this->id);
            $filepath = @nqvDB::parseSelect($stmt)[0]['filepath'];
        }
        $filepath = ROOT_PATH . $filepath;
        if(is_file($filepath)) unlink($filepath);
        $sql = 'delete from ' . $this->table->get('tablename') . ' where id = ?';
        $stmt = nqvDB::prepare($sql);
        $stmt->bind_param('i',$this->id);
        $stmt->execute();
        $this->error = $stmt->error;
        return empty($this->error);
    }

    public function getError() {
        return $this->error;
    }

    public function getEncodedSrc() {
        $filepath = ROOT_PATH . $this->get('filepath');
        if(!is_file($filepath)) return null;
        return 'data:' . mime_content_type($filepath) . ';base64,' . base64_encode(file_get_contents($filepath));
    }
}