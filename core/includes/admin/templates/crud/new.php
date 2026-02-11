<?php
$tablename = nqv::getVars(1);
$table = new nqvDbTable($tablename);
$fields = $table->getTableFields();
$formId = 'create-' . $tablename;

if(submitted($formId)) {
    if(nqvDB::save($tablename, $_POST)) nqvNotifications::add('El registro ha sido creado con éxito','success');
    header('location:' . getAdminUrl() . $tablename);
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
                        <?php if($field['Field'] === 'id') continue?>
                        <?php if($field['Field'] === 'created_at') continue?>
                        <?php if($field['Field'] === 'created_by') continue?>
                        <?php if($field['Field'] === 'modified_at') continue?>
                        <?php $f = new nqvDbField($field,$tablename)?>
                        <div class="col-lg-6 col-xl-4"><?php echo $f?></div>
                    <?php endforeach?>
                </div>
                <div class="my-3">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    <?php else:?>
        <h4>No tenés permiso para acceder a esta sección</h4>
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