<?php

class nqvDB {
    private static $connection;
    private static $tablenames;
    private static $describes;

    private static bool $inTransaction = false;

    public static function getConnection() {
        try {
            if(empty(self::$connection) || !@self::$connection->ping()) self::connect();
            return self::$connection;
        } catch (mysqli_sql_exception $e) {
            throw new Exception('Error de Conexión (' . $e->getMessage() . ' ' . basename($e->getFile()) . ' ' . $e->getLine() . ') ',$e->getCode());
        }
    }

    private static function connect() {
        try {
            if(!defined('DB_HOST')) throw new Exception('Falta configurar la DB');
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            self::$connection = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
            if (self::$connection->connect_error) {
                throw new Exception('Error de Conexión (' . self::$connection->connect_errno . ') ' . self::$connection->connect_error);
            } else {
                self::$connection->set_charset('utf8mb4');
            }
            return self::$connection;
        } catch(mysqli_sql_exception $e) {
            if ($e->getCode() === 1049) {
                $msg = "'" . DB_NAME . "' no existe.";
            } else {
                $msg = $e->getMessage();
            }
            throw new Exception('Error de Conexión (' . $msg . ' ' . basename($e->getFile()) . ' ' . $e->getLine() . ') ',$e->getCode());
        }
    }

    private static function close() {
        $conn = self::getConnection();
        $conn->close();
        self::$connection = null;
    }

    public static function getTablenames($force = false) {
        if(empty(self::$tablenames) || $force) self::parseTables();
        return self::$tablenames;
    }

    protected static function parseTables(): void {
        self::$tablenames = [];
        $stmt = self::prepare('SHOW TABLES');
        $result = self::parseSelect($stmt);
        foreach($result as $table) {
            self::$tablenames[] = array_shift($table);
        }
    }

    public static function isTable($tablename) {
        if(is_array($tablename)) _log_more($tablename);
        if (!preg_match('/^[a-zA-Z0-9_]+$/',(string) $tablename)) return false;
        if(empty(self::$tablenames)) self::parseTables();
        return in_array($tablename,self::$tablenames);
    }

    public static function delete($tablename, $id) {
        if(!self::isTable($tablename)) throw new Exception($tablename . ' no es una tabla');
        $stmt = self::prepare('DELETE FROM ' . $tablename . ' WHERE `id` = ?');
        $stmt->bind_param('i',$id);
        $stmt->execute();
        return empty($stmt->error);
    }

    public static function prepare($sql): mysqli_stmt {
        try {
            self::getConnection();
            return self::$connection->prepare($sql);
        } catch (Exception $e) {
            _log_more($sql . ' ' . $e->getMessage(),'db-error');
            throw $e;
        }
    }

    public static function parseSelectAndLimit(string $sql, array $limit = []): array {
        try {
            $types = self::addLimitClause('', [], $limit)['types'];
            $vars = self::addLimitClause('', [], $limit)['vars'];
            $sql .= self::addLimitClause('', [], $limit)['limit'];
            $stmt = nqvDB::prepare($sql);
            if(!empty($types)) $stmt->bind_param($types, ...$vars);
            return self::parseSelect($stmt);
        } catch (Exception $e) {
            _log_more($e->getMessage(),'db-error');
            return [];
        }
    }

    public static function parseSelect($stmt, $type = 'array'): array {
        try {
            $output = [];
            if(!$stmt) throw new Exception('Error en la configuración de a DB');
            $stmt->execute();
            #my_print([self::$connection->error,$stmt->error]);
            $result = $stmt->get_result();
            $func = $type === 'array' ? 'fetch_assoc':'fetch_object';
            while ($row = $result->$func()) {
                $output[] = $row;
            }
            //self::close();
            return $output;
        } catch (Exception $e) {
            _log($e->getMessage(),'db-error');
            return [];
        }
    }

    public static function select($sql) {
        self::getConnection();
        $output = [];
        $result = self::$connection->query($sql);
        if(is_object($result)) {
            while ($row = $result->fetch_assoc()) {
                $output[] = $row;
            }
        } else {
            $output = $result;
        }
        return $output;
    }

    public static function insert($stmt) {
        if($stmt->execute()) return $stmt->insert_id;
        else return 0;
    }

