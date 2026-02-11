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

    public static function createUniqueSlug(string $text, string $tablename, string $separator = '-'): ?string {
        if(!self::isTable($tablename)) return null;
        $baseSlug = createSlug($text, $separator);
        $slug = $baseSlug;
        $pattern = $baseSlug . '%';

        $stmt = self::prepare('SELECT `slug` FROM ' . $tablename . ' WHERE `slug` LIKE ?');
        $stmt->bind_param('s',$pattern);
        $existing = self::parseSelect($stmt);

        // Si no existe ninguno igual, devolvemos el baseSlug
        if (!in_array($slug, array_column($existing, 'slug'))) return $slug;

        // Extraer números existentes al final del slug y calcular el siguiente
        $max = 0;
        foreach ($existing as $exist) {
            if (preg_match('/^' . preg_quote($baseSlug, '/') . $separator . '(\d+)$/', $exist['slug'], $matches)) {
                $num = (int)$matches[1];
                if ($num > $max) $max = $num;
            }
        }

        return $baseSlug . $separator . ($max + 1);
    }
}