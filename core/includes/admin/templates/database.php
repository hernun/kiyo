<?php

$tablename = nqv::getVars(1);
$formtype  = nqv::getVars(2);

/**
 * Helper local: resuelve primero USER, luego CORE.
 * Devuelve el partpath (sin .php) o null si no existe.
 */
function resolveTemplate(string $partpath): ?string
{
    $user = USER_TEMPLATES_PATH . $partpath . '.php';
    $core = TEMPLATES_PATH      . $partpath . '.php';

    if (is_file($user)) {
        return $partpath;
    }

    if (is_file($core)) {
        return $partpath;
    }

    return null;
}

$partpath = null;

/*
 * 1. Plantilla específica por tabla + acción
 */
$partpath = resolveTemplate('crud/' . $tablename . '-' . $formtype);

/*
 * 2. Fallbacks por acción
 */
if ($partpath === null) {
    if ($formtype === 'new') {
        $partpath = resolveTemplate('crud/new');

    } elseif ($formtype === 'edit') {
        $partpath = resolveTemplate('crud/edit');

    } elseif ($formtype === 'delete') {
        $partpath = resolveTemplate('crud/delete');

    } elseif ($formtype === 'remove') {
        $partpath = resolveTemplate('crud/remove');

    } elseif ($formtype === 'upload') {
        $partpath = resolveTemplate('crud/upload');

    } elseif ($formtype === 'publish') {
        $partpath = resolveTemplate('crud/read-parts/' . $tablename . '-publish');

    } elseif ($formtype === 'show') {
        $partpath = resolveTemplate('crud/read-parts/show');
    }
}

/*
 * 3. Default final
 */
if ($partpath === null) {
    $partpath = resolveTemplate('crud/read-parts/list');
}

/*
 * 4. Include final
 */
if ($partpath !== null) {
    include_once ADMIN_TEMPLATES_PATH . $partpath . '.php';
} else {
    nqvNotifications::add(
        'No existe ninguna plantilla válida para ' . $tablename . ' / ' . $formtype,
        'error'
    );
}

nqvNotifications::flush(null);