    protected static function addLimitClause(string $types, array $vars, array $limit=[]) {
        $q = [];
        $limitString = '';
        foreach($limit as $l) {
            $q[] = '?';
            $types .= 'i';
            $vars[] = $l;
            $limitString = ' LIMIT ' . implode(',',$q);
        }
        return ['types' => $types, 'vars' => $vars, 'limit' => $limitString];
    }

    public static function describe($tablename) {
        if(empty(self::$describes[$tablename])) {
            if(!self::isTable($tablename)) return null;
            self::getConnection();
            $query = self::$connection->query('describe ' . $tablename);
            while($r = $query->fetch_assoc()) {
                self::$describes[$tablename][$r['Field']] = $r;
            }
        }
        return (array) @self::$describes[$tablename];
    }

    public static function getFieldNames($tablename) {
        $output = [];
        foreach((array) self::describe($tablename) as $r) {
            $output[] = $r['Field'];
        }
        return $output;
    }

    public static function isField($fieldname,$tablename) {
        return in_array($fieldname,self::getFieldNames($tablename));
    }

    public static function checkTable($tablename) {
        if(!self::isTable($tablename)) throw new Exception($tablename . ' no es una tabla');
    }

    public static function getObject(string $tablename, array $vars = []) {
        try {
            $c = 'nqv' . ucfirst($tablename);
            $o = new $c($vars);
            return $o;
        } catch(Exception $e) {
            _log($e->getMessage(),'getObject-error');
            return null;
        }
    }

    /*
    TODO / PENDIENTES DE REFACTORIZACIÓN

    - Normalizar el contrato de retorno del método:
    * La firma indica `: int` pero actualmente se devuelven int, bool y resultados de notifications.
    * Definir claramente: ID | bool | excepción.

    - Separar responsabilidades:
    * Extraer validaciones de campos a una capa previa (validator).
    * Extraer lógica de persistencia (SQL) de lógica de negocio.

    - Revisar manejo de errores:
    * Unificar estrategia (exceptions vs returns).
    * Evitar capturar Exception para luego devolver valores ambiguos.

    - Manejar transacciones:
    * Envolver save + parseImagesFilesUpload en una transacción DB.
    * Definir rollback si falla el procesamiento de imágenes.

    - Convertir parseImagesFilesUpload en hook post-save:
    * Evitar acoplar uploads directamente al método save().
    * Permitir reutilizar save() sin efectos colaterales.

    - Revisar lógica de created_by:
    * No depender de que el campo venga en $vars.
    * Setear automáticamente en insert si existe el campo en la tabla.

    - Validar caso de $set vacío:
    * Prevenir SQL inválido (INSERT/UPDATE sin campos).

    - Simplificar y deduplicar condiciones:
    * created_by chequeado dos veces en versiones previas.
    * chequeos redundantes de $stmt->error.

    - Documentar claramente el flujo:
    * insert vs update
    * cuándo se procesan imágenes
    * qué se espera que contenga $vars
    */

    public static function save($tablename, $vars): int {
        try {
            $table = new nqvDbTable($tablename);
            $fields = $table->getTableFields();
            $saveType = empty($vars['id']) ? 'insert':'update';
            $types = '';
            $vs = [];
            $set = [];
            try {
                foreach($fields as $field) {
                    $k = $field['Field'];
                    if($k === 'created_by') {
                        if($saveType === 'insert') $vars[$k] = nqv::getCurrentUser()->get('id');
                        else continue;
                    }
                    if(!isset($vars[$k])) continue;
                    if($k === 'created_at') continue;
                    if($k === 'id') continue;
                    if($k === 'repass') continue;

                    if($field['Null'] === 'NO' && empty($vars[$k])) throw new Exception($k . ' no puede estra vacío');
                    if($k === 'email' && empty(filter_var($vars[$k],FILTER_VALIDATE_EMAIL))) throw new Exception($k . ' no es correcto');
                    if($k === 'password' || $k === 'passphrase') {
                        if(!empty($vars['repass'])) {
                            if(empty($vars[$k])) {
                                if(!empty($vars['id'])) continue;
                                else throw new Exception('La contraseña no es correcta.');
                            }
                            if($vars['repass'] !== $vars[$k]) throw new Exception('Las contraseñas no coinciden.');
                        }
                        if(!is_encrypted($vars[$k])) $vars[$k] = set_password_value($vars[$k]);
                    }
                    $fs = new nqvDbField($field,$tablename);
                    $vs[] = $vars[$k];
                    $types .= $fs->getTypeLetter();
                    $set[] = '`' . $k . '` = ?';
                }
                $sql = empty($vars['id']) ? 'INSERT INTO ':'UPDATE ';
                $sql .= $tablename .' SET ' . implode(', ',$set);
                if(!empty($vars['id'])) {
                    $sql .= ' WHERE id = ?';
                    $types .= 'i';
                    $vs[] = $vars['id'];
                }
                $stmt = nqvDB::prepare($sql);
                $stmt->bind_param($types,...$vs);
                $stmt->execute();
                if(empty($stmt->error)) {
                    if(empty($vars['id'])) {    
                        nqv::parseImagesFilesUpload($tablename,$stmt->insert_id);
                        return $stmt->insert_id;
                    } else {
                        nqv::parseImagesFilesUpload($tablename,$vars['id']);
                        return true;
                    }
                } else return 0;
            } catch(Exception $e) {
                return nqvNotifications::add($e->getMessage(),'error');
            }
        } catch(Exception $e) {
            throw new Exception($e->getLine() . ' ' . $e->getMessage());
        }
    }

