<?php
// TOKEN
function get_token($form, $clean = true, $sustained = false) {
    $token = md5(uniqid(microtime(), true));
    if ($clean) clean_tokens();
    $k = $form . '_token';
    $_SESSION[$k] = $token;
    if ($sustained) {
        if(empty($_SESSION['token_sustained'])) $_SESSION['token_sustained'] = [];
        $_SESSION['token_sustained'][] = $k;
    }
    return $token;
}

function clean_tokens(?bool $force = false) {
    if(empty($_SESSION)) _log_more('Session error','session-error');
    $keys = preg_grep('/.(token)/', array_keys($_SESSION));
    foreach ($keys as $k) {
        if (in_array($k, (array) @$_SESSION['token_sustained']) && !$force) continue;
        unset($_SESSION[$k]);
    }
}

function check_token($formname, $persistent = false) {
    if (empty($_POST['form-token'])) return false;
    if (!isset($_SESSION[$formname . '_token'])) return false;
    if ($_SESSION[$formname . '_token'] !== $_POST['form-token']) $output = false;
    else $output = true;
    if (!$persistent) unset($_SESSION[$formname . '_token']);
    return $output;
}

function parse_uploading_error($filename = 'fileToUpload', $strict = true): bool {
    $message = null;
    $error = false;
    if (empty($_FILES[$filename])) $message = 'El fichero ' . $filename . ' no fue recibido';
    else $error = $_FILES[$filename]['error'];
    if ($error && !$message) {
        if (intval($error) === 1) $message = 'El archivo subido excede la directiva upload_max_filesize en php.ini.';
        elseif (intval($error) === 2) $message = 'Error: El/los archivo/s subido excede la directiva MAX_FILE_SIZE que se especificó en el formulario HTML. El máximo aceptable es ' . ini_get('upload_max_filesize');
        elseif (intval($error) === 3) $message = 'El archivo subido se ha subido sólo parcialmente.';
        elseif (intval($error) === 4) $message = 'Ningún archivo fue subido.';
        elseif (intval($error) === 6) $message = 'Falta una carpeta temporal.';
        elseif (intval($error) === 7) $message = 'No se ha podido escribir el archivo en el disco.';
        elseif (intval($error) === 8) $message = 'Una extensión de PHP detuvo la carga de archivos. PHP no proporciona una manera de determinar qué extensión causó detuvo la carga; examinar la lista de extensiones cargadas con phpinfo() puede ayudar.';
        else $message = $error;
    }
    if(!empty($message)) {
        if($strict) exit('Error: ' . $message);
        else throw new Exception($message);
    }
    return false;
}

function submitted($formname, $persistent = false) {
    if (!empty($_POST) && !empty($_POST['form-token']) && !empty($_SESSION[$formname . '_token'])) {
        if (check_token($formname, $persistent)) return true;
    }
    return false;
}

function get_core_filepath($filename,$path): string {
    if(empty($path)) {
        if(isAdmin()) $path = ADMIN_TEMPLATES_PATH;
        else $path = FRONT_TEMPLATES_PATH;
    }
    return cleanslashes($path . $filename) . '.php';
}

function get_user_filepath($filename,$path): string {
    if(empty($path)) {
        if(nqv::getSection() === 'admin') $path = USER_ADMIN_TEMPLATES_PATH;
        else $path = USER_FRONT_TEMPLATES_PATH;
    }
    return cleanslashes($path . $filename) . '.php';
}

function include_template_part(string $filename): void{
    $userFilepath = get_user_filepath($filename,USER_PARTS_PATH);
    $coreFilepath = get_core_filepath($filename,PARTS_PATH);
    if(is_file($userFilepath)) include($userFilepath);
    elseif(is_file($coreFilepath)) include($coreFilepath);
}

function include_template(string $filename, array $args = [], bool $print = true, $path = null){
    $filepath = null;
    $userFilepath = get_user_filepath($filename,$path);
    $coreFilepath = get_core_filepath($filename,$path);
    $userErrorFilePath = get_user_filepath('404',$path);
    $coreErrorFilePath = get_core_filepath('404',$path);
    if(is_file($userFilepath)) $filepath = $userFilepath;
    elseif(is_file($coreFilepath)) $filepath = $coreFilepath;
    elseif(is_file($userErrorFilePath)) $filepath = get_user_filepath('404',$path);
    elseif(is_file($coreErrorFilePath)) $filepath = get_core_filepath('404',$path);
    if(!is_file((string) $filepath)) return null;
    ob_start();
    extract($args);
    try {
        include($filepath);
    } catch (Exception $e) {
        $path = isAdmin() ? ADMIN_TEMPLATES_PATH:FRONT_TEMPLATES_PATH;
        include($path . 'parse-error.php');
    }
    $output = ob_get_clean();
    if($print) echo trim($output);
    return $output;
}

function getHeader() {
    return include_template('header');
}

function getFooter() {
    return include_template('footer');
}

