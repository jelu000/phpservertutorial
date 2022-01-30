<?php 
    
    require_once('constants.php');
    require_once('customer.php');
    require_once('DbConnect.php');
    
    
    

    class Rest {
    
        protected $request;
        protected $serviceName;
        protected $param;

    public function __construct(){
           
        if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    
               $this->throwError(REQUEST_METHOD_NOT_VALID, 'Request method is not valid, must be POST');
           }
           
           $handler = fopen("php://input", "r");
           $this->request = stream_get_contents($handler);
           $this->validateRequest($this->request);
           //echo $request;

       }
       
       public function validateRequest($request){

            if ($_SERVER['CONTENT_TYPE'] !== 'application/json'){
                $this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request content-type not valid, must be application/json');
            }

            $data = json_decode($this->request, true);

            //print_r($data);

            if (!isset($data['name'])  || $data['name'] == ""){
                $this->throwError(API_NAME_REQUIRED, "Api name required.");
            }
            $this->serviceName = $data['name'];

            if ( !is_array($data['param']) ){
                $this->throwError(API_PARAM_REQUIRED, "Api parameters required.");
            }
            $this->param = $data['param'];

            if ( !isset($data['name']) || $data['name']=="" ){
                $this->throwError(API_NAME_REQUIRED, "API name is required.");
            }
            $this->serviceName = $data['name'];

            if ( !is_array($data['param']) ){
                $this->throwError(API_PARAM_REQUIRED, "API param is required.");
            }
            $this->param = $data['param'];
            

       }

        public function processApi(){
            $api = new Api;
            $rMethod = new ReflectionMethod('Api', $this->serviceName);//WHY NOT TRY - CATCH?

            if (!method_exists($api, $this->serviceName)){
                $this->throwError(API_DOST_NOT_EXIST, "API dos not exist!");   
            }
            $rMethod->invoke($api);
            


        }

        public function validateParameter($fieldname, $value, $dataType, $required = true){
            //Validerar parametrar som är oblikatorisk och krävs
            if ($required == true && empty($value) == true){
                $this->throwError(VALIDATE_PARAMETER_REQUIRED, $fieldname . "Parameter is Required!");
            }

            switch ($dataType) {
                case BOOLEAN:
                    if (!is_bool($value)){
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid, must be boolean!" . $fieldname );
                    }
                    break;
                case INTEGER:
                    if (!is_numeric($value)){
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid, must be numeric!" . $fieldname );
                    }
                    break;
                case STRING:
                    if (!is_string($value)){
                        $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid,  must be string!" . $fieldname );
                }
                break;
                default:
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for " . $fieldname);
                break;
            }

            return $value;

        }

        public function throwError($code, $message ){
            header("content-type: application/json");

            $errorMsg = json_encode(['error' => ['status'=>$code, 'message'=>$message]]);
            echo $errorMsg, exit;

        }

        public function returnResponse($code, $data){
            header("content-type: application/json");
            $response = json_encode(['response' => ['status' => $code, "result" => $data ]]);

            echo $response; exit;

        }


        /** -----------------------------------------------------------------------------------------------------------------------------
         * Get header Authorization -https://stackoverflow.com/questions/40582161/how-to-properly-use-bearer-tokens
         * */
        public function getAuthorizationHeader(){
            $headers = null;
            if (isset($_SERVER['Authorization'])) {
                $headers = trim($_SERVER["Authorization"]);
            }
            else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
                $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
            } elseif (function_exists('apache_request_headers')) {
                $requestHeaders = apache_request_headers();
                // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                //print_r($requestHeaders);
                if (isset($requestHeaders['Authorization'])) {
                    $headers = trim($requestHeaders['Authorization']);
                }
            }
            return $headers;
        }
        /**
         * get access token from header
         * */
        public function getBearerToken() {
                       //getAuthorizationHeader()
            $headers = $this->getAuthorizationHeader();
            // HEADER: Get the access token from the header
            if (!empty($headers)) {
                if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                    return $matches[1];
                }
            }
            return null;
        }



    }
?>