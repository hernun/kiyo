<?php
class nqvHolders {
    protected int $id;
    protected $email;
    protected $name;
    protected $lastname;
    protected ?string $slug;
    protected $password;
    protected $birthdate;
    protected $phone;
    protected ?string $description;
    protected $status;
    protected $token;
    protected $residence_city;
    // 2025-03-25 14:55:55
    protected ?string $created_at;
    protected ?string $modified_at;
    protected int $created_by;

    protected $exists;
    protected string $tablename = 'holders';
    
    public function __construct(?array $data) {
        if($data) $this->set_data($data);
    }

    public function set_data(array $data): nqvHolders  {
        $dbData = [];
        if(!empty($data['id'])) {
            $stmt = nqvDB::prepare('SELECT * FROM `'.$this->tablename .'` WHERE `id` = ?');
            $stmt->bind_param('i',$data['id']);
            $dbData = (array) @nqvDB::parseSelect($stmt)[0];
        } else if(!empty($data['email'])) {
            $stmt = nqvDB::prepare('SELECT * FROM `'.$this->tablename .'` WHERE `email` = ?');
            $stmt->bind_param('s',$data['email']);
            $dbData = (array) @nqvDB::parseSelect($stmt)[0];
        } else if(!empty($data['token'])) {
            $stmt = nqvDB::prepare('SELECT * FROM `'.$this->tablename .'` WHERE `token` = ?');
            $stmt->bind_param('s',$data['token']);
            $dbData = (array) @nqvDB::parseSelect($stmt)[0];
        }
        if(!empty($dbData)) $this->exists = true;
        else $this->created_by = nqv::getCurrentUserId();
        $fullData = array_merge($dbData,$data);
        foreach($fullData as $k => $v) {
            if(property_exists($this,$k)) $this->$k = $v;
        }
        return $this;
    }

    public function get_fullname() {
        return ucwords($this->name . ' ' . $this->lastname);
    }

    public function exists() {
        return $this->exists;
    }

    public function set_slug(?string $sufix) {
        // Convertir a minúsculas
        $this->slug = strtolower($this->name . '-' . $this->lastname . '-' . $sufix);
        
        // Reemplazar caracteres especiales (acentos, ñ, etc.)
        $this->slug = preg_replace(
            array('/á/', '/é/', '/í/', '/ó/', '/ú/', '/ñ/', '/Á/', '/É/', '/Í/', '/Ó/', '/Ú/', '/Ñ/'),
            array('a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'),
            $this->slug
        );
    
        // Eliminar todos los caracteres no alfanuméricos (excepto los guiones)
        $this->slug = preg_replace('/[^a-z0-9-]/', '-', $this->slug);
    
        // Reemplazar múltiples guiones por uno solo
        $this->slug = preg_replace('/-+/', '-', $this->slug);
    
        // Eliminar guiones al principio y al final
        $this->slug = trim($this->slug, '-');
        $stmt = nqvDB::prepare('SELECT * FROM `'.$this->tablename .'` WHERE `slug` = ?');
        $slug = $sufix ? substr($this->slug,-strlen($sufix)):$this->slug;
        $stmt->bind_param('s',$slug);
        $result = nqvDb::parseSelect($stmt);
        if(!empty($result)) $this->set_slug((string) intval($sufix) + 1);
    
        return $this->slug;        
    }
        
    public function isLogged(): bool {
        $sessUid = nqv::getSession()->getUserId();
        return !empty($sessUid) && $this->id === $sessUid;
    }
    
    private function setPasswordValue($pass): string {
        return set_password_value($pass);
    }
    
    public function set_password(string $pass): void {
        $this->password = $pass;
    }
        
    public function check_password($pass) {
        return check_pass($pass, $this->password);
    }

    public function save(?array $data): bool  {
        if(!empty($data)) $this->set_data($data);
        if(empty($this->slug)) $this->set_slug(null);
        if(!is_encrypted($this->password)) $this->set_password($this->setPasswordValue($this->password));
        try {
            $setters = 'SET `email` = ?,`name` = ?,`lastname` = ?,`slug` = ?,`password` = ?,`birthdate` = ?,`phone` = ?,`description` = ?,`status` = ?,`token` = ?,`residence_city` = ?,`created_at` = ?,`modified_at` = ?,`created_by` = ?';
            $types = 'sssssssssssssi';
            $vars = [$this->email,$this->name,$this->lastname,$this->slug,$this->password,$this->birthdate,$this->phone,$this->description,$this->status,$this->token,$this->residence_city,$this->created_at,$this->modified_at,$this->created_by];
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
            $ok = $stmt->insert_id || empty($stmt->error);
            if($ok) $this->exists = true;
            return $ok;
        } catch(Exception $e) {
            _log($e->getMessage(),'holders-saving-error');
            throw new Exception($e->getMessage());
            return false;
        }
    }

    public function get(string $k) {
        if(property_exists($this,$k)) return $this->$k;
        else return null;
    }

    public function set(string $k, $v) {
        if(property_exists($this,$k)) $this->$k = $v;
        else return nqvNotifications::add('La propiedad ' . $k .' no existe','error');
    }

    public function test() {
        return get_object_vars($this);
    }

    public static function getMainSqlFieldProperty() {
        return 'CONCAT(name," ",lastname)';
    }

    public function getData(): array {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'birthdate' => $this->birthdate,
            'phone' => $this->phone,
            'description' => $this->description,
            'residence_city' => $this->residence_city,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
        ];
    }

    public function sendMail(string $template, string $subject, array $ops) {
        $options = array_merge([
            'template' => $template,
            'to' => [['address' => $this->email,'name' => $this->get_fullname()]],
            'fronturl' => URL,
            'subject' => $subject
        ],$ops);
        try {
            if(!$this->exists()) throw new Exception('El usuario ' . $this->email . ' no existe.',403);
            $mail = new nqvMail($options);
            return $mail->send();
        } catch(Exception $e) {
            throw new Exception($e->getMessage(),$e->getCode());
            return null;
        }
    }

    public static function getNewToken() {
        return bin2hex(random_bytes(32));
    }

    public function sendResetPasswordConfirmMail() {
        if(!$this->save(['token' => self::getNewToken()])) return false;
        return $this->sendMail('reset-password-confirm','Confirmar correo de Resonar',['token' => $this->token]);
    }

    public static function getByToken(string $token): ?nqvHolders {
        return new nqvHolders(['token'=>$token]);
    }

    public function isActive() {
        return $this->get('status') === 'active';
    }

    public function delete() {
        try {
            $stmt = nqvDB::prepare('DELETE FROM `images` WHERE `tablename` = "holders" AND `element_id` = ?');
            $stmt->bind_param('i',$this->id);
            $stmt->execute();
            $stmt = nqvDB::prepare('DELETE FROM `mainimages` WHERE `tablename` = "holders" AND `element_id` = ?');
            $stmt->bind_param('i',$this->id);
            $stmt->execute();
            $images_dir = UPLOADS_PATH . 'images/holders/' . $this->id;
            if(is_dir($images_dir)) rrmdir($images_dir);
            $stmt = nqvDB::prepare('DELETE FROM `holders` WHERE `id` = ?');
            $stmt->bind_param('i',$this->id);
            $stmt->execute();
            return empty($stmt->error);
        } catch(Exception $e) {
            _log($e->getMessage(),'holder-delete-error');
            nqvNotifications::add('Hubo un error y la eliminación del usuario no pudo completarse','error');
        }
    }
}