function getBanner($options = []) {
    if(isFront()) return include_template('banner',$options);
}

function getAsset(string $path): string {
    $userPath = USER_PATH . 'assets/' . ltrim($path, '/');
    $corePath = CORE_PATH . 'assets/' . ltrim($path, '/');
    $path = is_file($userPath) ? $userPath:$corePath;
    if(!is_file($path)) return '';
    return str_replace(ROOT_PATH, '/', $path) . '?v=' . ASSETVERSION;
}


function getSearch($type = 'default') {
    if($type === 'mobile') include_template('search-form-mobile');
    elseif($type === 'desktop') include_template('search-form-desktop');
    elseif($type === 'header') include_template('search-form-header');
    else include_template('search-form-default');
}

function getAdds() {
    if(isFront()) return include_template('adds');
}

function getBottomBanners() {
    if(isFront()) return include_template('bottom-banners');
}

function getMainSlider() {
    if(isFront()) return include_template('main-slider');
}

function getImageGallery() {
    return include_template('image-gallery',func_get_args(),true,FRONT_TEMPLATES_PATH);
}

function getForm(string $filename, array $args = [], $print = true) {
    return include_template($filename, $args, $print, FORMS_PATH);
}

function getFrontForm(string $filename, array $args = [], $print = true) {
    return include_template($filename, $args, $print, FRONT_FORMS_PATH);
}

function isDashboard() {
    $dashboardTemplates = ['login','dashboard'];
    return in_array(nqv::getVars(0),$dashboardTemplates);
}

function setupSql() {
    $sql = file_get_contents(SQL_PATH . 'setup.sql');
    try {
        nqvDB::beginTransaction();
        foreach(array_filter(explode(';',$sql)) as $query) nqvDB::query($query);
        nqvDB::commit();
    } catch (\Throwable $e) {
        nqvDB::rollback();
        throw $e;
    }
    header('location:/admin' . implode('/' , nqv::getVars()));
    exit;
}

function user_is_logged() {
    return !empty($_SESSION['auth']);
}

function user_is_front_logged() {
    return !empty($_SESSION['auth']) && userIs('holder') && userIsActive();
}

function root_user_exists() {
    $session_type = nqv::getRootSessionTypeId();
    $user = nqv::get('users',['session_types_id' => $session_type],['name' => 'desc'],[0,1]);
    return !empty($user);
}

function userIs(string $type): bool {
    $user = nqv::getCurrentUser();
    if(empty($user)) return false;
    if(is_a($user,'nqvHolders')) return $type === 'holder';
    else {
        $typeId = $user->get('session_types_id');
        $typeName = new nqvSession_types(['id' => $typeId]);
    }
    if(empty($typeName) || !$typeName->exists()) return false;
    return $type === $typeName->get('slug');
}

function userIsActive(): bool {
    $user = nqv::getCurrentUser();
    return $user->isActive();
}

function currentSessionTypeIs(string $type) {
    $typeId = nqv::getCurrentSessionTypeNameId();
    $typeName = new nqvSession_types(['id' => $typeId]);
    if(empty($typeName) || !$typeName->exists()) return false;
    return $type === $typeName->get('slug') || $typeName->get('slug') === 'root';
}

function isAdmin() {
    return nqv::getSection() === 'admin';
}

function isFront() {
    return nqv::getSection() === 'front';
}

function isCategory($slug) {
    $cat = new nqvCategories(['slug'=>$slug]);
    return $cat->exists();
}

function isPage($slug) {
    $page = getPageBySlug($slug);
    return !empty($page);
}

function isActivity($slug,$status = 'active') {
    $activity = new nqvActivities(['slug'=>$slug,'status'=>$status]);
    return $activity->exists();
}

function isInclude(string $path): bool {
    $basepath = str_replace($_SERVER['DOCUMENT_ROOT'],'',$path);
    $vars = array_values(array_filter(explode('/',$basepath)));
    return strtolower((string) @$vars[0]) === 'includes';
}

function getBreadcrumb() {
    return include_template( 'breadcrumb');
}

function isTemplate(string $filename) {
    if(!defined('TEMPLATES_PATH') || !defined('USER_TEMPLATES_PATH')) return null;
    $coreFilename = str_replace(TEMPLATES_PATH,'',$filename);
    $coreFilepath = TEMPLATES_PATH . $coreFilename . '.php';

    $userFilename = str_replace(USER_TEMPLATES_PATH,'',$filename);
    $userFilepath = USER_TEMPLATES_PATH . $userFilename . '.php';
    return is_file($coreFilepath) || is_file($userFilepath);
}

function isTemplatePath(string $path) {
    return strpos($path,TEMPLATES_PATH) === 0;
}

function is_template_enabled_on_maintenance($template): bool {
    $config = nqv::getConfig('maintenance-enabled-templates');
    if(isDev() && $template === 'readme' && isTemplate('readme')) return true;
    if(!empty($config)) {
        $templates = explode(',',(string) $config);
        return in_array($template,$templates);
    } else {
        return false;
    }
}

