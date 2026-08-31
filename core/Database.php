<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = require CONFIG_PATH . '/database.php';
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
        $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
