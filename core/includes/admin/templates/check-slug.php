<?php
nqv::cleanView();

$data  = json_decode(file_get_contents('php://input'), true);

$input   = $data['slug'] ?? '';
$table   = $data['table'] ?? '';
$exclude = $data['exclude'] ?? '';

if (!$input || !$table) {
    echo json_encode(['slug' => $input]);
    exit;
}

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
$sql = "SELECT `slug`
        FROM `$table`
        WHERE slug = ?
           OR slug LIKE ?";

$stmt = nqvDB::prepare($sql);
$stmt->bind_param('ss', $base, $pattern);
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
