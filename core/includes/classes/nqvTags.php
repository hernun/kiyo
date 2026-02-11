<?php
class nqvTags {
    protected int $id;
    protected $value;
    protected ?string $created_at;
    protected int $created_by;
    protected $exists = false;

    public function __construct($input) {
        if(is_numeric($input)) $this->parseData(['id'=>$input]);
        elseif(is_string($input)) $this->parseData(['value'=>$input]);
        elseif(is_array($input)) $this->parseData($input);
        else throw new Exception('El input para Tags es incorrecto');
    }

    public static function getHumanValue($val) {
        return $val;
    }

    protected function parseData($data = []): void {
        try {
            if (!empty($data['id'])) $dbData = @array_values((array) @nqv::get('tags',['id' => intval($data['id'])]));
            else $dbData = @array_values((array) @nqv::get('tags', $data));
            if (!empty($dbData[0])) {
                $this->exists = true;
                foreach ($dbData[0] as $k => $v) $this->$k = $v;
            }
        } catch(Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function get($k) {
        $meth = 'get_' . strtolower($k);
        if (method_exists($this, $meth)) return $this->$meth();
        else return property_exists($this,$k) ? $this->$k:null;
    }

    public function exists() {
        return $this->exists;
    }
}