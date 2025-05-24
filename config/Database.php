<?php
class Database {
    private $host = "aws-0-ap-southeast-1.pooler.supabase.com";
    private $port = "5432";
    private $db_name = "postgres";
    private $username = "postgres.kiiawuqvluzcrggkjmyt";
    private $password = "elkansai1251"; // Replace with your actual password
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "pgsql:host=" . $this->host . 
                   ";port=" . $this->port . 
                   ";dbname=" . $this->db_name;
            
            error_log("Attempting to connect to database with DSN: " . $dsn);
            error_log("Username: " . $this->username);
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            error_log("Database connection successful");
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            error_log("Connection details - Host: " . $this->host . ", Port: " . $this->port . ", DB: " . $this->db_name);
            echo "Connection Error: " . $e->getMessage();
            return null;
        }
    }

    public function query($table, $method = 'GET', $params = []) {
        if (!$this->conn) {
            $this->getConnection();
        }

        try {
            switch ($method) {
                case 'GET':
                    $sql = "SELECT * FROM " . $table;
                    if (!empty($params)) {
                        $conditions = [];
                        foreach ($params as $key => $value) {
                            if (strpos($value, 'eq.') === 0) {
                                $value = substr($value, 3);
                                $conditions[] = "$key = :$key";
                            }
                        }
                        if (!empty($conditions)) {
                            $sql .= " WHERE " . implode(' AND ', $conditions);
                        }
                    }
                    $stmt = $this->conn->prepare($sql);
                    if (!empty($params)) {
                        foreach ($params as $key => $value) {
                            if (strpos($value, 'eq.') === 0) {
                                $value = substr($value, 3);
                                $stmt->bindValue(":$key", $value);
                            }
                        }
                    }
                    $stmt->execute();
                    return $stmt->fetchAll();
                    break;

                case 'POST':
                    $columns = implode(', ', array_keys($params));
                    $values = ':' . implode(', :', array_keys($params));
                    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
                    $stmt = $this->conn->prepare($sql);
                    foreach ($params as $key => $value) {
                        $stmt->bindValue(":$key", $value);
                    }
                    $stmt->execute();
                    return $this->conn->lastInsertId();
                    break;

                case 'PUT':
                    $updates = [];
                    foreach ($params as $key => $value) {
                        if ($key !== 'id') {
                            $updates[] = "$key = :$key";
                        }
                    }
                    $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE id = :id";
                    $stmt = $this->conn->prepare($sql);
                    foreach ($params as $key => $value) {
                        $stmt->bindValue(":$key", $value);
                    }
                    return $stmt->execute();
                    break;

                case 'DELETE':
                    $sql = "DELETE FROM $table WHERE id = :id";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindValue(':id', $params['id']);
                    return $stmt->execute();
                    break;

                default:
                    throw new Exception("Unsupported method: $method");
            }
        } catch(Exception $e) {
            error_log("Query Error: " . $e->getMessage());
            return null;
        }
    }

    public function __destruct() {
        $this->conn = null;
    }
}