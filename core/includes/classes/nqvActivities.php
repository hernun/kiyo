<?php
class nqvActivities {
    protected int $id;
    protected $name;
    protected $email;
    protected $phone;
    protected $holders_id;
    protected $services;
    protected ?string $description;
    protected $instagram;
    protected $facebook;
    protected $spotify;
    protected $youtube;
    protected $bandcamp;
    protected $website;
    protected $cities_id;
    protected $status;
    protected $stand;
    protected $stand_responsible_name;
    protected $stand_responsible_phone;
    protected $stand_requirements;
    protected $categories_id;
    protected $element_token;
    protected $tags;
    protected ?string $slug;
    protected string $tablename = 'activities';

    // 2025-03-25 14:55:55
    protected ?string $created_at;
    protected ?string $modified_at;
    protected int $created_by;

    protected $exists;
    
    public function __construct(?array $data) {
        $this->created_by = nqv::getCurrentUserId();
        if($data) $this->set_data($data);
    }

    public static function getHumanTags($value) {
        if(empty($value)) return '';
        $ids = explode(',',$value);
        foreach(array_unique(array_filter($ids)) as $id) {
            $tag = new nqvTags($id);
            $output[] = $tag->get('value');
        }
        return implode(',',$output);
    }

    public function set_data(array $data): nqvActivities  {
        try {
            $dbData = [];
            $sql = null;
            $types = '';
            $vars = [];
            if(!empty($data['id'])) {
                $sql = 'SELECT * FROM `'.$this->tablename .'` WHERE `id` = ?';
                $types .= 'i';
                $vars[] = $data['id'];
            } elseif(!empty($data['slug'])) {
                $sql = 'SELECT * FROM `'.$this->tablename .'` WHERE `slug` = ?';
                $types .= 's';
                $vars[] = $data['slug'];
            }
            if($sql) {
                if(!empty($data['status'])) {
                    $sql .= ' AND `status` = ?';
                    $types .= 's';
                    $vars[] = $data['status'];
                }
                $stmt = nqvDB::prepare($sql);
                $stmt->bind_param($types,...$vars);
                $dbData = (array) @nqvDB::parseSelect($stmt)[0];
            }
            if(!empty($dbData)) $this->exists = true;
            $fullData = array_merge($dbData,$data);
            foreach($fullData as $k => $v) {
                if(property_exists($this,$k)) $this->$k = $v;
            }
        } catch (Exception $e) {
            _log([$sql,$e->getMessage()],'activity-set-data-error');
        }
        return $this;
    }

    public static function getMainSqlFieldProperty() {
        return 'name';
    }

    public function getMainPhoto() {
        $mainImage = nqvMainImages::getByElementId($this->tablename,$this->get('id'));
        $src = $mainImage->getSrc();
        if(empty($src)) {
            if($this->get('image')) $output = $this->get('image');
            elseif($this->get('photos')) $output = $this->get('photos');
            elseif($this->get('services_photos')) $output = $this->get('services_photos');
            elseif($this->get('images')) $output = $this->get('images');
            $output = implode(',',array_filter(explode(';',$output)));
            $output = explode(',',$output);
            return @array_filter((array) $output)[0];
        } else {
            return $src;
        }
    }

    public function exists() {
        return $this->exists;
    }

    public function set_slug(?string $sufix) {
        // Convertir a minúsculas
        $this->slug = $sufix ? strtolower($this->name . '-' . $sufix):strtolower($this->name);
        
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

        $stmt = nqvDB::prepare('SELECT * FROM `' . $this->tablename . '` WHERE `slug` = ?');
        $stmt->bind_param('s',$this->slug);
        $result = nqvDb::parseSelect($stmt);
        if(!empty($result)) $this->set_slug((string) intval($sufix) + 1);
    
        return $this->slug;        
    }

    public function getCategory() {
        return new nqvCategories(['id' => $this->get('categories_id')]);
    }

