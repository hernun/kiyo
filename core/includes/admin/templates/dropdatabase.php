<?php if(userIs('root')):?>
 <?php
    if(submitted('dropdatabase')) {
        try {
            $db = nqvDB::getConnection();
            $schema = $db->real_escape_string(DB_NAME); // asegúrate que DB_NAME esté definido en conf.php
            $result = $db->query("
                SELECT CONCAT('DROP TABLE IF EXISTS ', GROUP_CONCAT(CONCAT('`', table_name, '`') SEPARATOR ', '), ';') AS stmt
                FROM information_schema.tables
                WHERE table_schema = '$schema'
            ");
            if ($row = $result->fetch_assoc()) {
                $dropSQL = $row['stmt'];
                $db->query($dropSQL);
                nqvNotifications::add('Todas las tablas eliminadas', 'success');
            }
        } catch (Exception $e) {
            nqvNotifications::add('Error: ' . $e->getMessage(), 'error');
        }
        nqv::back();
    }
?>ç
<div class="comntainer center-center">
    <form accept-charset="utf-8" method="post">
        <input type="hidden" name="form-token" value="<?php echo get_token('dropdatabase')?>" />
        <input type="submit" class="btn btn-danger" value="Eliminar todas las tablas de la base de datos" />
    </form>
</div>
<?php else:?>
    <?php 
        nqvNotifications::add(nqv::translate('You do not have permission to access this section'),'error');
        nqv::back();
    ?>
<?php endif?>