<?php

function my_print($item, $html = true, $index = 0, $cleanView = true, $print = true) {
    if($cleanView && class_exists('nqvH')) ob_end_clean();
    $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $line = $debug[$index]['line'];
    $file = $debug[$index]['file'];
    $output = '';
    if ($html) $output .= '<pre class="my-print"><div>';
    $output .= basename($file) . ' ' . $line . PHP_EOL;
    if (is_array($item)) array_walk_recursive($item, function (&$v) use ($html) {
        if (is_string($v) && $html) $v = htmlentities($v);
    });
    elseif (is_string($item) && $html) $item = htmlentities($item);
    ob_start();
    @print_r($item);
    $output .= ob_get_contents();
    ob_end_clean();
    #print_r($debug);
    if ($html) $output .= '</div></pre><br/>';
    if($print) echo $output . PHP_EOL . PHP_EOL;
    if ($item === 'exit') exit('---');
    return $output . PHP_EOL . PHP_EOL;
}

function my_print_more($item, $html = true, $index = 0) {
    if(defined('PHPUNIT_RUNNING') && PHPUNIT_RUNNING) $html = false;
    $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $files = array();
    $root = !defined('ROOT_PATH') ? @$_SERVER['DOCUMENT_ROOT']:ROOT_PATH;
    $debug = array_slice($debug, $index, count($debug) - $index);
    foreach ($debug as $d) {
        $file = str_replace($root, '', @$d['file']) . ' ' . @$d['line'] . PHP_EOL;
        if ($html) {
            $file = str_replace(basename($file), '<strong>' . basename($file) . '</strong>', $file);
            $file = '<small>' . $file . '</small>' . PHP_EOL;
        } else {
            $file = ' - ' . $file;
        }
        $files[] = $file;
    }
    $output = '';
    if ($html) $output .= '<pre class="my-print"><div>';
    $output .= implode($files) . PHP_EOL;
    if (is_array($item)) array_walk_recursive($item, function (&$v) use ($html) {
        if (is_string($v) && $html) $v = htmlentities($v);
    });
    elseif (is_string($item) && $html) $item = htmlentities($item);
    ob_start();
    @print_r($item);
    $output .= ob_get_contents();
    ob_end_clean();
    if ($html) $output .= '</div></pre><br/>';
    echo $output . PHP_EOL . PHP_EOL;
    if ($item === 'exit') exit('---' . PHP_EOL);
    return $output . PHP_EOL . PHP_EOL;
}

function my_print_clean($input) {
    for($a = 0; $a <= ob_get_level(); $a++) @ob_end_clean();
    my_print($input,true,1);
}

function _log($str, $logfile = 'general.log', $index = 2) {
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    $root = !defined('ROOT_PATH') ? $_SERVER['DOCUMENT_ROOT']:ROOT_PATH;
    $offset = $index - 1;
    $dirname = dirname($logfile);
    $logpath = $root . 'logs/';
    if(!is_dir($logpath)) mkdir($logpath);
    if (strpos(dirname($dirname), '.') === 0 || !is_dir($dirname)) $logfile = $logpath . $logfile;
    if (!ends_with($logfile, '.log')) $logfile .= '.log';
    $debug = debug_backtrace();
    $class = @$debug[$offset]['class'] ? @$debug[$offset]['class']:@$debug[$offset -1]['class'];
    $type = @$debug[$offset]['type'] ? @$debug[$offset]['type']:@$debug[$offset -1]['type'];
    $function = @$debug[$offset]['function'] ? @$debug[$offset]['function']:@$debug[$offset -1]['function'];
    $line = @$debug[$offset -1]['line'];
    $file = str_replace($root,'',@$debug[$offset -1]['file']);
    if (is_array($str) || is_object($str)) {
        $str = parseArray($str, 'n', $index);
        $log_str = date('d/m H:i') . ' - ' . $class . $type . $function . ":\r\n{$str}\r\n";
    } else {
        $log_str = date('d/m H:i') . ' - ' . $class . $type . $function . ', ' . $file . ' ' . $line . ":\r\n{$str}\r\n";
    }

    #file_put_contents($root . '/logs/overlog.log', $logfile . ': ' . $log_str, FILE_APPEND);
    file_put_contents($logfile, $log_str, FILE_APPEND);
}

