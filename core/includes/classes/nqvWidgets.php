<?php
class nqvWidgets {
    protected int $id;
    protected $name;
    protected $altname;
    protected ?string $slug;
    protected ?string $description;
    protected $public;
    protected $order;
    protected $fields;
    // 2025-03-25 14:55:55
    protected ?string $created_at;
    protected int $created_by;
    protected ?string $modified_at;

    protected $exists;
    protected static $table;

    public function __construct(?array $data = []) {
        $this->created_by = nqv::getCurrentUserId();
        if($data) $this->set_data($data);
    }

    public static function getMainSqlFieldProperty() {
        return 'name';
    }


    public static function setTable() {
        $vars = get_class_vars(self::class);
        self::$table = new nqvDbTable('widgets');
    }
    
    public function getField($fieldname){
        $fields = $this->getFields();
        return @$fields[$fieldname];
    }

    public static function getTable() {
        if(empty(self::$table)) self::setTable();
        return self::$table;
    }

    public static function getTableFields(): array {
        try {
            return self::getTable()->getTableFields();
        } catch (Exception $e) {
            #throw new Exception($e->getMessage());
            return [];
        }
    }

    public function getFields(){
        if(empty($this->fields)) {
            foreach (self::getTableFields() as $field) {
                $this->fields[$field['Field']]  = new nqvDbField($field, get_called_class());
            }
        }
        return $this->fields;
    }

    public function set_data(array $data): nqvWidgets  {
        $dbData = [];
        if(!empty($data['id'])) {
            $stmt = nqvDB::prepare('SELECT * FROM `widgets` WHERE `id` = ?');
            $stmt->bind_param('i',$data['id']);
            $dbData = (array) @nqvDB::parseSelect($stmt)[0];
        } else if(!empty($data['slug'])) {
            $sql = 'SELECT * FROM `widgets` WHERE `slug` = ?';
            $stmt = nqvDB::prepare($sql);
            $stmt->bind_param('s',$data['slug']);
            $dbData = (array) @nqvDB::parseSelect($stmt)[0];
            if(empty($dbData)) {
                $stmt = nqvDB::prepare($sql);
                $slug = nqv::translate($data['slug'],'es','slug',true);
                $stmt->bind_param('s',$slug);
                $dbData = (array) @nqvDB::parseSelect($stmt)[0];
            }
        }
        if(!empty($dbData)) $this->exists = true;
        $fullData = array_merge($dbData,$data);
        foreach($fullData as $k => $v) {
            if(property_exists($this,$k)) $this->$k = $v;
        }
        return $this;
    }

    public function exists() {
        return $this->exists;
    }

    public function save(?array $data): bool  {
        if(!empty($data)) $this->set_data($data);
        try {
            $setters = 'SET `name` = ?,`altname` = ?,`slug` = ?,`description` = ?,`public` = ?, `order` = ?,`created_at` = ?,`created_by` = ?,`modified_at` = ?';
            $types = 'ssssiisis';
            $vars = [$this->name,$this->altname,$this->slug,$this->description,$this->public,$this->order,$this->created_at,$this->created_by,$this->modified_at];
            if(empty($this->id)) {
                $sql = 'INSERT INTO `widgets` ' . $setters;
            } else {
                $sql = 'UPDATE `widgets` ' . $setters . ' WHERE `id` = ?';
                $types .= 'i';
                $vars[] = $this->id;
            }
            $stmt = nqvDB::prepare($sql);
            $stmt->bind_param($types,...$vars);
            $stmt->execute();
            if($stmt->error) throw new Exception($stmt->error);
            if($stmt->insert_id) $this->id = $stmt->insert_id;
            return $stmt->insert_id || empty($stmt->error);
        } catch(Exception $e) {
            _log($e->getMessage(),'widget-saving-error');
            throw new Exception($e->getMessage());
            return false;
        }
    }

    public function get(string $k) {
        if(property_exists($this,$k)) return $this->$k;
        else return null;
    }

    public function get_menu_label(): string {
        if(!empty($this->name)) $output = $this->name;
        else $output = $this->name;
        return empty($output) ? '':nqv::translate($output,'es','widget-label');
    }
}