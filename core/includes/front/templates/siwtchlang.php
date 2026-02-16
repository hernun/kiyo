<?php
nqv::cleanView();
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $data);
    $language = $data['language'] ?? null;
    if ($language) {
        $_SESSION['CURRENT_LANGUAGE'] = strtoupper($language);
        echo json_encode(['status' => 'ok']);
    } else {
        http_response_code(400);
        echo json_encode(['status'=>'error','error' => 'Missing language']);
    }
}
exit;