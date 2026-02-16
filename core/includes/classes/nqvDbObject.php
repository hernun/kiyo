<?php

class nqvDbObject {
    protected int $id = 0;
    protected $tbl_name;
    protected $data = [];
    protected $dbData = [];
    protected $exists = false;
    protected $fields = [];
    protected int $created_by;

    protected $type_id;
    protected ?string $name;
    // protected ?string $slug;

    protected static $main_field = 'id';
    protected static $tableFieldsObjects = [];
    protected static $tableForeignKeys = [];
    protected static string $tablename = '';
    protected static $tablenameLabel = '';
    protected static $get_called_class = null;
    protected static $describes = [];
    protected static $excludeTableFieldsObjects = [];
    protected static $foreignKeys = [];

    protected static $addZeroWorldOnCountriesId = false;
    protected static $table;

    public static function get_called_class() {
        self::$get_called_class = get_called_class();
        return self::$get_called_class;
    }

    public static function setTable() {
        $vars = get_class_vars(self::get_called_class());
        self::$table = new nqvDbTable($vars['tablename']);
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

    public static function getTableFieldsObjects(): array {
        try {
            $o = [];
            $calledClass = self::get_called_class();
            if (!isset(self::$tableFieldsObjects[$calledClass])) {
                foreach (self::getTableFields() as $k => $v) {
                    $o[$v['Field']] = new nqvDbField($v, $calledClass);
                }
                self::$tableFieldsObjects[$calledClass] = $o;
            } else {
                $o = self::$tableFieldsObjects[$calledClass];
            }
           return $o;
        } catch (Exception $e) {
            throw new Exception($e->getMessage() . ' ' . basename(__FILE__) . ' ' . __LINE__);
            return [];
        }
    }

    public static function getDataToSearch() {
        try {
            $output = [];
            $class = new ReflectionClass(get_called_class());
            $tablename = $class->getStaticPropertyValue('tablename');
            $mainFieldName = $class->getStaticPropertyValue('main_field');
            $stmt = nqvDB::prepare('SELECT id, ' . $mainFieldName . ' from ' . $tablename);
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
                $output[] = [
                    'label' => trim($row[$mainFieldName]),
                    'value' => $row['id'],
                    'type' => $tablename
                ];
            }
            return json_encode($output,JSON_HEX_APOS);
        } catch (Exception $e){
            throw new Exception($e->getMessage() . ' ' . basename(__FILE__) . ' ' . __LINE__);
        }
    }

    public function getField($fieldname){
        $fields = $this->getFields();
        return @$fields[$fieldname];
    }

    public function getFields(){
        if(empty($this->fields)) {
            foreach (self::getTableFields() as $field) {
                $this->fields[$field['Field']]  = new nqvDbField($field, get_called_class());
            }
        }
        return $this->fields;
    }

    public function getFieldsRequired(): array {
        $o = [];
        foreach (self::getTableFields() as $field) {
            if ($field['Null'] === 'NO') $o[$field['Field']]  = new nqvDbField($field, get_called_class());
        }
        return $o;
    }

    public function getFieldsRequiredList() {
        foreach($this->getFieldsRequired() as $r) {
            echo $r->getName() . ' ' . $this->getField($r->getName())->getValue();
        }
    }

    public function getFieldsNotRequired(): array {
        $o = [];
        foreach (self::getTableFields() as $field) {
            if ($field['Null'] !== 'NO') $o[$field['Field']]  = new nqvDbField($field, get_called_class());
        }
        return $o;
    }

    public function isField(string $k): bool {
        $fields = self::getTableFields();
        $test = array_filter($fields,function($v) use ($k){
            return ($v['Field'] === $k);
        });
        return !empty($test);
    }

    public function getFieldObject(string $k): ?nqvDbField {
        $fields = self::getTableFields();
        if(!$this->isField($k)) return null;
        else {
            $fields[$k]['Value'] = $this->get($k);
            return new nqvDbField($fields[$k], self::getTableForeignKeys(), get_called_class());
        }
    }

    public static function getForcedType(string $name): ?string {
        return null;
    }

