<?php

namespace NE\System {

    use PDO;

    /**
     * PDO singleton
     * 8/30/15 6:45 PM
     * @author elchin
     */
    class DPDO {

        const BOOL = 'bool';
        const BOOLEAN = 'boolean';
        const DOUBLE = 'double';
        const FLOAT = 'float';
        const DECIMAL = 'decimal';
        const BIGINT = 'bigint';
        const INT = 'int';
        const TINYINT = 'tinyint';
        const SMALLINT = 'smallint';
        const MEDIUMINT = 'mediumint';
        const ENUM = 'enum';
        const CHAR = 'char';
        const VARCHAR = 'varchar';
        const LONGTEXT = 'longtext';
        const DATETIME = 'datetime';
        const TEXT = 'text';
        const STRING = 'text';
        const DATE = 'date';
        const TIMESTAMP = 'timestamp';
        const TINYTEXT = 'tinytext';
        const MEDIUMTEXT = 'mediumtext';
        const TIME = 'time';
        const SET = 'set';
        const VARBINARY = 'varbinary';
        const BLOB = 'blob';
        const LONGBLOB = 'longblob';

        static private $instance;
        static private $statement;
        static private $sqlLog = [];
        static public $lastSql;
        static public $bindedLog;
        static private $LOG_SQL = false;

        /**
         * Connect and create PDO singleton
         * @param string $host DB server host
         * @param string $user DB user name
         * @param string $pass DB user's password
         * @param string $db DB to use
         * @param string $persistency
         * @param string $charset Connection charset
         * @param int $LOG_SQL value can be: <br />1-log into an internal variable,<br />2-log into file,<br />3-log errors,false-don't log
         */
        public static function connect($host, $user, $pass, $db, $charset = 'utf8', $LOG_SQL = 1) {
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $opt = array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode=""'
            );
            self::$instance = new PDO($dsn, $user, $pass, $opt);
            self::$LOG_SQL = $LOG_SQL;
        }

        public static function logSql($sql) {
            switch (self::$LOG_SQL) {
                case 1:
                    $binded = '';
                    if (count(self::$bindedLog)) {
                        foreach (self::$bindedLog as $k => $v) {
                            $binded .= "\n\t\t" . $k . ' => ' . $v;
                        }
                    }
                    self::$bindedLog = [];
                    array_push(self::$sqlLog, $sql . $binded);
                    break;
            }
        }

        public static function getLog() {
            return self::$sqlLog;
        }

        /**
         * Get PDO instance
         * @return type
         */
        public static function getInstance() {
            return self::$instance;
        }

        /**
         * Prepares a statement for execution and returns a statement object
         * @param string $sql This must be a valid SQL statement template for the target database server.
         * @param array $driver_options This array holds one or more key=>value pairs to set attribute values for the PDOStatement object that this method returns.
         * @return PDOStatement
         * @link http://php.net/manual/en/pdo.prepare.php
         */
        public static function prepare($sql, array $driver_options = []) {
            self::$lastSql = $sql;
            return self::$statement = self::$instance->prepare($sql, $driver_options);
        }

        /**
         * Executes a prepared statement
         * @param array $input_parameters An array of values with as many elements as there are bound parameters in the SQL statement being executed.
         * @param object $statement PDOStatement
         */
        public static function execute(array $input_parameters = [], $statement = false) {
            self::logSql(self::$lastSql);
            //printr(self::getLog());
            $c = count($input_parameters);
            if ($statement !== false) {
                if ($c) {
                    $statement->execute($input_parameters);
                } else {
                    $statement->execute();
                }
            } else {
                if ($c) {
                    self::$statement->execute($input_parameters);
                } else {
                    self::$statement->execute();
                }
            }
        }

        public static function executeAndGetFirst($sql, array $input_parameters = [], $fetch_style = PDO::FETCH_ASSOC, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
            $statement = self::prepare($sql);
            self::execute($input_parameters, $statement);
            return self::fetch($statement, $fetch_style, $cursor_orientation, $cursor_offset);
        }

        public static function debugDumpParams() {
            self::$statement->debugDumpParams();
        }

        /**
         * Returns the ID of the last inserted row or sequence value
         * @return integer
         */
        public static function lastInsertId() {
            return self::$instance->lastInsertId();
        }

        /**
         * Fetches the next row from a result set
         * @param object $statement PDOStatement
         * @param int $fetch_style Controls how the next row will be returned to the caller.
         * @param int $cursor_orientation For a PDOStatement object representing a scrollable cursor, this value determines which row will be returned to the caller.
         * @param int $cursor_offset For a PDOStatement object representing a scrollable cursor for which the cursor_orientation parameter is set to PDO::FETCH_ORI_ABS, this value specifies the absolute number of the row in the result set that shall be fetched.
         * @return array
         * The return value of this function on success depends on the fetch type. In all cases, FALSE is returned on failure.
         * @link http://php.net/manual/en/pdostatement.fetch.php description
         */
        public static function fetch($statement = false, $fetch_style = PDO::FETCH_ASSOC, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0) {
            if ($statement !== false) {
                return $statement->fetch($fetch_style, $cursor_orientation, $cursor_offset);
            } else {
                return self::$statement->fetch($fetch_style, $cursor_orientation, $cursor_offset);
            }
        }

