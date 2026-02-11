<?php
if(!userIs('root')) nqv::back();

$updateStatus = require CORE_PATH . '/includes/update/check.php';
$nv = nqv::getNextVersion();

$hasCoreUpdate = $updateStatus['has_update'];
$hasDbUpdate   = !empty($nv);

if(submitted('upgrade-db')) {
    $sqlFile = UPGRADES_PATH . 'upgrade-' . $nv . '.sql';
    if(!is_file($sqlFile)) nqvNotifications::add('La versión solicitada no existe','error');
    else {
        $mysqli = nqvDB::getConnection();
        // Leer archivo SQL
        $sqlContent = file_get_contents($sqlFile);

        if ($sqlContent === false) nqvNotifications::add('Error al leer el archivo SQL.','error');
        else {
            // Desactivar autocommit para usar transacción
            $mysqli->autocommit(false);
            try {
                // Separar las sentencias por punto y coma
                $queries = array_filter(array_map('trim', explode(';', $sqlContent)));
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        if (!$mysqli->query($query)) throw new Exception("Error en la consulta: " . $mysqli->error);
                    }
                }
                // Si todas las consultas fueron exitosas
                $mysqli->commit();
                nqvNotifications::add('Se actualizó la base de datos a la versión ' . $nv,'success');
            } catch (Exception $e) {
                // En caso de error, deshacer todo
                $mysqli->rollback();
                nqvNotifications::add('Transacción revertida. Error: ' . $e->getMessage(),'error');
            }

            $mysqli->close();
        }
    }
    header('location:');
    exit;
}

if (submitted('upgrade-core')) {
    require CORE_PATH . '/includes/update/download.php';
    require CORE_PATH . '/includes/update/apply.php';
}

?>
<?php nqvNotifications::flush(null)?>
<div id="upgrade-template" class="px-4">
    <div class="my-4 container">
        <?php if (!$hasCoreUpdate && !$hasDbUpdate): ?>
            <h2 class="text-center mt-5">El sistema está actualizado</h2>
        <?php elseif ($hasCoreUpdate): ?>
            <h2>Hay una nueva versión del sistema</h2>
            <strong>v<?php echo $updateStatus['latest'] ?></strong>
            <button name="upgrade-core">Actualizar sistema</button>
        <?php else:?>
            <h2 class="text-center my-5">Hay una nueva versión de la base de datos</h2>
            <h2 class="text-center my-5 text-success"><strong>v<?php echo $nv?></strong></h2>
            <form id="delete-activity" class="text-center w-100 needs-validation" method="post" accept-charset="utf-8" novalidate>
                <input type="hidden" name="form-token" id="form-token-input" value="<?php echo get_token('upgrade-db')?>" />  
                <button class="btn btn-default nqv-hover-sucess m-4 mx-0">Actualizar</button>
            </form>
        <?php endif?>
    </div>
</div>