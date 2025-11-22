<?php
namespace App\Core;

/**
 * Clase base para todos los modelos
 */
abstract class Model {
    protected $db;
    protected $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }
}