    public function save(?array $data): bool  {
        if(!empty($data)) $this->set_data($data);
        if(empty($this->slug)) $this->set_slug(null);
        if(empty($this->created_at)) $this->created_at = date('Y/m/d h:i:s');
        try {
            $setters = 'SET `name` = ?,`email` = ?,`slug` = ?,`phone` = ?,`holders_id` = ?,`services` = ?,`description` = ?,`instagram` = ?,`facebook` = ?,`spotify` = ?,`youtube` = ?,`bandcamp` = ?,`website` = ?,`cities_id` = ?,`status` = ?,`tags` = ?,`stand` = ?,`stand_responsible_name` = ?,`stand_responsible_phone` = ?,`stand_requirements` = ?,`categories_id` = ?,`created_at` = ?,`modified_at` = ?,`created_by` = ?';
            $types = 'ssssissssssssississsissi';
            $vars = [$this->name,$this->email,$this->slug,$this->phone,$this->holders_id,$this->services,$this->description,$this->instagram,$this->facebook,$this->spotify,$this->youtube,$this->bandcamp,$this->website,$this->cities_id,$this->status,$this->tags,$this->stand,$this->stand_responsible_name,$this->stand_responsible_phone,$this->stand_requirements,$this->categories_id,$this->created_at,$this->modified_at,$this->created_by];
            if(empty($this->id)) {
                $sql = 'INSERT INTO `'.$this->tablename .'` ' . $setters;
            } else {
                $sql = 'UPDATE `'.$this->tablename .'` ' . $setters . ' WHERE `id` = ?';
                $types .= 'i';
                $vars[] = $this->id;
            }
            #_log([$sql,$types,$vars,strlen($types),count($vars)]);
            $stmt = nqvDB::prepare($sql);
            $stmt->bind_param($types,...$vars);
            $stmt->execute();
            if($stmt->error) throw new Exception($stmt->error);
            if($stmt->insert_id) $this->id = $stmt->insert_id;
            return $stmt->insert_id || empty($stmt->error);
        } catch(Exception $e) {
            _log($e->getMessage(),'activities-saving-error');
            throw new Exception($e->getMessage());
            return false;
        }
    }

    public function get(string $k) {
        if(property_exists($this,$k)) return $this->$k;
        else return null;
    }

    public function addCategory(int $category_id): nqvActivities {
        $categories = explode(',',(string) $this->categories_id);
        $categories[] = $category_id;
        $this->categories_id = implode(',',array_unique(array_filter($categories)));
        return $this;
    }

    public function getSocial() {
        $output = [];
        if($this->get('instagram')) $output['fa-brands fa-instagram'] = $this->get('instagram');
        if($this->get('facebook')) $output['fa-brands fa-facebook'] = $this->get('facebook');
        if($this->get('youtube')) $output['fa-brands fa-youtube'] = $this->get('youtube');
        if($this->get('spotify')) $output['fa-brands fa-spotify'] = $this->get('spotify');
        if($this->get('bandcamp')) $output['fa-brands fa-bandcamp'] = $this->get('bandcamp');
        if($this->get('website')) $output['fa-solid fa-globe'] = $this->get('website');
        return $output;
    }

    public function set(string $varname, $value): nqvActivities {
        if(property_exists($this,$varname)) $this->$varname = $value;
        return $this;
    }

    public function isPublic() {
        return $this->status === 'active';
    }

    public function delete() {
        try {
            $stmt = nqvDB::prepare('DELETE FROM `images` WHERE `tablename` = "activities" AND `element_id` = ?');
            $stmt->bind_param('i',$this->id);
            $stmt->execute();
            $stmt = nqvDB::prepare('DELETE FROM `mainimages` WHERE `tablename` = "activities" AND `element_id` = ?');
            $stmt->bind_param('i',$this->id);
            $stmt->execute();
            $images_dir = UPLOADS_PATH . 'images/activities/' . $this->id;
            if(is_dir($images_dir)) rrmdir($images_dir);
            $stmt = nqvDB::prepare('DELETE FROM `activities` WHERE `id` = ?');
            $stmt->bind_param('i',$this->id);
            $stmt->execute();
            return empty($stmt->error);
        } catch(Exception $e) {
            _log($e->getMessage(),'activity-delete-error');
            nqvNotifications::add('Hubo un error y la eliminación de la actividad no pudo completarse','error');
        }
    }
}