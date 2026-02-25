<?php
$tablename = nqv::getVars(1);
$id = nqv::getVars(3);
$table = new nqvDbTable($tablename);
if(!$table->isTable()) {
    nqvNotifications::add($tablename . ' no es una tabla' ,'error');
    header('location:' . getAdminUrl());
    exit;
}
$fields = $table->getTableFields();
$formId = 'update-' . $tablename;

$sql = 'SELECT * FROM ' . $tablename . ' WHERE id = ? LIMIT 1';
$stmt = nqvDB::prepare($sql);
$stmt->bind_param('i',$id);
$result = nqvDB::parseSelect($stmt);
$item = @$result[0];

if($tablename === 'config' && isTemplate($item['slug'])) {
    include_template($item['slug'],$item);
    return;
}

if($tablename === 'config' && $item['slug'] === 'mail-settings') {
    include_template('mail-settings',$item);
    return;
}

if(empty($item)) {
    nqvNotifications::add($tablename . ' con id ' . $id . ' no existe.' ,'error');
    header('location:' . getAdminUrl());
    exit;
}

if(submitted($formId)) {
    if(nqvDB::save($tablename, $_POST)) nqvNotifications::add('El registro ha sido actualizado con éxito','success');
    header('location:');
    exit;
}
?>
<div class="my-4">
    <?php if(nqv::userCan(['create',$tablename])):?>
        <?php $list = new nqvList($tablename)?>
        <?php echo $list->getHeader()?>
        <div class="form-container d-flex justify-content-center">
            <form id="<?php echo $formId?>" class="needs-validation" method="post" accept-charset="utf8" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="form-token" value="<?php echo get_token($formId)?>" />
                <div class="row" style="max-width:1400px">
                    <?php foreach($fields as $field):?>
                        <?php if($field['Field'] === 'created_at') continue?>
                        <?php if($field['Field'] === 'created_by') continue?>
                        <?php if($field['Field'] === 'modified_at') continue?>
                        <?php $f = new nqvDbField($field,$tablename)?>
                        <?php $f->setValue($item[$field['Field']])?>
                        <?php if($f->isHidden()):  echo $f;?>
                        <?php else:?>
                            <div class="col-lg-6 col-xl-4"><?php echo $f?></div>
                        <?php endif?>
                    <?php endforeach?>
                </div>
                <div class="my-3">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>
    <?php else:?>
        <div class="center-center">
            <p class="fs-5"><?php echo nqv::translate('You do not have permission to access this section')?></p>
        </div>
    <?php endif?>
</div>
<script>
    $(document).ready( function () {
        $('#database-table').DataTable({
            autoWidth: false,
            scrollX: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/2.1.6/i18n/es-AR.json',
            },
        });
    });
</script>