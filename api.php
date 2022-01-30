
<?php


    //require_once('customer.php');
    //require_once('JWT.php');
    require 'vendor/autoload.php';
    
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    

    class Api extends Rest {
        
        public $dbConn;
        
        public function __construct(){
            parent::__construct();

            $db = new DbConnect;
            $this->dbConn = $db->connect();
        }

        public function generateToken(){
            //print_r($this->param);//TEST print

            $email = $this->validateParameter('email', $this->param['email'], STRING);
            
            $pass = $this->validateParameter('pass', $this->param['pass'], STRING);
            
            
            try{
                /**Find the user in the DB table with email and password*/
                $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE email = :email AND password = :pass");
                
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":pass", $pass);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!is_array($user)){
                    $this->returnResponse(INVALID_USER_PASS, "Email or Password are incorrect!");
                }
                
                if ( $user['active'] == 0 ){
                    $this->returnResponse(USER_NOT_ACTIVE, "User not activated, contact admin!");
                }
                /**Skapar Nyckel, se part 7: 16:00*/
                $payload = [
                    'iat' => time(),
                    'iss' => 'localhost',
                    'exp' => time() + (1500*60),
                    'userId' => $user['id']
                ];
                /**$payload, SECRET_KEY*/
                $token = JWT::encode($payload, SECRET_KEY, 'HS256');
            
                /**echo($token);*/
                $data = ['token' => $token];
                $this->returnResponse(SUCCESS_RESPONSE, $data);

            } 
            catch (Exception $e){
                $this->throwError(JWT_PROCESSING_ERROR, $e->getMessage());
            }
        }

        public function addCustomer()
        {
            $name = $this->validateParameter('name', $this->param['name'], STRING, false);
            $email = $this->validateParameter('emai', $this->param['email'], STRING, false);
            $addr = $this->validateParameter('addr', $this->param['addr'], STRING, false);
            $mobile = $this->validateParameter('mobile', $this->param['mobile'], STRING, false);

            
            
            try {
                
                $token = $this->getBearerToken();
                
                /**did not work! */
                //$payload = JWT::decode($token, new Key(SECRET_KEY, ['HS256']), ['HS256']);
                
                /**I have to create a Key Object instead! */
                $t_key = new Key(SECRET_KEY, 'HS256');
                $payload = JWT::decode($token, $t_key);

                $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE id = :userId");
                
                $stmt->bindParam(":userId", $payload->userId);
                //$stmt->bindParam(":pass", $pass);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!is_array($user)){
                    $this->returnResponse(INVALID_USER_PASS, "This user is not found in our database!");
                }
                
                if ( $user['active'] == 0 ){
                    $this->returnResponse(USER_NOT_ACTIVE, "This user maybe deactivated, contact admin!");
                }

                print_r($payload->userId);

                //EVERYTHING OK ADD TO DATABAS
                $cust = new Customer;
                $cust->setName($name);
                $cust->setEmail($email);
                $cust->setAddress($addr);
                $cust->setMobile($mobile);
                $cust->setCreatedBy($payload->userId);
                $cust->setCreatedOn(date('Y-m-d'));

                $booStatus = true;

                if (!$cust->insert()){
                    $php_errormsg = 'Failed to insert';
                    $booStatus = false;
                }
                else{
                    $message = "Inserted succesfullt!";
                }

                $this->returnResponse(SUCCESS_RESPONSE, $message);
                
                
            }
            catch (Exception $e){
                $this->throwError(ACCES_TOKEN_ERRORS, $e->getMessage());
            }

            
        }

        

    }
?>