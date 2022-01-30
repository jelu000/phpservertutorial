<?php 
/**
 *  Data
 */

 class DbConnect
 {

    private $server = 'localhost';
    private $dbname = 'jwtapi';
    private $user = 'phpservertutorial';
    private $pass = 'losenord';

     function connect(){
         
        try {
            $conn = new PDO('mysql:host=' .$this->server .';dbname=' .$this->dbname, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //echo "CONNECT db";

            return $conn;
        }
        catch (Exception $e){
            echo "Database error: " . $e->getMessage() ; 
        }
        catch (PDOException $e){
            echo "Database PDO error: " . $e->getMessage() ; 
        }
     }
 }

 //$db = new DbConnect;
 //$db->connect();

?>