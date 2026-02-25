<?php
function isAjax() {
    return getIsAjax();
}

function setIsAjax ($is) {
    global $isajax;
    $isajax = $is;
}

function getIsAjax () {
    global $isajax;
    return $isajax;
}

function ajax_get($tablename,$limit,$vars) {
    nqv::cleanView();
    $w =  !empty($vars) ? [$vars[0] => $vars[1]]:null;
    $output = nqv::get($tablename, $w, [], $limit);
	if ($vars[0] === 'id' && !empty($output[0])) echo json_encode($output[0]);
    elseif (!empty($output[0])) echo json_encode($output);
    else echo json_encode([]); 
    return;
}

function ajax_getslug() {
    nqv::cleanView();
    if(!nqvDB::isTable((string) @$_GET['tablename'])) return null;
    $id = empty($_GET['id']) ? 0:$_GET['id'];
    $slug = cleanString((string) @$_GET['value']);
    $stmt = nqvDB::prepare('SELECT * FROM ' . $_GET['tablename'] . ' where `slug`=? AND id <> ?');
    $stmt->bind_param('si',$slug, $id);
    $data = nqvDB::parseSelect($stmt);
    $i = 2;
    while(!empty($data)) {
        $stmt = nqvDB::prepare('SELECT * FROM ' . $_GET['tablename'] . ' where `slug`=? AND id <> ?');
        $stmt->bind_param('si',$slug, $id);
        $data = nqvDB::parseSelect($stmt);
        if(!empty($data)) $slug .= '-' . $i;
        $i++;
    }
    echo json_encode(['slug'=>$slug]);
    return;
}

function ajax_save_home_widgets_list() {
    if(!empty($_POST['list'])) {
        $list = base64_decode($_POST['list']);
        if(isValidJson($list)) nqv::setConfig('home-widgets',$list,true);
    }
    return;
}

function ajax_save_header_menu() {
    $lang = $_POST['lang'];
    $list = json_decode(base64_decode($_POST['list']), true);

    $menu = nqv::getConfig('header-menu');
    $menu[$lang] = $list;
    nqv::setConfig('header-menu',json_encode($menu));
    exit;
}