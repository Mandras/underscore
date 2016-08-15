<?php

class mysql {
    private static $mysql;
    public static $execution_time = 0;

    public static function connect($database = false) {

        if (!$database) $database = _::$mysql_database;

        if (empty(_::$mysql_host)) error(500, "Variable de configuration absente: mysql_host");
        if (empty(_::$mysql_username)) error(500, "Variable de configuration absente: mysql_username");
        if (empty(_::$mysql_database) && empty($database)) error(500, "Variable de configuration absente: mysql_database");

        self::$mysql = @mysqli_connect(_::$mysql_host, _::$mysql_username, _::$mysql_password, $database);

        if (!self::$mysql) error(500, "Impossible de se connecter au serveur MYSQL " . $database);

        self::query("SET NAMES 'utf8'"); 
    }

    public static function is_connect() { if (self::$mysql) return (true); else return (false); }

    public static function close() { if (self::$mysql) mysqli_close(self::$mysql); }

    public static function escape($string) { return (mysqli_real_escape_string(self::$mysql, $string)); }

    public static function row($query) { return (mysqli_fetch_assoc($query)); }

    public static function tab($query) {
        for ($tab = array() ; $row = self::row($query) ; ) { $tab []= $row; }
        return ($tab);
    }

    public static function query($query) {
        if (!self::is_connect()) error(400, "Trying to query something but you aren't connected");
        if (_::$environment == DEV) {
            $time_start = microtime(true);
            $return = mysqli_query(self::$mysql, $query) or error(400, self::$mysql->error . "<br /><pre>" . $query."</pre>");
            self::$execution_time += (microtime(true) - $time_start);
            return ($return);
        }
        return (mysqli_query(self::$mysql, $query) or error(400, self::$mysql->error));
    }

    public static function last_id() { return (mysqli_insert_id(self::$mysql)); }
}

?>