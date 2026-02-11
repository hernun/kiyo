<?php
    $tablename = 'users';
    $formtype = nqv::getVars(2);
    $file = TEMPLATES_PATH . 'crud/' . $formtype . '_' . $tablename . '.php';

    nqvNotifications::flush(null);

    nqv::setAccess(['read',$tablename]);
    $table = new nqvDbTable($tablename);
    if(!$table->isTable()) {
        nqvNotifications::add($tablename . ' no es una tabla','error');
        header('location:' . getAdminUrl());
        exit;
    } else $fields = $table->getTableFields();
    $options = ['include'=>[],'exclude'=>[]];
    $options['exclude'] = ['id','mainfield','token','modified_at','created_by','created_at'];
?>
    <div class="my-4">
        <?php $list = new nqvList($tablename)?>
        <?php if(!currentSessionTypeIs('root') && $tablename === 'users') $list->addCondition('session_types_id%not',nqv::getRootSessionTypeId())?>
        <?php echo $list->getHeader()?>
        <div class="table-container">
            <table id="database-table" class="table table-striped table-hover table-bordered">
                <?php echo $list->getTableHead(@$options)?>
                <?php echo $list->getTableBody(@$options)?>
            </table>
        </div>
    </div>
<script>
    $(document).ready( function () {
        $('#database-table').DataTable({
            columnDefs: [
                { width: '20px', targets: [0], searchable: false },
                { width: '120px', targets: [-1,-2,-3], searchable: false }
            ],
            autoWidth: false,
            scrollX: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/2.1.6/i18n/es-AR.json',
            },
        });
    });
</script>