function getCategoriesTabs() {
    ob_start();
    include_template('categories-tabs');
    return ob_get_clean();
}

function get_main_image_input(string $id,?array $styles,?string $bg) {
    ob_start();
    include_template('main-image-input',func_get_args(),true,ADMIN_TEMPLATES_PATH);
    return ob_get_clean();
}

/**
 * Devuelve el HTML del input de Main Image (create / edit)
 * Compatible con parseImagesFilesUpload
 *
 * @param string $entity        Nombre de la tabla / entidad
 * @param int|null $elementId   ID del registro (null si create)
 * @param string|null $bg       URL de la imagen existente
 * @param array|null $options   Configuración extra (opcional)
 */
function get_main_image_input_pro(string $entity, ?int $elementId = null, ?array $options = null) {
    $defaults = [
        'formats' => [
            'square' => 'Cuadrado (512x512)',
            'full'   => 'Full (Original)',
            'banner' => 'Banner (3040x1020)',
        ],
        'default_format' => 'full',
        'dpi_options' => [72, 192],
        'default_dpi' => 72,
    ];

    $opts = $options ? array_merge($defaults, $options) : $defaults;

    ob_start();
    include_template('main-image-input-pro', ['entity'=>$entity,'elementId'=>$elementId,'opts'=>$opts], true, ADMIN_TEMPLATES_PATH);
    return ob_get_clean();
}


function get_image_gallery_input(string $id,?array $styles,?array $images) {
    ob_start();
    include_template('image-gallery-input',func_get_args(),true,FRONT_TEMPLATES_PATH);
    return ob_get_clean();
}

function isDev() {
    return ENV === 'dev';
}

function isProd() {
    return ENV === 'prod';
}

function isTes() {
    return ENV === 'test';
}

function isFrontendUser($role) {
    return in_array($role, FRONTEND_ROLES);
}

function isBackendUser($role) {
    return in_array($role, BACKEND_ROLES);
}

function hasHeader() {
    if(!user_is_logged()) return false;
    elseif(isAdmin() && nqv::getConfig('admin_header')) return true;
    elseif(isFront() && nqv::getConfig('front_header')) return true;
    return false;
}

function hasFooter() {
    if(nqv::getConfig('maintenance-mode') && !user_is_logged()) return false;
    elseif(isAdmin() && nqv::getConfig('admin_footer')) return true;
    elseif(isFront() && nqv::getConfig('front_footer')) return true;
    return false;
}

function getAdminUrl() {
    return nqv::url(ROOT_PATH) . 'admin/';
}

function getPageById(int $id, $lang = null) {
    if(!$lang) $lang = $_SESSION['CURRENT_LANGUAGE'];
    $stmt = nqvDB::prepare('SELECT * FROM `pages` WHERE `id` = ? AND lang = ?');
    $stmt->bind_param('is',$id,$lang);
    $pages = nqvDB::parseSelect($stmt);
    return empty($pages[0]) ? []:$pages[0]; 
}

function getPageBySlug(string $slug, $lang = null) {
    if(!$lang) $lang = $_SESSION['CURRENT_LANGUAGE'];
    $stmt = nqvDB::prepare('SELECT * FROM `pages` WHERE `slug` = ? AND `lang` = ?');
    $stmt->bind_param('ss',$slug,$lang);
    $pages = nqvDB::parseSelect($stmt);
    return empty($pages[0]) ? []:$pages[0]; 
}

function getEnabledLangs() {
    $langs = explode(',',ENABLED_LANGUAGES);
    return array_combine($langs, $langs);
}

function getLaguageSelector() {
    $cl = $_SESSION['CURRENT_LANGUAGE'] ?? 'ES';
    $lis = ['<div class="language-selector"><details><summary>' . $cl . '</summary><div class="lang-options">'];
    foreach(getEnabledLangs() as $lang) {
        if($lang === $cl) continue;
        $lis[] = '<a class="lang-item" href="/' . strtolower($lang) . '/' . implode('/',nqv::getVars()) . '">' . $lang . '</a>';
    }
    array_push($lis,' </div></details></div>');
    return implode($lis);
}

function getOvoEditor() {
    $path = TEMPLATES_PATH . 'ovo-editor/ovo-editor-includes.php';
    if(is_file($path)) include $path;
}

// Devuelve la url absoluta inclyendo el idiom
function getUrl($url) {
    return '/' . strtolower($_SESSION['CURRENT_LANGUAGE']) . '/' . $url;
}

function getPageLink($slug): string {
    $page = nqv::get('pages',['slug'=>$slug,'lang'=>$_SESSION['CURRENT_LANGUAGE']]);
    if(!empty($page[0])) return '<a href="' . getUrl($slug) . '">' . $page[0]['title'] . '</a>';
    else return '';
}

function getHeaderMenuItems($lang) {
    $menuItems = nqv::getConfig('header-menu');
    return !empty($menuItems[$lang]) ? $menuItems[$lang]:[];
}