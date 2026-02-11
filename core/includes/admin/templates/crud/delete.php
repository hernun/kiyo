<?php
$tablename = nqv::getVars(1);
$table = new nqvDbTable($tablename);
$id = nqv::getVars(3);
$fields = $table->getTableFields();
$formId = 'delete-' . $tablename;

if(!$table->isTable()) {
    nqvNotifications::add($tablename . ' no es una tabla' ,'error');
    header('location:' . getAdminUrl());
    exit;
}

$sql = 'SELECT * FROM ' . $tablename . ' WHERE id = ? LIMIT 1';
$stmt = nqvDB::prepare($sql);
$stmt->bind_param('i',$id);
$result = nqvDB::parseSelect($stmt);
$item = @$result[0];

$mainfield = $item[$table->getMainFieldName()];

if(empty($item)) {
    nqvNotifications::add($tablename . ' con id ' . $id . ' no existe.' ,'error');
    header('location:' . getAdminUrl());
    exit;
}

if(submitted($formId)) {
    if(nqvDB::delete($tablename, $id)) nqvNotifications::add('El registro ha sido eliminado con éxito','success');
    header('location:' . getAdminUrl() . $tablename);
    exit;
}
?>
<div class="my-4">
    <div class="form-container d-flex justify-content-center">
        <form id="<?php echo $formId?>" class="needs-validation" method="post" accept-charset="utf8" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
            <div class="fs-1">
                ¿Querés eliminar el registro <?php echo $mainfield?> de la tabla <?php echo $tablename?>?
            </div>
            <div class="alert alert-warning my-2"><svg class="bi me-2" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>Esta acción no puede deshacerse</div>
            <div class="my-3">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>