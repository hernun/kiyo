<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class nqv {
    protected static $session;
    protected static $vars = [];
    protected static $section = 'front';
    protected static $referer = '';
    protected static $config;
    public static $meses = ['enero', 'febrero','marzo', 'abril','mayo', 'junio','julio', 'agosto','septiembre', 'octubre','noviembre', 'diciembre'];
    public static $months = ['january', 'february','march', 'april','may', 'june','july', 'august','september', 'october','november', 'december'];

    public static function sendMail(string $email_to, string $subject, array $content, array $sender = [], $options = []) {
        if(empty($sender)) $sender = [ADMIN_EMAIL_SENDER,APP_NAME];
        $phpMailer = new PHPMailer(true);
        $email_to = filter_var($email_to,FILTER_VALIDATE_EMAIL);
        try {
            $phpMailer->CharSet = 'utf-8';
            $phpMailer->setFrom($sender[0], $sender[1]);
            $phpMailer->addAddress($email_to);
            $phpMailer->isHTML(true); 
            $phpMailer->Subject = $subject;
            $phpMailer->Body = $content['Body'];
            $phpMailer->AltBody = empty($content['AltBody']) ? strip_tags(br2nl($content['Body'])):$content['AltBody'];
            if(!empty($options['reply-to'])) {
                $reply_to = filter_var($options['reply-to'],FILTER_VALIDATE_EMAIL);
                if(!$reply_to) throw new Exception('Replay To debe ser una dirección válida');
                $phpMailer->addReplyTo($reply_to, $reply_to);
            }
            $phpMailer->send();
            return true;
        } catch(Exception $e) {
            _log($phpMailer->ErrorInfo,'mail-error');
            return false;
        }
    }

    public static function translate(string $str, string $lang = 'es', ?string $context = '', bool $inverse = false): string  {
        include($_SERVER['DOCUMENT_ROOT'] . '/core/translations/' . strtolower($lang) . '.php');
        if($inverse) {
            $output = @array_flip($translations)[$str];
            if(empty($output)) $output = $str;
            elseif(!empty($context)) $output = str_replace('-'.$context,'',$output);
            return empty($output) ? $str:$output;
        }
        if (isset($translations[strtolower($str) . '-' . strtolower($context)])) return @$translations[strtolower($str) . '-' . strtolower($context)];
        if (isset($translations[strtolower($str)])) return @$translations[strtolower($str)];
        else return $str;
    }

    public static function cleanView() {
        while (ob_get_level() >= 1) ob_end_clean();
    }

    public static function back(?string $default = '/',$queryvars = [],$strict = false): void {
        $vars = [];
        $referer = self::getReferer();
        $r = empty($referer) ? $default : $referer;
        unset($_SESSION['referers'][0]);
        if(!$strict) {
            foreach($queryvars as $k) $vars[$k] = @$_GET[$k];
        } else {
            $vars = $queryvars;
        }
        $vars = array_filter($vars);
        $q = empty($vars) ? '' : '?' . http_build_query($vars);
        _log([$r,$q]);
        self::redirect($r.$q);
    }

    public static function reload() {
        header('location:');
        exit;
    }

    public static function redirect(?string $uri): void {
        static $count;
        if(strpos($uri,'/') !== 0) $uri = '/'.$uri;
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        $current = $parsed['path'];
        if($current === '/index.php') $current = '/';
        if($current === $uri && $count) return;
        else {
            $count ++;
            header('location:' . $uri);
            exit;
        }
    }

    public static function getCurrentUri() {
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        return url($parsed['path']);
    }

    public static function getSession(): nqvSession {
        if(empty(self::$session)) self::$session = nqvSession::getInstance();
        return self::$session;
    }

    public static function checkDB() {
        if(!defined('DB_HOST') || empty(DB_HOST)) {
            nqvNotifications::add('La base de datos no está definida','error');
            return false;
        } elseif(!defined('DB_USER') || empty(DB_USER)) {
            nqvNotifications::add('No hay usuario definido para la base de datos','error');
            return false;
        } elseif(!defined('DB_PASS') || empty(DB_PASS)) {
            nqvNotifications::add('No hay contraseña definida para el usuario de la base de datos','error');
            return false;
        } elseif(!defined('DB_NAME') || empty(DB_NAME)) {
            nqvNotifications::add('Falta el nombre de la base de datos','error');
            return false;
        }
        try {
            $conection = nqvDB::getConnection();
        } catch(Exception $e) {
            nqvNotifications::add($e->getMessage(),'error');
        }
    }

    public static function addVar($key,$index = null): array {
        if(!is_null($index)) {
            array_splice( self::$vars, $index, 0, [$key] );
        } else {
            self::$vars[] = $key;
        }
        return self::$vars;
    }

    public static function getVars($index = null) {
        if (empty(self::$vars)) self::parseVars();
        if (!is_null($index)) return @self::$vars[$index];
        else return self::$vars;
    }

    public static function setAdmin() {
        self::$section = 'admin';
    }

    public static function getSection() {
        return self::$section;
    }

    public static function parseVars(?array $argv = []): void {
        $parsed = parse_url($_SERVER['REQUEST_URI']);
        $current = @$parsed['path'];
        $vars = (array) array_values(array_filter(explode('/',(string) @$current)));
        if (in_array('logout', $vars)) self::getSession()->destroy();
        elseif(@$vars[0] === 'ajax') {
            setIsAjax(true);
            $funcname = 'ajax_'.$vars[1];
            $test = [$funcname];
            if(function_exists($funcname)) echo call_user_func($funcname,...array_slice($vars,2));
            else echo json_encode(array_merge($test,$vars));
            exit;
        } else {
            if(@$vars[0] === 'admin') {
                array_shift($vars);
                self::setAdmin();
                if(!isTemplate((string)@$vars[0]) && nqvDB::isTable((string) @$vars[0])) array_unshift($vars,'database');
            }
            self::$vars = self::$vars + $vars;
        }
    }

    public static function get(string $tablename, array $fields = [], array $order = [], array $limit = []) {
        if(!nqvDB::isTable($tablename)) return [];
        $sql = 'SELECT * FROM ' . $tablename;
        $where = [];
        $orderby = [];
        $types = '';
        $vars = [];
        foreach($fields as $k => $v) {
            $or = explode('%',$k);
            if(!empty($or[1])) {
                if($or[1] === 'not') $where[] = $or[0] . ' <> ?';
                elseif($or[1] === 'or') $whereOr[] = $or[0] . ' = ?';
                elseif($or[1] === 'ornot') $whereOr[] = $or[0] . ' <> ?';
                elseif($or[1] === 'in') $whereOr[] = ' FIND_IN_SET(' . $or[0] . ',?)';
                $types .= 's';
                $vars[] = $v;
            } else {
                $where[] = $or[0] . ' = ?';
                $types .= 's';
                $vars[] = $v;
            }
        }
        foreach($order as $k => $v) {
            if(!nqvDB::isField($k,$tablename)) continue;
            $orderby[] = '`' . $k . '`' . ' ' . $v;
        }
        if(!empty($where)) {
            $sql .= ' WHERE ' . implode(' and ', $where);
            if(!empty($whereOr)) $sql .= ' ' . implode(' or ', $whereOr);
        }
        elseif(!empty($whereOr)) $sql .= ' WHERE ' . implode(' or ', $whereOr);
        if(!empty($orderby)) $sql .= ' ORDER BY ' . implode(' , ', $orderby);
        if(!empty($limit)) {
            if(!empty($limit[1])) $sql .= ' LIMIT ' . intval($limit[0]) . ', ' . intval($limit[1]);
            else $sql .= ' LIMIT ' . intval($limit[0]);
        }
        #if($tablename === 'categories') _log([$sql,$order,$orderby]);
        try {
            $stmt = nqvDB::prepare($sql);
            if(!empty($vars)) $stmt->bind_param($types,...$vars);
        } catch(Exception $e) {
            if(DEBUG) my_print([$sql,$e->getMessage()]);
        }
        return nqvDB::parseSelect($stmt);
    }

    public static function getAdminSessionTypeId() {
        $stmt = nqvDB::prepare('SELECT id FROM `session_types` WHERE `slug` = "admin" LIMIT 1');
        $output = nqvDB::parseSelect($stmt);
        return @$output[0]['id'];
    }

    public static function getRootSessionTypeId() {
        $stmt = nqvDB::prepare('SELECT id FROM `session_types` WHERE `slug` = "root" LIMIT 1');
        $output = nqvDB::parseSelect($stmt);
        return @$output[0]['id'];
    }

    public static function getSessionTypes($full = false) {
        $sql = 'SELECT * FROM `session_types`';
        if(!$full) $sql .= ' WHERE `slug` != "root"';
        $sql .= ' ORDER BY `name`';
        $stmt = nqvDB::prepare($sql);
        $output = nqvDB::parseSelect($stmt);
        return $output;
    }

    public static function getSessionTypeById($id) {
        $sessionType = new nqvSession_types(['id' => $id]);
        return $sessionType;
    }

    public static function getCurrentSessionType() {
        if(!self::getSession()->isAuth()) return null;
        $typeId = self::getSession()->getType();
        if(empty($typeId)) $typeId = $_SESSION['user']->get('session_types_id');
        return self::getSessionTypeById($typeId);
    }

    public static function getCurrentSessionTypeName() {
        $type = self::getCurrentSessionType();
        return is_object($type) ? $type->get('name'):null;
    }

    public static function getCurrentSessionTypeNameId() {
        $type = self::getCurrentSessionType();
        return is_object($type) ? $type->get('id'):null;
    }

    public static function getCurrentUser() {
        return @$_SESSION['user'];
    }

    public static function getCurrentUserId() {
        $user = self::getCurrentUser();
        return empty($user) ? 0:$user->get('id');
    }

    public static function getActivitiesByCategory(mixed $category,?string $status) {
        try {
            if(is_numeric($category)) $cat  = new nqvCategories(['id' => $category]);
            elseif(is_string($category)) $cat  = new nqvCategories(['slug' => $category]);
            elseif(is_array($category)) $cat  = new nqvCategories(['id' => $category['id']]);
            elseif(is_a($category,'nqvCategories')) $cat  = $category;
            else throw new Exception('Error de entrada en getActivitiesByCategory');
            $id = $cat->get('id');
            $sql = "SELECT * FROM `activities` WHERE FIND_IN_SET(categories_id,?)";
            $types = 'i';
            $vars = [$id];
            if($status) {
                $sql .= ' AND `status` = ?';
                $types .= 's';
                $vars[] = $status;
            }
            $stmt = nqvDB::prepare($sql);
            $stmt->bind_param($types,...$vars);
            $result = nqvDB::parseSelect($stmt);
            return $result;
        } catch (Exception $e) {
            _log($e->getMessage(),'activities-error');
        }
    }

    public static function parseTags(): void {
        if(isValidJson((string) @$_POST['tags'])) {
            $tags = json_decode($_POST['tags'],true);
            if(!empty($tags)) {
                foreach($tags as $tag) {
                    /*
                    $OBJtag = new nqvTags(['VALUE' => $tag['value']]);
                    if(!$OBJtag->exists()) continue;
                    $output[] = $OBJtag->get('value');
                    */
                    $output[] = $tag['id'];
                }
                $_POST['tags'] = implode(',',$output);
            }
        }
    }

    // Alias
    public static function getCurrentUserType(){
        return self::getCurrentSessionTypeName();
    }

    public static function createUser($vars) {
        $email = filter_var($vars['email'],FILTER_VALIDATE_EMAIL);
        $pass = set_password_value($vars['password']);
        $sql = 'INSERT INTO `users` SET `name` = ?, `lastname` = ?, `email` = ?, `password` = ?, `session_types_id` = ?, created_at = NOW()';
        $stmt = nqvDB::prepare($sql);
        $stmt->bind_param('ssssi',$vars['name'],$vars['lastname'],$email,$pass,$vars['type']);
        $stmt->execute();
        return $stmt->insert_id;
    }

    public static function getUser($vars) {
        return new nqvUsers($vars);
    }
 
    public static function login() {
        if(empty($_POST['email']) || empty($_POST['password'])) return false;
        $user = self::getUser(['email' => $_POST['email']]);
        if(empty($user) || !$user->exists()) return false;
        if($user->check_password($_POST['password'])) self::getSession()->create($user);
        else self::getSession()->destroy('Las credenciales no son correctas','/admin');
        return self::getSession()->isAuth();
    }

    public static function setReferer(string $template): void {
        if(isAjax()) return;
        if(@$_SERVER['REQUEST_METHOD'] !== 'GET') return;
        $current = isAdmin() ? '/admin/' . $template:$template;
        if(empty($_SESSION['referers'])) $_SESSION['referers'] = [$current,$current];
        else $_SESSION['referers'] = [$current,$_SESSION['referers'][0]];
    }

    public static function getReferer(): string {
        return empty($_SESSION['referers']) ? '':array_pop($_SESSION['referers']);
    }

    public static function getConfig($slug) {
        if(empty(self::$config)) {
            $config = self::get('config');
            foreach($config as $conf) {
                self::$config[$conf['slug']] = $conf;
            }
            self::$config['listcount']['value'] = 20;
        }
        if(!isset(self::$config[$slug])) return null;
        else return (string) self::$config[$slug]['value'];
    }

    public static function createConfig(string $name, string $slug, string $value) {
        $sql = 'INSERT INTO `config` SET `name` = ?, `slug` = ?, `value` = ?, `created_at` = NOW(), `created_by` = ?';
        $userId = nqv::getCurrentUser()->get('id');
        $stmt = nqvDB::prepare($sql);
        $stmt->bind_param('sssi',$name,$slug,$value,$userId);
        $stmt->execute();
        if($stmt->insert_id) {
            nqvNotifications::add('Se ha creado la configuración ' . $name , 'success');
            return $stmt->insert_id;
        } else {
            return nqvNotifications::add('No se pudo crear la configuración ' . $name , 'error');
        }
    }

    public static function setConfig(string $slug, string $value, $create = false) {
        $config = self::getConfig($slug);
        if(is_null($config) && $create) {
            $sql = 'INSERT INTO `config` SET `name` = ?, `slug` = ?, `value` = ?, `created_by` = ?';
            $stmt = nqvDB::prepare($sql);
            $current_user_id = self::getCurrentUserId();
            $stmt->bind_param('sssi',$slug,$slug,$value,$current_user_id);
            $stmt->execute();
        } elseif(!is_null($config)) {
            $sql = 'UPDATE `config` SET `value` = ? WHERE `slug` = ?';
            $stmt = nqvDB::prepare($sql);
            $stmt->bind_param('ss',$value,$slug);
            $stmt->execute();
        } else {
            if(isAjax()) return false;
            else return nqvNotifications::add($slug . ' no existe en la configuración' , 'error');
        }
        if(empty($stmt->error)) {
            if(isAjax()) return true;
            else return nqvNotifications::add('Se ha actualizado la configuración ' . $slug , 'success');
        } else {
            if(isAjax()) return false;
            else return nqvNotifications::add('No pudo actualizarse la configuración ' . $slug , 'error');
        }
    }

    public static function setAccess(array $perms) {
        if(!self::userCan($perms)) throw new Exception('No tenés permiso para acceder a esta sección.');
    }

    public static function getCurrentSessionPermissions(): array {
        if(nqv::getCurrentUserType() === 'root') return ['all'];
        $type = nqv::getCurrentSessionTypeName();
        $conf = self::getConfig('permissions');
        $output = [];
        $conf = json_decode($conf,true);
        $full_array = array_walk_recursive($conf,function($v,$k) use (&$output){
            if(isValidJson($v)) $v = json_decode($v,true);
            $output[$k] = $v;
        });
        return $output;
    }

    public static function userCan(array $perms): bool {
        if(nqv::getCurrentUserType() === 'root') return true;
        else {
            $conf = self::getConfig('permissions');
            if(empty($conf)) return false;
            else {
                $conf = json_decode($conf,true);
                $type = nqv::getCurrentSessionTypeName();
                if(isValidJson(@$conf[$type]['crud'])) $crud = json_decode($conf[$type]['crud'],true);
                else $crud =  @$conf[$type]['crud'];
                if(isValidJson(@$conf[$type][$perms[0]])) $perm = json_decode($conf[$type][$perms[0]],true);
                else $perm =  @$conf[$type][$perms[0]];
                if(empty($perms[1])) {
                    _log_more(['Solicitud de permisos mal conformada',$perms],'perms-error');
                    return false;
                }
                return in_array($perms[1],(array) @$perm) || in_array($perms[1],(array) @$crud);
            }
        }
    }

    public static function getQuery(?array $vars = []): string {
        $q = array_merge($_GET,$vars);
        if(empty($q)) return '';
        else return '?' . http_build_query($q);
    }

    public static function rmQuery(string $k): string {
        $q = $_GET;
        unset($q[$k]);
        return '?' . http_build_query($q);
    }

    public static function url(string $path): string {
        if(isTemplatePath($path)) $path = str_replace(TEMPLATES_PATH, CORE_PATH . DIRECTORY_SEPARATOR, $path);
        elseif(isInclude($path)) $path = str_replace(INCLUDES_PATH, CORE_PATH . DIRECTORY_SEPARATOR, $path);
        return url($path);
    }

    public static function getMonth($i, $lang='es') {
        if($lang === 'en') return self::$months[$i-1];
        else return self::$meses[$i-1];
    }

    public static function translateDate($date,$style=null) {
        $meses = self::$meses;
        if($style === 'ucfirst') array_walk($meses,function(&$a,$b){
            $a = ucfirst($a);
        });
        return str_ireplace(self::$months, $meses, $date);
    }

    public static function getValue(string $k, $v){
        $output = $v; 
        $parts = array_values(array_filter(explode('_',$k)));
        $sufix = array_pop($parts);
        $tablename = implode('_',$parts);
        $table = new nqvDbTable($tablename);
        if($sufix === 'id' && $table->isTable()) {
            $fieldname = $table->getMainFieldName();
            $sql = 'SELECT ' . $fieldname . ' as label FROM ' . $tablename . ' WHERE id = ? ORDER by ' . $fieldname . ' ASC, id ASC LIMIT 1';
            $stmt = nqvDB::prepare($sql);
            $stmt->bind_param('i',$v);
            $output = @nqvDB::parseSelect($stmt)[0]['label'];
        } elseif($k === 'created_by') {
            $user = new nqvUsers(['id' => $v]);
            $output = $user->exists() ? $user->get_fullname():'Usuario desconocido';
        }
        return $output;
    }

    public static function getSearchables(): array {
        $output = [];
        $conf = nqv::getConfig('searchables');
        return array_map(function($t){
            return trim($t);
        },array_unique(array_filter((array) explode(',',$conf))));
    }

    public static function getAllTags() {
        $stmt = nqvDB::prepare('SELECT id as value, value as label FROM tags');
        $output = nqvDB::parseSelect($stmt);
        return json_encode($output,JSON_HEX_APOS);
    }

    public static function tableHasColumn($tablename, $columname) {
        $output = in_array($columname,nqvDB::getFieldNames($tablename));
        return !empty($output);
    }

    public static function __getAll() {
        $tablenames = self::getSearchables();
        $output = [];
        foreach($tablenames as $tablename) {
            $table = new nqvDbTable($tablename);
            $field = $table->getStaticMainField();
            if(is_array($field)) $field = 'CONCAT(' . implode('," ",',$field). ')';
            if(self::tableHasColumn($tablename,'tags')) $sql = 'SELECT id as value, ' . $field .' as label, tags FROM `' . $tablename . '`';
            else $sql = 'SELECT id as value, ' . $field .' as label FROM `' . $tablename . '`';
            $sql .= ' order by label';
            $stmt = nqvDB::prepare($sql);
            $result = nqvDB::parseSelect($stmt);
            array_walk($result, function(&$i,$k) use ($tablename) {
                $i['category'] = ['slug' => nqv::translate($tablename,'es','slug'),'label' => nqv::translate($tablename)];
                $i['tablename'] = $tablename;
                $result[$k] = $i;
            },$result);
            $output = array_merge($output,$result);
        }
        return json_encode($output,JSON_HEX_APOS);
    }

    public static function getAll(?string $status) {
        $categories = nqv::get('categories');
        $output = [];
        foreach($categories as $category) {
            $activities = self::getActivitiesByCategory($category,$status);
            array_walk($activities, function(&$i,$k) use ($category) {
                $i['category'] = ['slug' => nqv::translate($category['slug'],'es','slug'),'label' => nqv::translate($category['name'])];
                $tags = array_unique(array_filter(explode(',',(string) $i['tags'])));
                array_walk($tags,function(&$a){
                    if(is_numeric($a)) {
                        $tagOBJ = new nqvTags(['id'=>$a]);
                        if(!$tagOBJ->exists()) return null;
                        $a = $tagOBJ->get('value');
                    }
                });
                $i['tags'] = implode(',',$tags);
                $result[$k] = $i;
            },$activities);
            $output = array_merge($output,$activities);
        }
        return json_encode($output,JSON_HEX_APOS);
    }

    public static function getAllFromTag($tag): array {
        $tagOBJ = new nqvTags(['value' => $tag]);
        $tagID = $tagOBJ->get('id');
        $stmt = nqvDB::prepare('SELECT * FROM `activities` WHERE FIND_IN_SET(? ,`tags`) OR FIND_IN_SET(? ,`tags`) AND `status` = "active"');
        $stmt->bind_param('ss',$tagID,$tag);
        return (array) nqvDB::parseSelect($stmt);
    }

    public static function getTagsFromTextList($list) {
        return implode(', ',array_map(function($a){
            if(is_numeric($a)) {
                $tagOBJ = new nqvTags(['id'=>$a]);
                if(!$tagOBJ->exists()) return null;
                $text = $tagOBJ->get('value');
            } else {
                $text = $a;
            }
            return '<a href="/tags/' . urlencode($text) . '">' . $text . '</a>';
        },explode(',',$list)));
    }

    public static function getDatabasePupupItems() {
        $output = [];
        $items = nqv::getConfig('items-admin');
        if(isValidJson($items)) {
            $tablenames = json_decode($items,true);
            if(currentSessionTypeIs('root')) {
                $tablenames['-'] = '--';
                $tablenames = array_merge($tablenames,array_diff(nqvDB::getTablenames(),$tablenames));
            }
        }
        else {
            if(currentSessionTypeIs('root')) $tablenames = nqvDB::getTablenames();
            else $tablenames = [];
        }
        foreach($tablenames as $tablename) {
            $output[$tablename] = nqv::translate($tablename,'es','adminheader');
        }
        return $output;
    }

    public static function parseImagesFilesUpload(string $tablename,int $element_id) {
        if(!filesIsEmpty()) {
            $images_saved = 0;
            $images_deleted = 0;
            $images = nqvImage::getByElementId($tablename,$element_id);
            foreach($_FILES as $k => $f) {
                $fileToDelete = null;
                $isImageFile = preg_match('/^image-[0-9]+$/',$k);
                $isGallerFile = strpos($k,'gallery-files') !== false;
                if($isImageFile || $isGallerFile) {
                    foreach((array) $_FILES[$k]['name'] as $m => $name) {
                        if($_FILES[$k]['error'][$m] === UPLOAD_ERR_NO_FILE) continue;
                        $filepath = UPLOADS_PATH . 'images/' . $tablename . '/' . $element_id . '/' . createImageSlug($name);
                        $input = [
                            'filepath' => $filepath,
                            'tablename' => $tablename,
                            'element_id' => $element_id
                        ];
                        $image = new nqvImage($input);
                        $fileToDelete = @array_map(function($a) use ($filepath){
                            if($a->get('filepath') === str_replace(ROOT_PATH,'',$filepath)) return $a->get('filepath');
                        },$images)[0];
                        if($image->upload($_FILES[$k]['tmp_name'][$m],$fileToDelete)) {
                            $saved = $image->save();
                        } else {
                            nqvNotifications::add('No se pudo subir el archivo de la imagen ' . $k,'error');
                            break;
                        }
                        if(empty($saved)) {
                            nqvNotifications::add('La imagen ' . $name . ' no se pudo guardar','error');
                            break;
                        }
                        else $images_saved ++;
                    }
                } elseif($k === 'main-image' && !empty($_FILES['main-image']['size'])) {
                    $input = [
                        'name' => $_FILES['main-image']['name'],
                        'tablename' => $tablename,
                        'element_id' => $element_id
                    ];
                    $stmt = nqvDB::prepare('SELECT * FROM `mainimages` WHERE `tablename` = ? and `element_id` = ?');
                    $stmt->bind_param('si',$tablename,$element_id);
                    $imageData = (array) @nqvDB::parseSelect($stmt)[0];
                    $fileToDelete = @$imageData['filepath'];
                    $image = new nqvMainimages(array_merge($imageData,$input));
                    if($image->upload($_FILES['main-image']['tmp_name'],$fileToDelete,false)) {
                        $saved = $image->save();
                        $image->createThumbnails();
                    } else nqvNotifications::add('No se pudo subir el archivo de la imagen','error');
                    if(empty($saved)) nqvNotifications::add('La imagen no se pudo guardar','error');
                    else nqvNotifications::add('La imagen se ha guardado','success');
                }
            }
            if($images_saved) nqvNotifications::add('Se guardaron ' . $images_saved . ' imágenes.','success');
            if(!empty($_POST['files-to-delete'])) {
                foreach(array_unique(array_filter(explode(',',$_POST['files-to-delete']))) as $did) {
                    $dim = new nqvImage(['id' => $did]);
                    if($dim->delete()) $images_deleted ++;
                }
                if($images_deleted === 1) nqvNotifications::add('Se eliminó una imagen.','success');
                elseif($images_deleted) nqvNotifications::add('Se eliminaron ' . $images_deleted . ' imágenes.','success');
            }
        }
    }

    public static function getActivitiesByHolder(int $holder_id,?array $excludes) {
        $sql = 'SELECT * FROM `activities` WHERE `holders_id` = ?';
        $types = 'i';
        $vars = [$holder_id];
        foreach((array) $excludes as $k => $v) {
            if(property_exists('nqvActivities',$k)) {
                foreach($v as $var) {
                    $sql .= ' AND ' . $k . ' <> ?';
                    $types .= $var['type'];
                    $vars[] = $var['value'];
                }
            }
        }
        $stmt = nqvDB::prepare($sql);
        $stmt->bind_param($types,...$vars);
        return nqvDB::parseSelect($stmt);
    }

    public static function getNextVersion(): string {
        $versions = [];
        $cv = nqv::getConfig('version');
        foreach(scandir(UPGRADES_PATH) as $file) {
            if (strpos($file,'.') === 0) continue;
            $filename = pathinfo($file,PATHINFO_FILENAME);
            $nv = @explode('-',$filename)[1];
            $versions[] = $nv;
        }
        usort($versions, function($a, $b) {
            return version_compare($a, $b);
        });

        foreach($versions as $version) {
            if(version_compare($version,$cv) > 0) return $nv;
        }
        return '';
    }

    public static function checkUpgrades(): bool {
        $nv = self::getNextVersion();
        return !empty($nv);
    }

    public static function parseCheckboxes(string $tablename) {
        $table = new nqvDbTable($tablename);
        $fields = $table->getTableFields();
        foreach($fields as $field) {
            if($field['Type'] === 'tinyint unsigned' || $field['Type'] === 'tinyint(1)') {
                $value = isset($_POST[$field['Field']]);
                $_POST[$field['Field']] = $value;
            }
        }
    }

    public static function parseMainHttoQuery() {
        #_log([@$_SERVER['REQUEST_URI'],@$_SERVER['HTTP_REFERER'],@$_SERVER['REMOTE_ADDR']]);
        $mm = self::getConfig('maintenance-mode');
        if(!user_is_logged() && isAdmin()) {
            if(nqv::getVars(0) === 'password-reset') $template = 'password-reset';
            else $template = 'login';
        } else {
            if($mm && !user_is_logged()) {
                if(is_template_enabled_on_maintenance(nqv::getVars(0))) $template = nqv::getVars(0);
                else $template = nqv::getConfig('maintenance-template');
            } else {
                $template = nqv::getVars(0);
                if(!empty($template)) {
                    if(!isTemplate($template)) {
                        $test = nqv::translate((string) $template,'es','slug',true);
                        if(isAdmin()) {
                            if(nqvDB::isTable($test)) {
                                nqv::addVar('database',0);
                                $template = 'database';
                            }
                        } elseif(isFront()) {
                            if(isCategory($template)) {
                                header('location:/category/' . $template);
                                exit;
                            }
                        }
                    } elseif($template === 'images') {
                        include_template($template);
                        exit;
                    }
                }
            }
            if(empty($template)) $template = 'home';
        }
        nqv::setReferer($template);
        return [
            'maintenanceMode' => $mm,
            'template' => $template
        ];
    }
}