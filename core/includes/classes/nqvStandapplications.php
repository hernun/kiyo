<?php
class nqvStandapplications {
    protected int $id;
    protected $holders_id;
    protected $activities_id;
    protected $requirements;
    protected $status;
    protected $responsible_dni;
    protected $responsible_name;
    protected $responsible_email;
    protected $responsible_phone;
    protected $responsible_birthdate;
    protected int $created_by;
    protected ?string $created_at;
    protected ?string $modified_at;

    protected $exists;
    protected string $tablename = 'standapplications';
    protected static $main_field = ['activities_id','holders_id'];

    public function __construct(?array $data) {
        if($data) $this->set_data($data);
    }

    public static function getMainSqlFieldProperty() {
        return self::$main_field;
    }

    public function set(string $varname, $value): nqvStandapplications {
        if(property_exists($this,$varname)) $this->$varname = $value;
        return $this;
    }

    public static function getHumanActivities_id(int $id): string {
        $activity = new nqvActivities(['id' => $id]);
        return $activity->exists() ? $activity->get('name'):'Actividad eliminada';
    }

    public static function getHumanCreated_by(int $id): string {
        return self::getHumanHolders_id($id);
    }

    public static function getHumanHolders_id(int $id): string {
        $holder = new nqvHolders(['id' => $id]);
        return $holder->exists() ? ucwords($holder->get_fullname()) . ' (' . $holder->get('id') . ')':'';
    }

    public static function getActivities_id(int $id): string {
        $activity = new nqvActivities(['id' => $id]);
        return $activity->exists() ? ucwords($activity->get('name')) . ' (' . $activity->get('id') . ')':'';
    }

    public function set_data(array $data): nqvStandapplications  {
        $dbData = [];
        if(!empty($data['id'])) {
            $stmt = nqvDB::prepare('SELECT * FROM `'. $this->tablename .'` WHERE `id` = ?');
            $stmt->bind_param('i',$data['id']);
            $dbData = (array) @nqvDB::parseSelect($stmt)[0];
        }
        if(!empty($dbData)) $this->exists = true;
        else {
            $this->created_by = nqv::getCurrentUserId();
            $this->created_at = date('Y-m-d h:i:s');
        }
        $fullData = array_merge($dbData,$data);
        foreach($fullData as $k => $v) {
            if(property_exists($this,$k)) {
                $this->$k = $v;
                #_log([$k,$this->$k]);
            }
        }
        return $this;
    }

    public function exists() {
        return $this->exists;
    }

    public function get(string $k) {
        if(property_exists($this,$k)) return $this->$k;
        else return null;
    }

    public function isPublic() {
        return $this->status === 'active';
    }

    public function delete() {
        try {
            $stmt = nqvDB::prepare('DELETE FROM `standapplications` WHERE `id` = ?');
            $stmt->bind_param('i',$this->id);
            $stmt->execute();
            return empty($stmt->error);
        } catch(Exception $e) {
            _log($e->getMessage(),'stand-delete-error');
            nqvNotifications::add('Hubo un error y la eliminación de la solicitud no pudo completarse','error');
        }
    }

    public function save(?array $data): bool  {
        if(!empty($data)) $this->set_data($data);
        try {
            if(empty($this->created_at)) $this->created_at = date('Y-m-d h:i:s');
            if(empty($this->modified_at)) $this->modified_at = date('Y-m-d h:i:s');
            $setters = 'SET `holders_id` = ?,`activities_id` = ?,`requirements` = ?,`status` = ?,`responsible_dni` = ?,`responsible_email` = ?,`responsible_name` = ?,`responsible_phone` = ?,`responsible_birthdate` = ?,`created_at` = ?,`modified_at` = ?,`created_by` = ?';
            $types = 'iisssssssssi';
            $vars = [$this->holders_id,$this->activities_id,$this->requirements,$this->status,$this->responsible_dni,$this->responsible_email,$this->responsible_name,$this->responsible_phone,$this->responsible_birthdate,$this->created_at,$this->modified_at,$this->created_by];
            if(empty($this->id)) {
                $sql = 'INSERT INTO `'.$this->tablename .'` ' . $setters;
            } else {
                $sql = 'UPDATE `'.$this->tablename .'` ' . $setters . ' WHERE `id` = ?';
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
            _log($e->getMessage(),'stands-saving-error');
            throw new Exception($e->getMessage());
            return false;
        }
    }

    public static function getByHolderId($holder_id) {
        $stmt = nqvDB::prepare('SELECT * FROM `standapplications` WHERE `holders_id` = ?');
        $stmt->bind_param('i',$holder_id);
        return nqvDB::parseSelect($stmt);
    }
}