    public static function beginTransaction() {
        self::getConnection();
        self::$connection->begin_transaction();
        self::$inTransaction = true;
        return self::$connection;
    }

    public static function query(string $sql) {
        $result = self::$connection->query($sql);
        if ($result === false) {
            _log('Query failed: ' . self::$connection->error . " | SQL: $sql", 'sql-error');
            throw new Exception('Error en la consulta a la DB');
        }
        return $result;
    }

    public static function commit() {
        self::$connection->commit();
        self::$inTransaction = false;
    }

    public static function rollback() {
        if(!self::$connection) throw new Exception('La conexión con la DB no es válida');
        if (self::$inTransaction) {
            self::$connection->rollback();
            self::$inTransaction = false;
        } else {
            throw new Exception('La transacción no es válida o nunca se inició');
        }
    }

    public static function exportDatabase( array $excludeTables = [], string $type = 'full'): string {

        self::getConnection();

        if (!in_array($type, ['full','structure','data'])) throw new Exception('Tipo de exportación inválido: ' . $type);

        $filename = nqvBackup::buildFilename(nqvBackup::CATEGORY_DATABASE, $type, 'sql');
        $outputFile = BKP_PATH . $filename;

        $excludeLookup = array_flip(
            array_filter($excludeTables, fn($t) => self::isTable($t))
        );

        $handle = fopen($outputFile, 'w');
        if (!$handle) throw new Exception('No se pudo crear el archivo.');
        $write = fn($text) => fwrite($handle, $text);

        $write("-- Export generado el " . date('Y-m-d H:i:s') . "\n");
        $write("-- Base de datos: `" . DB_NAME . "`\n\n");
        $write("SET FOREIGN_KEY_CHECKS=0;\n\n");

        $tables = self::getTablenames(true);

        foreach ($tables as $table) {

            if (isset($excludeLookup[$table])) continue;

            self::checkTable($table);

            /* =========================
            ESTRUCTURA
            ========================== */
            if ($type === 'full' || $type === 'structure') {

                $write("DROP TABLE IF EXISTS `$table`;\n");

                $create = self::select("SHOW CREATE TABLE `$table`");

                if (!empty($create[0]['Create Table'])) {
                    $write($create[0]['Create Table'] . ";\n\n");
                }
            }

            /* =========================
            DATOS
            ========================== */
            if ($type === 'full' || $type === 'data') {

                $rows = self::select("SELECT * FROM `$table`");

                if (!empty($rows)) {

                    foreach ($rows as $row) {

                        $columns = [];
                        $values  = [];

                        foreach ($row as $col => $value) {

                            $columns[] = "`$col`";

                            if (is_null($value)) {
                                $values[] = "NULL";
                            } else {
                                $escaped = self::$connection->real_escape_string($value);
                                $values[] = "'$escaped'";
                            }
                        }

                        $write(
                            "INSERT INTO `$table` (" .
                            implode(',', $columns) .
                            ") VALUES (" .
                            implode(',', $values) .
                            ");\n"
                        );
                    }

                    $write("\n");
                }
            }
        }

        $write("SET FOREIGN_KEY_CHECKS=1;\n");

        fclose($handle);
        return $outputFile;
    }