        /**
         * Binds a parameter to the specified variable name
         * @param string $column variable name
         * @param type $data variable value
         * @param string $type data type <br/>
         * bool<br/>
         * boolean<br/>
         * double<br/>
         * float<br/>
         * decimal<br/>
         * bigint<br/>
         * int<br/>
         * tinyint<br/>
         * smallint<br/>
         * mediumint<br/>
         * enum<br/>
         * char<br/>
         * varchar<br/>
         * longtext<br/>
         * datetime<br/>
         * text<br/>
         * date<br/>
         * timestamp<br/>
         * tinytext<br/>
         * mediumtext<br/>
         * time<br/>
         * set<br/>
         * varbinary<br/>
         * blob<br/>
         * longblob
         * @param PDOStatement $statement PDOStatement
         */
        public static function bindParam($column, $data, $type, $statement = false) {
            self::$bindedLog[$column] = gain::subStringByLastNBSP($data, 200, 50, '...');
            if (!$statement)
                $statement = &self::$statement;
            switch ($type) {
                case 'bool':
                case 'boolean':
                    $statement->bindParam(':' . $column, $data, PDO::PARAM_BOOL);
                    break;
                case 'double':
                case 'float':
                case 'decimal':
                    $statement->bindParam(':' . $column, $data, PDO::PARAM_STR);
                    break;
                case 'bigint':
                case 'int':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                    $statement->bindParam(':' . $column, $data, PDO::PARAM_INT);
                    break;
                case 'enum':
                case 'char':
                case 'varchar':
                case 'longtext':
                case 'datetime':
                case 'text':
                case 'date':
                case 'timestamp':
                case 'tinytext':
                case 'mediumtext':
                case 'time':
                case 'set':
                    $statement->bindParam(':' . $column, $data, PDO::PARAM_STR);
                    break;
                case 'varbinary':
                case 'blob':
                case 'longblob':
                    $statement->bindParam(':' . $column, $data, PDO::PARAM_LOB);
                    break;
            }
        }

        /**
         * Binds a parameters to the specified variable names in schema
         * @param array $schema schema array
         * @param array $data associated data array array('var_name'=>'var_value')
         * @param bool $bindOnlyDataArray bind only vars assigned in array, other vars in schema will not binded
         * @param PDOStatement $statement PDOStatement
         */
        public static function bindParams($schema, array $data, $bindOnlyDataArray = false, $statement = false) {
            if (!$statement) {
                $statement = &self::$statement;
            }
            foreach ($schema as $column => $v) {
                if ($column != ':keys' && (!$bindOnlyDataArray || isset($data[$column]))) {
                    if (is_array($data[$column])) {
                        foreach ($data[$column] as $d_k => $d_v) {
                            self::bindParam($column . $d_k, $d_v, $v['type'], $statement);
                        }
                    } else {
                        self::bindParam($column, $data[$column], $v['type'], $statement);
                    }
                }
            }
        }

        /**
         * Get table schema. Generate cache if not exist.
         * @param string $table table name
         * @param bool $renew renew cache
         * @return array return schema array
         */
        public static function schema($table, $renew = false) {
            gain::mkdir('schemas');
            $file = 'schemas/' . $table . '.schema.php';
            if (file_exists($file) && !$renew) {
                return require $file;
            } else {
                self::prepare("DESCRIBE $table");
                self::execute();
                $result = [];
                while ($row = self::fetch()) {
                    $field = [];
                    $i = strpos($row['Type'], '(');
                    if ($i === false) {
                        $field['type'] = $row['Type'];
                        $field['params'] = null;
                    } else {
                        $field['type'] = substr($row['Type'], 0, $i);
                        $field['params'] = substr($row['Type'], $i + 1, strpos($row['Type'], ')') - $i - 1);
                        if (strpos($row['Type'], ')') != strlen($row['Type']) - 1) {
                            $field[trim(substr($row['Type'], strpos($row['Type'], ')') + 1))] = 1;
                        }
                    }
                    if ($row['Null'] != 'YES') {
                        $field['null'] = 0;
                    }

                    if ($row['Default'] !== null) {
                        $field['default'] = $row['Default'];
                    }

                    if ($row['Extra'] == 'auto_increment') {
                        $field['autoincrement'] = 1;
                    }
                    $result[$row['Field']] = $field;
                }
                $stmt = self::$instance->prepare("SHOW INDEX FROM $table");
                $stmt->execute();
                $rows = [];
                while ($row = $stmt->fetch()) {
                    if ($row['Sub_part']) {
                        $f = array($row['Column_name'], $row['Sub_part']);
                    } else {
                        $f = $row['Column_name'];
                    }
                    if (isset($rows[$row['Key_name']])) {
                        $rows[$row['Key_name']]['fields'][] = $f;
                    } else {
                        $rows[$row['Key_name']] = array(
                            'fields' => array($f)
                        );
                        if ($row['Key_name'] != 'PRIMARY' && !$row['Non_unique']) {
                            $rows[$row['Key_name']]['unique'] = 1;
                        }
                        if ($row['Index_type'] == 'FULLTEXT') {
                            $rows[$row['Key_name']]['fulltext'] = 1;
                        }
                    }
                }
                $result[':keys'] = $rows;
                file_put_contents($file, '<?php return ' . var_export($result, true) . '?>');
                return $result;
            }
        }

        /**
         * Disconnect PDO
         */
        public static function disconnect() {
            self::$instance = null;
        }

    }

}