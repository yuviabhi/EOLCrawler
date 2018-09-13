<?php
/**
 * 
 * @author abhisek
 *
 */

class Database
{

    protected $db_conn = null;
    
    /**
     *
     * @param resource $db_conn
     */
    public function __construct($db_conn = null) {
        $this->db_conn = $db_conn ?: self::create_database_connection ();
    }
    public function get_connection() {
        return $this->db_conn;
    }
    public function close_database() {
        pg_close ( $this->db_conn );
    }
    private static function create_database_connection() {
        $connection_string = implode ( " ", array (
            "host=" . '10.18.20.230',
            "dbname=" . 'eol_temp_db_ver1',
            "user=" . 'postgres',
            "password=" . 'postgres'
        ) );
        for($i = 0; $i < 1; $i ++) {
            $db = @pg_connect ( $connection_string, PGSQL_CONNECT_FORCE_NEW );
            if ($db !== false && pg_connection_status ( $db ) == PGSQL_CONNECTION_OK) {
                return $db;
            }
        }
        return null;
    }
}

