<?php
nqv::cleanView();

$data  = json_decode(file_get_contents('php://input'), true);

$input   = $data['slug'] ?? 'test';
$table   = $data['table'] ?? 'pages';
$exclude = $data['exclude'] ?? '';
$lang = $data['lang'] ?? '';

if (!$input || !$table) {
    echo json_encode(['slug' => $input]);
    exit;
}

//$tableObj = new nqvDbTable($table);

if (!nqvDB::isTable($table)) {
    echo json_encode(['slug' => $input]);
    exit;
}

/*
|--------------------------------------------------------------------------
| 1. Normalizar slug base
|--------------------------------------------------------------------------
| - quitar sufijo _numero
| - quitar _ final suelto
*/
$base = preg_replace('/_[0-9]+$/', '', $input);
$base = rtrim($base, '_');

if (!empty($exclude) && $exclude === $base) {
    echo json_encode(['slug' => $base]);
    exit;
}

/*
|--------------------------------------------------------------------------
| 2. Escapar comodines para LIKE
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
| 3. Query
|--------------------------------------------------------------------------
*/
$sql = "SELECT `id`,`slug` FROM `$table` WHERE (slug = ? OR slug LIKE ?)";
$types = 'ss';
$vars = [$base, $pattern];

if($lang) {
    $sql .= ' AND `lang` = ?';
    $types .= 's';
    $vars[] = $lang;
}

$stmt = nqvDB::prepare($sql);
$stmt->bind_param($types, ... $vars);
$result = nqvDB::parseSelect($stmt);

/*
|--------------------------------------------------------------------------
| 4. Calcular siguiente sufijo
|--------------------------------------------------------------------------
*/
if (empty($result)) {
    echo json_encode(['slug' => $base]);
    exit;
}

$max = 0;

foreach ($result as $row) {

    $current = $row['slug'];

    if ($current === $base) {
        $max = max($max, 0);
        continue;
    }

    if (preg_match('/^' . preg_quote($base, '/') . '_([0-9]+)$/', $current, $m)) {
        $num = (int)$m[1];
        $max = max($max, $num);
    }
}

$newSlug = $max === 0
    ? $base . '_1'
    : $base . '_' . ($max + 1);

echo json_encode(['slug' => $newSlug]);
exit;