function _log_more($str, $logfile = 'general.log') {
    $debug = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $root = !defined('ROOT_PATH') ? $_SERVER['DOCUMENT_ROOT']:ROOT_PATH;
    $files = [];
    foreach ($debug as $d) {
        $files[] = str_replace($root, '', (string) @$d['file']) . ' ' . (string) @$d['line'];
    }

    if (is_array($str) || is_object($str)) $str = parseArray($str);
    $str = PHP_EOL . implode(PHP_EOL, $files) . PHP_EOL . $str;
    _log($str, $logfile);
 }

 function parseArray($input, $glue = 'n', $index = 1) {
    ob_start();
    my_print($input, false, $index, false);
    $o = ob_get_clean();
    if ($glue === 'br') return nl2br($o);
    elseif ($glue === 'n') return br2nl($o);
    else return $o;
}

function ends_with($haystack, $needle) {
    $pos = strrpos($haystack, $needle);
    $ref = strlen($haystack) - strlen($needle);
    return $ref && $pos == $ref;
}

function br2nl($string) {
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function url(?string $path = null) {
    $root = !defined('ROOT_PATH') ? $_SERVER['DOCUMENT_ROOT']:ROOT_PATH;
    $domain = !defined('DOMAIN') ? $_SERVER['HTTP_HOST']:DOMAIN;
    $url = str_replace($root,'https://' . $domain . '/',(string) $path);
    return $url;
}

function path(string $url) {
    $root = !defined('ROOT_PATH') ? $_SERVER['DOCUMENT_ROOT']:ROOT_PATH;
    $domainUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/';
    if(strpos($url,$domainUrl) === false) $url = 'https://' . cleanslashes(DOMAIN . $url);
    $path = str_replace($domainUrl,$root,$url);
    $path = @explode('?',$path)[0];
    return $path;
}

function isCurrent(string $url) {
    $path = path($url);
    $url = url($path);
    $current = 'https://' . cleanslashes($_SERVER['HTTP_HOST'] . '/' . explode('?',$_SERVER['REQUEST_URI'])[0]);
    return $current === $url || $current === $url . '/' || $current . '/' === $url;
}

// Replace of zama_GenerarPalabra
function create_word($min = 6, $max = 30) {		
	$nros 	= array('1', '2', '3', '4','5','6','7','8','9');
	$letras 	= array('a','b', 'c', 'd', 'e','f', 'g', 'h','i','j', 'k', 'l', 'm', 'n', 'o', 'p', 'q','r', 's', 't','w','z');
	$tamano 	= intval(rand($min, $max));
	$actual		= intval(rand(1,2));		
	$nombre 	= '';	
	for($x=0;$x<$tamano;$x++) {			
		if($actual == 0) {
			$actual	= 1;
			$pos 	= rand(0,count($nros)-1);
			$nombre	.=  $nros[$pos];				
		} else {
			$actual	= 0;
			$pos 	= rand(0,count($letras)-1);
			$nombre	.=  $letras[$pos];				
		}				
	}
	return ucfirst($nombre);
}

function wrapText($text,$maxChar=160) { 
    $text = substr($text, 0, $maxChar);
    $index = strrpos($text, ' ');
    $text = substr($text, 0, $index); 
    $text .= '...';
    return $text;
}

function normalizeText( $texto ) {
    return preg_replace( '[^ A-Za-z0-9_.-]', '_', $texto);
}

function is_user_logged_in() {
    return isset($_SESSION['user']);
}

function convertToBytes($from) {
    if(empty($from)) return 0;
    if ($x = stripos((string) $from, 'Bytes') !== false) return floatval(substr($from, 0, $x));
    $number = substr($from, 0, -2);
    $sigla = strtoupper(substr($from, -2));
    $test = preg_match('/[0-9]/', $sigla);
    if ($test) {
        $number = substr($from, 0, -1);
        $sigla = strtoupper(substr($from, -1));
    }
    switch ($sigla) {
        case "KB":
            return $number * 1000;
        case "K":
            return $number * 1000;
        case "MB":
            return $number * pow(1000, 2);
        case "M":
            return $number * pow(1000, 2);
        case "GB":
            return $number * pow(1000, 3);
        case "G":
            return $number * pow(1000, 3);
        case "TB":
            return $number * pow(1000, 4);
        case "T":
            return $number * pow(1000, 4);
        case "PB":
            return $number * pow(1000, 5);
        case "P":
            return $number * pow(1000, 5);
        case "B":
            return $number;
        default:
            return $from;
    }
}

function cleanString($str) {
    $output = strtolower(preg_replace(array('/[^a-zA-Z0-9 \.-]/', '/[ -]+/', '/^-|-$/'), array('', '-', ''), remove_accent($str)));
    return substr($output, 0, 255);
}

function remove_accent($str) {
    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', ',', '#');
    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', '-', '');
    return str_replace($a, $b, $str);
}

/**
 *
 * Delete a directory RECURSIVELY
 * @param string $dir - directory path
 * @link http://php.net/manual/en/function.rmdir.php
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                $f = $dir . '/' . $object;
                if (is_dir($f)) rrmdir($dir . "/" . $object);
                else unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        return rmdir($dir);
    }
    return false;
}

function getWithinParenthesis($text) {
    preg_match('#\((.*?)\)#', $text, $match);
    return @$match[1];
}

function removeQutes(string $string): string {
    $string = str_replace('"', "", $string);
    $string = str_replace("'", "", $string);
    return $string;
}

/*
    *	Chequea la correspondencia entre una cadena y una encriptación
    *	@param string: [string] Cadena sin encriptar
    *	@param password: [string] Cadena codificada
    * 	return: bool
    */
function check_pass($string, $password) {
    $x = false;
    $salt = substr($password, 0, 32);
    $length = strlen($string);
    if ($length < 10) $string = crypt($string, 'h.');
    if ($password == crypt(trim($string), $salt)) $x = true;
    return $x;
}

function is_encrypted($string) {
    $encrypted = strpos($string, '$6$rounds=10000$');
    return $encrypted === 0;
}

function set_password_value($pass) {
    $chars = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $prefix_salt = '$6$rounds=10000$';
    $salt = '';
    $length = strlen($pass);
    if ($length < 10) $pass = crypt($pass, 'h.');
    for ($i = 0; $i < 16; $i++) $salt .= $chars[rand(0, 63)];
    for ($i = 0; $i < 10000; $i++) $salt = sha1($salt);
    $x = rand(0, 23);
    $salt = $prefix_salt . substr($salt, $x, 16);
    return crypt($pass, $salt);
}

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 16; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function isValidJson($string) {
    try {
        if(!is_string($string)) return false;
        json_decode((string) $string);
        return (json_last_error() == JSON_ERROR_NONE);
    } catch(Exception $e) {
        return false;
    }
}

function formatBytes($bytes) {
    $bytes = convertToBytes($bytes);
    $kilo = round($bytes / 1000, 2);
    $mega = round($bytes / pow(1000, 2), 2);
    $giga = round($bytes / pow(1000, 3), 2);
    $tera = round($bytes / pow(1000, 4), 2);
    $penta = round($bytes / pow(1000, 5), 2);

    if ($penta >= 1) return $penta . ' PB';
    if ($tera >= 1) return $tera . ' TB';
    if ($giga >= 1) return $giga . ' GB';
    if ($mega >= 1) return $mega . ' MB';
    if ($kilo >= 1) return $kilo . ' KB';
    return $bytes . ' Bytes';
}

function formatBibytes($bytes) {
    $bytes = convertToBytes($bytes);
    $kilo = round($bytes / 1024, 2);
    $mega = round($bytes / pow(1024, 2), 2);
    $giga = round($bytes / pow(1024, 3), 2);
    $tera = round($bytes / pow(1024, 4), 2);
    $penta = round($bytes / pow(1024, 5), 2);

    if ($penta >= 1) return $penta . ' PiB';
    if ($tera >= 1) return $tera . ' TiB';
    if ($giga >= 1) return $giga . ' GiB';
    if ($mega >= 1) return $mega . ' MiB';
    if ($kilo >= 1) return $kilo . ' KiB';
    return $bytes . ' Bytes';
}

function formatBits($bits) {
    $bytes = convertToBytes($bits);
    $kilo = round($bits / 1000, 2);
    $mega = round($bits / pow(1000, 2), 2);
    $giga = round($bits / pow(1000, 3), 2);
    $tera = round($bits / pow(1000, 4), 2);
    $penta = round($bits / pow(1000, 5), 2);

    if ($penta >= 1) return $penta . ' Pb';
    if ($tera >= 1) return $tera . ' Tb';
    if ($giga >= 1) return $giga . ' Gb';
    if ($mega >= 1) return $mega . ' Mb';
    if ($kilo >= 1) return $kilo . ' Kb';
    return $bytes . ' bits';
}

function cleanslashes(string $string): string {
    while(strpos($string,'//') !== false) {
        $string = str_replace('//','/',$string);
    }
    return $string;
}

function parse_cvs(string $filepath,int $offset = 1): array {
    $data = [];
    if (($handle = fopen($filepath, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
    }
    if(!empty($data) && $offset) return array_slice($data,$offset);
    else return $data;
}

function catchFilesError() {
    $errors = [];
    foreach($_FILES as $k => $file) {
        $ferror = intval($file['error']);
        if(empty($file['error'])) continue;
        elseif(UPLOAD_ERR_INI_SIZE === $ferror) $errors[] = $k . ': size of the uploaded file exceeds the maximum value';
        elseif(UPLOAD_ERR_FORM_SIZE === $ferror) $errors[] = $k . ': the size of the uploaded file exceeds the maximum value specified in the HTML form in the MAX_FILE_SIZE element.';
        elseif(UPLOAD_ERR_PARTIAL === $ferror) $errors[] = $k . ': the file was only partially uploaded';
        elseif(UPLOAD_ERR_NO_FILE === $ferror) $errors[] = $k . ': no file was uploaded';
        elseif(UPLOAD_ERR_NO_TMP_DIR === $ferror) $errors[] = $k . ': no temporary directory is specified in the php.ini';
        elseif(UPLOAD_ERR_CANT_WRITE === $ferror) $errors[] = $k . ': writing the file to disk failed';
        elseif(UPLOAD_ERR_EXTENSION === $ferror) $errors[] = $k . ': a PHP extension stopped the file upload process';
    }
    if(empty($errors)) return null;
    else return $errors;
}

function filesIsEmpty() {
    foreach($_FILES as $file) {
        if(UPLOAD_ERR_NO_FILE !== $file['error']) return false;
    }
    return true;
}

function nl2p(string $string, $line_breaks = false, $xml = false) {
    $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);
    return implode(array_map(function($a){
        $string = trim($a);
        if($string) return '<p>' . $a . '</p>';
    },explode(PHP_EOL,$string)));
}

function dir_is_empty($dir,$strict = true) {
    if(!is_dir($dir)) {
        if($strict) return false;
        else return true;
    }
    $handle = opendir($dir);
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && $entry != ".DS_Store") {
            closedir($handle);
            return false;
        }
    }
    closedir($handle);
    return true;
}

