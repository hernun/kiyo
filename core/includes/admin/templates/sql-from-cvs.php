<?php 
if(!user_is_logged()) {
    $form = 'login';
} else {
    $form = 'db-file-upload';
    $args = [];
    if(submitted('db-file-upload')) {
        $error = parse_uploading_error('datafile');
        $args['fields'] = [];
        $args['tablename'] = '';
        if(!$error) {
            $args['tablename'] = $_FILES['datafile']['name'];
            $content = file($_FILES['datafile']['tmp_name']);
            $headers = str_getcsv(array_shift($content));
        }
        $model = str_getcsv((string)@$content[0]);
        if(!empty($model)) {
            foreach($headers as $k => $header) {
                $unsigned = false;
                $notnull = false;
                $unique = false;
                $original_name = $header;
                if($header === 'Timestamp') {
                    $header = 'created_at';
                    $type = 'datetime';
                    $notnull = true;
                } elseif($header === 'Nombre') {
                    $header = 'name';
                    $type = 'varchar';
                    $notnull = true;
                } elseif($header === 'Apellido') {
                    $header = 'lastname';
                    $type = 'varchar';
                    $notnull = true;
                } elseif($header === 'Email Address') {
                    $header = 'email';
                    $type = 'varchar';
                    $notnull = true;
                } elseif($header === 'Fecha de Nacimiento') {
                    $header = 'birthdate';
                    $type = 'datetime';
                } elseif($header === 'Ciudad de Residencia') {
                    $header = 'residence_city';
                    $type = 'varchar';
                } elseif($header === 'Nombre de la Empresa/Emprendimiento') {
                    $header = 'company_name';
                    $type = 'varchar';
                } elseif($header === 'Teléfono de contacto') {
                    $header = 'company_phone';
                    $type = 'integer';
                    $unsigned = true;
                } elseif($header === 'Teléfono') {
                    $header = 'personal_phone';
                    $type = 'integer';
                    $unsigned = true;
                } elseif($header === 'Correo electrónico del emprendimiento') {
                    $header = 'company_email';
                    $type = 'varchar';
                } elseif(strpos($header,'Breve descripción') === 0) {
                    $header = 'company_description';
                    $type = 'text';
                } elseif($header === 'Red Social Instagram') {
                    $header = 'instagram';
                    $type = 'varchar';
                } elseif($header === 'Red Social Facebook') {
                    $header = 'facebook';
                    $type = 'varchar';
                } elseif(strpos($header,'Spotify') !== false) {
                    $header = 'spotify';
                    $type = 'varchar';
                } elseif($header === 'Pagina Web') {
                    $header = 'website';
                    $type = 'varchar';
                } elseif(strpos($header,'Te pedimos 2 fotos') !== false) {
                    $header = 'photos';
                    $type = 'text';
                } elseif(strpos($header,'Queres Participar con un STAND') === 0) {
                    $header = 'stand';
                    $type = 'bool';
                } elseif($header === 'DNI') {
                    $header = 'dni';
                    $type = 'integer';
                    $unsigned = true;
                    $notnull = true;
                    $unique = true;
                } elseif($header === 'Nombre y apellido del responsable del STAND') {
                    $header = 'stand_responsible_name';
                    $type = 'varchar';
                } elseif(strpos($header,'necesidades espaciales y técnicas') !== false) {
                    $header = 'stand_requirements';
                    $type = 'text';
                } elseif(strpos($header,'YouTube') !== false) { 
                    $header = 'youtube';
                    $type = 'varchar';
                } else {
                    $type = is_numeric(@$model[$k]) ? 'integer':'text';
                }
                $args['fields'][] = [
                    'Field' => $header,
                    'Type' => $type,
                    'Model' => @$model[$k],
                    'unsigned' => $unsigned,
                    'notnull' => $notnull,
                    'unique' => $unique,
                    'original_name' => $original_name
                ];
            }
        }
        $form = 'db-parse-fields';
    } elseif(submitted('db-parse-fields')) {
        $tablename = $_POST['tablename'];
        $fields = [];
        $uniques = [',PRIMARY KEY (`id`)'];
        foreach($_POST['fieldnames'] as $k => $v) {
            $type = $_POST['fieldtypes'][$k];
            $null = !in_array($v,(array)@$_POST['not-null']) ? ' NULL':' NOT NULL';
            $unsigned = !in_array($v,(array)@$_POST['unsigned']) ? '':' unsigned';
            $fields[] = '`' . strtolower($v) . '` ' . $type . $unsigned . $null;
            if(in_array($v,(array)@$_POST['unique'])) $uniques[] = 'UNIQUE KEY `' . strtoupper($v) . '` (`' . strtolower($v) . '`)';
        }
        $fields[] = '`modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP';
        $sql = 'CREATE TABLE ' . $tablename . '(`id` bigint unsigned NOT NULL AUTO_INCREMENT,' . implode(',',$fields );
        $sql .= implode(',',$uniques);
        $sql .= ') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';
        $stmt = nqvDB::prepare($sql);
        $stmt->execute();
        if(empty($stmt->error)) nqvNotifications::add('La tabla ' . $tablename . ' ha sido creada con éxito','success');
        else nqvNotifications::add('Error. ' . $stmt->error, 'error');
    }
}
?>
<div class="container py-4">
    <?php getForm('create-users-db-table');?>
    <?php nqv::checkDB()?>
    <?php nqvNotifications::flush()?>
    <?php getForm($form,$args);?>
</div>