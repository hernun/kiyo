<?php
nqv::cleanView();

$vars = nqv::getVars();
$endpoint = $vars[1] ?? null;

header('Content-Type: application/json');

if($endpoint === 'fonts') {
    echo nqvApi::listGoogleFonts();
} else {
    http_response_code(400);

    echo json_encode([
        'error' => true,
        'code' => 400,
        'message' => 'Bad request',
        'details' => ['endpoint'=>$endpoint]
    ]);
}

exit;