    public static function importDatabase(string $filePath, string $mode = 'replace'): bool {
        if (!file_exists($filePath)) throw new Exception('El archivo no existe.');

        if (!in_array($mode, ['replace', 'truncate', 'append'])) throw new Exception('Modo de importación inválido.');

        self::getConnection();

        $sql = file_get_contents($filePath);

        if ($sql === false) throw new Exception('No se pudo leer el archivo.');

        self::$connection->begin_transaction();

        try {

            self::$connection->query("SET FOREIGN_KEY_CHECKS=0");

            if ($mode === 'truncate') {
                foreach (self::getTablenames(true) as $table) self::$connection->query("TRUNCATE TABLE `$table`");
            }

            if ($mode === 'append') {
                // eliminamos DROP TABLE del dump
                $sql = preg_replace('/DROP TABLE IF EXISTS .*?;/i', '', $sql);
            }

            if ($mode === 'truncate') {
                // eliminamos DROP + CREATE del dump
                $sql = preg_replace('/DROP TABLE IF EXISTS .*?;/i', '', $sql);
                $sql = preg_replace('/CREATE TABLE .*?;\n\n/s', '', $sql);
            }

            // Ejecutar múltiples queries
            if (!self::$connection->multi_query($sql)) {
                throw new Exception(self::$connection->error);
            }

            // Consumir resultados
            do {
                if ($result = self::$connection->store_result()) {
                    $result->free();
                }
            } while (self::$connection->more_results() && self::$connection->next_result());

            self::$connection->query("SET FOREIGN_KEY_CHECKS=1");

            self::$connection->commit();

            return true;

        } catch (Exception $e) {

            self::$connection->rollback();
            throw $e;
        }
    }

    public static function resolveServerBackup(string $filename): string {
        if(empty($filename)) throw new Exception('No se indicó archivo.');

        $filePath = realpath(BKP_PATH . '/' . $filename);

        if(
            !$filePath ||
            !str_starts_with($filePath, realpath(BKP_PATH)) ||
            pathinfo($filePath, PATHINFO_EXTENSION) !== 'sql'
        ) throw new Exception('Archivo inválido.');

        return $filePath;
    }

    public static function resolveUploadedBackup(array $file): string {

        if(empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE)
            throw new Exception('No se subió ningún archivo.');

        if($file['error'] !== UPLOAD_ERR_OK)
            throw new Exception('Error en la subida.');

        if(pathinfo($file['name'], PATHINFO_EXTENSION) !== 'sql')
            throw new Exception('Solo se permiten archivos .sql.');

        $tmpPath = sys_get_temp_dir() . '/' . uniqid('import_',true) . '.sql';

        if(!move_uploaded_file($file['tmp_name'],$tmpPath))
            throw new Exception('No se pudo procesar el archivo subido.');

        return $tmpPath;
    }

    public static function importFromRequest(string $mode = 'replace'): bool {

        $serverFile = $_POST['server_file'] ?? null;
        $uploadFile = $_FILES['upload_file'] ?? null;

        if(
            empty($serverFile) &&
            (empty($uploadFile) || $uploadFile['error'] === UPLOAD_ERR_NO_FILE)
        ) throw new Exception('Debe seleccionar o subir un archivo.');

        if(
            !empty($serverFile) &&
            !empty($uploadFile) &&
            $uploadFile['error'] !== UPLOAD_ERR_NO_FILE
        ) throw new Exception('Seleccione solo una opción.');

        $isUpload = false;

        if(!empty($serverFile)) {
            $filePath = self::resolveServerBackup($serverFile);
        } else {
            $filePath = self::resolveUploadedBackup($uploadFile);
            $isUpload = true;
        }

        try {

            $result = self::importDatabase($filePath,$mode);

            if($isUpload && file_exists($filePath)) unlink($filePath);

            return $result;

        } catch(Exception $e) {

            if($isUpload && file_exists($filePath)) unlink($filePath);

            throw $e;
        }
    }

    private static function buildBackupFilename(string $category = 'db', string $type = 'full', string $extension = 'sql'): string {

        if(!in_array($type,['full','structure','data']))
            throw new Exception('Tipo inválido para nombre de backup.');

        $project = defined('APP_NAME') ? APP_NAME : 'ovo';
        $timestamp = date('Ymd_His');

        return sprintf(
            '%s_%s_%s_%s.%s',
            $project,
            $category,
            $type,
            $timestamp,
            $extension
        );
    }

}