    /*
    *   @param string $name, mixed $val
    *   Obtiene valores adecuados al tipo de campo, especialmente útil para la obtención de listados para campos select
    *   Devuelve vlaores destinados a su implementación en formularios.
    *   No confundir con getHuman[Field].
    */
    public static function getForcedValue(string $name, $val) {
        try {
            $values = null;
            $sprefix = substr($name,0,strpos($name,'_id'));
            if ($name === 'countries_id') {
                $ws = nqv::get('countries');
                $values = @self::parseCrossSelectValues($ws, 'countries');
                $class = new ReflectionClass(get_called_class());
                $addZeroWorldOnCountriesId = $class->getStaticPropertyValue('addZeroWorldOnCountriesId');
                if($addZeroWorldOnCountriesId) array_unshift($values,'Mundo');
            } elseif ($name === 'countries_ids') {
                $ws = nqv::get('countries');
                $vals = array_filter(explode(',',$val));
                $values = [];
                foreach(@self::parseCrossSelectValues($ws, 'countries') as $k => $c) {
                    if(in_array($k,$vals)) $values[] = $c;
                }
            } elseif (nqvDB::isTable($sprefix)){
                $ws = nqv::get($sprefix);
                $values = @self::parseCrossSelectValues($ws, $sprefix);
            }
            return $values;
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function getHumanCountries_id($id){
        $class = new ReflectionClass(get_called_class());
        $addZeroWorldOnCountriesId = $class->getStaticPropertyValue('addZeroWorldOnCountriesId');
        if($$addZeroWorldOnCountriesId && $id === 0) return 'Mundo';
        $country = nqv::get('countries',['id'=> $id]);
        return $country[0]['name'];
    }

    public static function parseCrossSelectValues($values, $rTablename, $sufix_on_label = null) {
        $vs = [];
        $ss = '';
        foreach ($values as $v) {
            $rClassname = 'nqv' . ucfirst($rTablename);
            $ob = new $rClassname($v);
            if($sufix_on_label) {
                if($ob->isField($sufix_on_label)) $ss = ' ('. $ob->get($sufix_on_label) .')';
                else $ss = ' ('. $sufix_on_label .')';
            }
            $vs[$v['id']] = $ob->get_main_field_value() . $ss;
        }
        return $vs;
    }

    public static function parseCrossPredictiveValues($values, $rTablename, $sufix_on_label = null) {
        $vs = [];
        $ss = '';
        foreach ($values as $v) {
            $rClassname = 'nqv' . ucfirst($rTablename);
            $ob = new $rClassname($v);
            if($sufix_on_label) {
                if($ob->isField($sufix_on_label)) $ss = ' ('. $ob->get($sufix_on_label) .')';
                else $ss = ' ('. $sufix_on_label .')';
            }
            $vs[] = ['label' => $ob->get_main_field_value() . $ss, 'value' => $v['id']];
        }
        return $vs;
    }

    public function getUniques() {
        try {
            nqvDB::checkTable($this->getTablename());
            $sql = 'SHOW INDEX from '.$this->getTablename();
            $stmt = nqvDB::prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $uniques = [];
            while ($row = $result->fetch_assoc()) {
                if($row['Key_name'] === 'UQI') $uniques[] = $this->getFieldObject($row['Column_name']);
            }
            return $uniques;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(),$e->getCode());
        }
    }

    public function getUniquesList ($withValues = false) {
        $output = [];
        foreach($this->getUniques() as $u) {
            $o = $u->getName();
            if($withValues) $o .= ' = <span class="unique-value">' . $u->getValue() . '</span>';
            $output[] = $o;
        }
        return implode(', ',$output);
    }

    public static function getTableForeignKeys(?string $k = null) {
        $calledClass = get_called_class();
        $fks = [];
        if (!isset(self::$tableForeignKeys[$calledClass])) {
            $vars = get_class_vars($calledClass);
            $sql = 'SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME';
            $sql .= ' FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
            $sql .= ' WHERE REFERENCED_TABLE_SCHEMA = ?';
            $sql .= ' AND TABLE_NAME = ?';
            $stmt = nqvDB::prepare($sql);
            $dbname = DB_NAME;
            $stmt->bind_param('ss',$dbname,$vars['tablename']);
            $d = nqvDB::parseSelect($stmt);
            foreach ($d as $fk) {
                if(empty(self::$foreignKeys[$fk['REFERENCED_TABLE_NAME']])) {
                    $values = nqv::get($fk['REFERENCED_TABLE_NAME']);
                    $crossObject = 'nqv' . ucfirst($fk['REFERENCED_TABLE_NAME']);
                    $class = new ReflectionClass($crossObject);
                    $main_field = $class->getStaticPropertyValue('main_field');
                    foreach($values as $value) $fks[$fk['COLUMN_NAME']][] = $value[$main_field];
                    self::$foreignKeys[$fk['REFERENCED_TABLE_NAME']] = $fks[$fk['COLUMN_NAME']];
                } else {
                    $fks[$fk['COLUMN_NAME']] = self::$foreignKeys[$fk['REFERENCED_TABLE_NAME']];
                }
            }
            self::$tableForeignKeys[$calledClass] = $fks;
        } else {
            $fks = self::$tableForeignKeys[$calledClass];
        }

        #_log($fks,'test-db');
        if ($k) return $fks[$k];
        else return $fks;
    }

    public function getInputs() {
        $inputs = [];
        foreach (self::getTableFieldsObjects() as $k => $f) {
            $inputs[$k] = $f->getHtmlInput();
        }
        return $inputs;
    }

    public function __construct($data = 0,string $tbl_name = '') {
        try {
            $this->tbl_name = empty($tbl_name) ? self::getTablename():$tbl_name;
            if (is_numeric($data)) $data = ['id' => $data];
            elseif (is_string($data)) $data = [self::$main_field => $data];
            if (!empty($data)) $this->parseData($data);
            else $this->data = [];
        } catch(Exception $e) {
            if(DEBUG) my_print_more($this->tbl_name . ': ' . $e->getMessage() . ' ' . basename($e->getFile()) . ' ' . $e->getLine());
            throw new Exception($e->getMessage());
        }
    }

    protected function getDbData() {
        return $this->dbData;
    }

    protected function parseData($data = []): void {
        try {
            $c = get_called_class();
            if (!empty($data['id'])) $this->dbData = @array_values((array) @nqv::get($this->tbl_name,['id' => intval($data['id'])]))[0];
            else $this->dbData = @array_values((array) @nqv::get($this->tbl_name, $data))[0];
            if (!empty($this->dbData)) $this->exists = true;
            foreach ((array) $this->dbData as $k => $v) {
                if(property_exists($this,$k)) {
                    $reflection = new ReflectionProperty(get_called_class(), $k);
                    $type = $reflection->getType();
                    #settype($v,$type->getName());
                    if($type->getName() === 'array' && isValidJson($v)) $this->$k = json_decode($v,true);
                    else $this->$k = $v;
                }
            }
            $this->data = $this->dbData;
            $this->setData($data);
        } catch(Exception $e) {
            if(DEBUG) throw new Exception($e->getMessage() . ' ' . basename($e->getFile()) . ' ' . $e->getLine());
            else throw new Exception($e->getMessage());
        }
    }

    public function setData($data) {
        foreach ((array) $data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->data[$k] = $v;
                $this->$k = $v;
            }
            unset($data[$k]);
        }
    }

