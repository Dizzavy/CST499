<?php
class Connect {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "cst499";
    public $con;

    public function __construct() {
        $this->con = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->con->connect_error) {
            die("Connection failed: " . $this->con->connect_error);
        }
    }

    // Prepared SELECT query
    public function executePreparedSelect($sql, $params = []) {
        $stmt = $this->con->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . $this->con->error);
        }
    
        if (!empty($params)) {
            $types = "";
            foreach ($params as $param) {
                $types .= is_int($param) ? "i" : (is_float($param) ? "d" : "s");
            }
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) return null;
    
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    
        return $data;
    }
    
}
?>