/*
* @path string ruta del directorio
* @level int nivel de profundidad límite para el escaneo recursivo. Si es 0 no hay límite.
*/
function parseDirectory(string $path, int $level, ?closure $callback): array{
    static $limit = 1;
    $output = [];
    $objects = scandir($path);
    foreach ($objects as $object) {
        if(strpos($object,'.') === 0) continue;
        $f = cleanslashes($path . '/' . $object);
        if(is_dir($f) && (intval($limit) < $level || $level === 0)) {
            $limit ++;
            $o = parseDirectory($f,$level,null);
            $output = $output + $o;
        }
        else $output[] = $f;
    }
    if(!empty($callback)) return array_filter(array_map($callback,$output));
    else return $output;
}

function createImageSlug(string $string) {
    return createSlug(pathinfo($string,PATHINFO_FILENAME));
}

function createSlug(
    string $text,
    string $separator = '-',
    ?string $table = null,
    ?string $exclude = null,
    string $column = 'slug'
): string {

    /*
    |--------------------------------------------------------------------------
    | 1. Normalización base (igual que tu función actual)
    |--------------------------------------------------------------------------
    */

    $slug = mb_strtolower($text, 'UTF-8');
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
    $slug = str_replace(['(', ')'], $separator, $slug);
    $slug = preg_replace('/[^a-z0-9\s' . preg_quote($separator, '/') . ']/', '', $slug);
    $slug = preg_replace('/\s+/', $separator, $slug);
    $slug = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $slug);
    $slug = trim($slug, $separator);

    /*
    |--------------------------------------------------------------------------
    | 2. Si no hay tabla → devolver slug simple
    |--------------------------------------------------------------------------
    */

    if (!$table || !nqvDB::isTable($table)) {
        return $slug;
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Normalizar base (quitar sufijos existentes)
    |--------------------------------------------------------------------------
    */

    $base = preg_replace('/_[0-9]+$/', '', $slug);
    $base = rtrim($base, '_');

    if (!empty($exclude) && $exclude === $base) {
        return $base;
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Escapar comodines LIKE
    |--------------------------------------------------------------------------
    */

    $escapedBase = str_replace(
        ['\\', '_', '%'],
        ['\\\\', '\\_', '\\%'],
        $base
    );

    $pattern = $escapedBase . '\_%';

    /*
    |--------------------------------------------------------------------------
    | 5. Query dinámica por columna
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT `$column`
            FROM `$table`
            WHERE `$column` = ?
               OR `$column` LIKE ?";

    $stmt = nqvDB::prepare($sql);
    $stmt->bind_param('ss', $base, $pattern);
    $result = nqvDB::parseSelect($stmt);

    if (empty($result)) {
        return $base;
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Calcular siguiente sufijo
    |--------------------------------------------------------------------------
    */

    $max = 0;

    foreach ($result as $row) {

        $current = $row[$column];

        if ($current === $base) {
            $max = max($max, 0);
            continue;
        }

        if (preg_match('/^' . preg_quote($base, '/') . '_([0-9]+)$/', $current, $m)) {
            $num = (int)$m[1];
            $max = max($max, $num);
        }
    }

    return $max === 0
        ? $base . '_1'
        : $base . '_' . ($max + 1);
}


function htmlToEditorJS($html) {
    $doc = new DOMDocument();
    // Cargar HTML y manejar caracteres UTF-8
    @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

    $blocks = [];

    $body = $doc->getElementsByTagName('body')->item(0);
    if (!$body) return json_encode(['blocks' => []]);

    foreach ($body->childNodes as $node) {
        // Saltar nodos que no son elementos
        if (!($node instanceof DOMElement)) continue;

        switch (strtolower($node->nodeName)) {
            case 'p':
                $blocks[] = [
                    'type' => 'paragraph',
                    'data' => ['text' => trim($doc->saveHTML($node))]
                ];
                break;

            case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                $level = (int)substr($node->nodeName, 1);
                $blocks[] = [
                    'type' => 'header',
                    'data' => [
                        'text' => trim($node->textContent),
                        'level' => $level
                    ]
                ];
                break;

            case 'ul': case 'ol':
                $style = $node->nodeName === 'ol' ? 'ordered' : 'unordered';
                $items = [];
                foreach ($node->getElementsByTagName('li') as $li) {
                    // Solo tomar los <li> hijos directos
                    if ($li->parentNode->isSameNode($node)) {
                        $items[] = trim($li->textContent);
                    }
                }
                $blocks[] = [
                    'type' => 'list',
                    'data' => [
                        'style' => $style,
                        'items' => $items
                    ]
                ];
                break;
        }
    }

    return json_encode(['blocks' => $blocks], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}