    public function get_object_vars() {
        return get_object_vars($this);
    }

    protected function parseDiffPreSave($returnKeys = false) {
        $db = $this->getDbData();
        $diff = [];
        foreach($this->getData() as $k => $v) {
            if($db[$k] != $v) $diff[$k] = $v;
        }
        if($returnKeys) return array_keys($diff);
        else return $diff;
    }

    public function save(): ?int {
        try {
            $class = get_called_class();

            $this->prepareSlug();
            $this->prepareCreatedBy();
            $this->sanitizeFields($class);

            $data = get_object_vars($this);
            $output = nqvDB::save($this->tbl_name, $data);

            if ($output && empty($this->id)) {
                $this->id = $output;
                $this->exists = true;
            }

            return $output;

        } catch (Exception $e) {
            throw new Exception(
                basename($e->getFile()) . ' ' . $e->getLine() . ' ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    private function prepareSlug(): void {
        if (property_exists($this, 'slug') && empty($this->slug)) {
            $this->create_slug();
        }
    }

    private function prepareCreatedBy(): void {
        if (property_exists($this, 'created_by') && empty($this->created_by)) {
            $current_user = nqv::getSession()->getUserId();
            $this->created_by = $current_user ? $current_user:0;
        }
    }

    private function sanitizeFields(string $class): void {
        foreach ($class::getTableFieldsObjects() as $k => $field) {
            if ($k === 'id') continue;
            if ($k === 'created_by' && empty($this->$k)) {
                if($this->$k !== 0) $this->$k = nqv::getCurrentUserId();
                continue;
            } if (!$field->canBeNull() && empty($this->$k)) {
                if ($field->getDefault()) {
                    unset($this->$k);
                } else {
                    throw new Exception("Campo '$k' es obligatorio y no tiene valor");
                }
            }
        }
    }


    public function set($k, $v): void {
        if (property_exists($this, $k)) {
            $this->$k = $v;
            $this->data[$k] = $v;
        }
    }

    public function unset($k) {
        if (property_exists($this, $k)) {
            $field = $this->getField($k);
            if($field->canBeNull()) $this->set($k,null);
            elseif($field->getDefault()) $this->set($k,$field->getDefault());
            else throw new Exception($k . ' no puede ser nulo y no tiene valor por defecto.');
            $this->save();
        } else {
            throw new Exception($k . ' no existe en ' . $this->getTablename());
        }
    }

    public function delete(): bool {
        try {
            $d = nqvDB::delete($this->tbl_name, $this->get_id());
            if(!$d) throw new Exception('No se pudo borrar ' . $this->tbl_name .' '. $this->get_id());
            $output = true;
        } catch (Exception $e) {
            nqvNotifications::add(get_called_class() . ': ' . $e->getMessage(),'error');
            $output = false;
        }
        return $output;
    }

    public function get_id(): int {
        return intval($this->id);
    }

    public function get($k) {
        $meth = 'get_' . strtolower($k);
        if (method_exists($this, $meth)) return $this->$meth();
        else return $this->$k;
    }

    public function exists(): bool {
        return $this->exists;
    }

    public function getData($k = null) {
        if(empty($k)) return $this->data;
        else return @$this->data[$k];
    }

    public function cleanDir($d) {
        try {
            if (is_dir($d)) rrmdir($d);
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function checkFields(array $input) {
        $fs = self::getTableFieldsObjects();
        foreach ($fs as $k => $v) {
            $v->setValue(@$input[$k]);
            if (!$v->checkValue()) return false;
        }
        return true;
    }

    public function getTablename() {
        $class = new ReflectionClass(get_called_class());
        return $class->getStaticPropertyValue('tablename');;
    }

    public function getTablenameLabel() {
        $class = new ReflectionClass(get_called_class());
        return $class->getStaticPropertyValue('tablenameLabel');
    }

    public static function getStaticTablenameLabel() {
        $class = new ReflectionClass(get_called_class());
        $tablenameLabel = self::getTablenameLabel();
        return $tablenameLabel ? $tablenameLabel:self::getTablename();
    }

    public static function getStaticMainField(){
        $class = new ReflectionClass(get_called_class());
        return $class->getStaticPropertyValue('main_field');
    }

    public function get_main_field() {
        return self::getStaticMainField();
    }

    public function getMainField() {
        return $this->getFieldObject($this->get_main_field());
    }

    public function get_main_field_value() {
        return $this->getMainField()->getValue();
    }

    public function getApiDescription(): array {
        return ['id' => $this->get('id'), $this->get_main_field() => $this->get($this->get_main_field())];
    }

    public static function getLabel($k){
        return nqv::translate($k,'ES','label');
    }

    protected function get_slug_sufix(): string {
        return '';
    }

    private function removeAccents(string $string): string {
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
            if ($converted !== false) {
                return $converted;
            }
        }

        // Fallback manual (español + básico)
        return strtr($string, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u',
            'ñ'=>'n','Ñ'=>'n'
        ]);
    }

    public function create_slug(?string $string = ''): void {
        try {
            $name = empty($string) ? $this->get_main_field_value() : $string;

            if (empty($name) && !empty($_POST[$this->get_main_field()])) {
                $name = $_POST[$this->get_main_field()];
            }

            if (empty($name)) {
                throw new Exception(
                    'Error: el elemento ' . $this->get_called_class() .
                    ' no tiene ' . $this->get_main_field()
                );
            }

            $name = $this->removeAccents($name);

            $baseSlug = cleanString($name . $this->get_slug_sufix());
            $slug = $baseSlug;

            $i = 0;
            while ($this->checkSlug()) {
                $slug = $baseSlug . '-' . (++$i);
                $this->set_slug($slug);
            }

            $this->set_slug($slug);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function set_slug(string $slug): void {
        $k = 'slug';
        $this->$k = $slug;
    }
    
    public function checkSlug(): bool {
        $classname = $this->get_called_class();
        $k = 'slug';
        $obj = new $classname(['slug' => $this->$k]);
        if ($obj->get('id') === $this->get('id')) return false;
        else return $obj->exists();
    }

    public function save_slug(?string $slug = null): int {
        if (empty($slug)) $this->create_slug();
        else $this->set_slug($slug);
        return $this->save();
    }

    public function get_countries() {
        $field = $this->getFieldObject('countries_id');
        return implode(',',self::getForcedValue('countries_ids',$field->getValue()));
    }

    public static function parseSearchConditions($conditions) {
        return $conditions;
    }

}
