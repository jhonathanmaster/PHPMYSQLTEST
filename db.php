<?php
ini_set("display_errors",1);
include_once __DIR__ . '/app_connect.php';


class DB {

    // Propiedades
	private $motorDB;
    private $ServerName;
    private $UserName;
    private $Password;
    private $DbName;
    /**
     * @var PDO Conexion de bd
     **/
    private $conn;

    public function __construct() {
        global $motorDB, $ServerName, $UserName, $Password, $DbName;
		
        $this->motorDB = $motorDB;
        $this->ServerName = $ServerName;
        $this->UserName = $UserName;
        $this->Password = $Password;
        $this->DbName = $DbName;
    }
	
    public function connect() {
        $this->conn = new PDO($this->motorDB.':dbname=' . $this->DbName .
                ';host=' . $this->ServerName, $this->UserName, $this->Password);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function selectRows($sql, $paramters = array()) {
        $sth = $this->conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if(!empty($paramters)){
            $sth->execute($paramters);
        }else{
            $sth->execute();
        }
        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function selectRow($sql, $paramters = array()) {
        $result = $this->selectRows($sql,$paramters);
        return ((empty($result[0]))?null:$result[0]);
    }
    
    public function execute($sql, $parameters = array()){
        $sth = $this->conn->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        if(!empty($parameters)){
            $execute = $sth->execute($parameters);
        }else{
            $execute = $sth->execute();
        }
        return $execute;
    }

}
