<?php
include_once('recurring.php');
include_once('firebase/php-jwt/src/JWT.php');
include_once('firebase/php-jwt/src/SignatureInvalidException.php');
include_once('firebase/php-jwt/src/BeforeValidException.php');
include_once('firebase/php-jwt/src/ExpiredException.php');

use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;

class IPN {
    // Temporary 
    public $logFile;

    public $posSignatureSet;
    public $hashMethod;
    public $alg;
    public $publicKeyStr;

    // Error code defination
    const E_VERIFICATION_FAILED_GENERAL			= 0x10000101;
    const E_VERIFICATION_FAILED_SIGNATURE		= 0x10000102;
    const E_VERIFICATION_FAILED_NBF_IAT			= 0x10000103;
    const E_VERIFICATION_FAILED_EXPIRED			= 0x10000104;
    const E_VERIFICATION_FAILED_AUDIENCE		= 0x10000105;
    const E_VERIFICATION_FAILED_TAINTED_PAYLOAD	= 0x10000106;
    const E_VERIFICATION_FAILED_PAYLOAD_FORMAT	= 0x10000107;

    public const ERROR_TYPE_NONE 		= 0x00;
    public const ERROR_TYPE_TEMPORARY 	= 0x01;
    public const ERROR_TYPE_PERMANENT 	= 0x02;

	

    const RECURRING_ERROR_CODE_NEED_VERIFY  = 0x200; // Need Verify Recurring API Key

    /**
     * available statuses for the purchase class (prcStatus)
     */
    const STATUS_NEW 									= 1;	//0x01; //new purchase status
    const STATUS_OPENED 								= 2;	//OK //0x02; // specific to Model_Purchase_Card purchases (after preauthorization) and Model_Purchase_Cash
    const STATUS_PAID 									= 3;	//OK //0x03; // capturate (card)
    const STATUS_CANCELED 								= 4;	//0x04; // void
    const STATUS_CONFIRMED 								= 5;	//OK //0x05; //confirmed status (after IPN)
    const STATUS_PENDING 								= 6;	//0x06; //pending status
    const STATUS_SCHEDULED 								= 7;	//0x07; //scheduled status, specific to Model_Purchase_Sms_Online / Model_Purchase_Sms_Offline
    const STATUS_CREDIT 								= 8;	//0x08; //specific status to a capture & refund state
    const STATUS_CHARGEBACK_INIT 						= 9;	//0x09; //status specific to chargeback initialization
    const STATUS_CHARGEBACK_ACCEPT 						= 10;	//0x0a; //status specific when chargeback has been accepted
    const STATUS_ERROR 									= 11;	//0x0b; // error status
    const STATUS_DECLINED 								= 12;	//0x0c; // declined status
    const STATUS_FRAUD 									= 13;	//0x0d; // fraud status
    const STATUS_PENDING_AUTH 							= 14;	//0x0e; //specific status to authorization pending, awaiting acceptance (verify)
    const STATUS_3D_AUTH 								= 15;	//0x0f; //3D authorized status, speficic to Model_Purchase_Card
    const STATUS_CHARGEBACK_REPRESENTMENT 				= 16;	//0x10;
    const STATUS_REVERSED 								= 17;	//0x11; //reversed status
    const STATUS_PENDING_ANY 							= 18;	//0x12; //dummy status
    const STATUS_PROGRAMMED_RECURRENT_PAYMENT 			= 19;	//0x13; //specific to recurrent card purchases
    const STATUS_CANCELED_PROGRAMMED_RECURRENT_PAYMENT 	= 20;	//0x14; //specific to cancelled recurrent card purchases
    const STATUS_TRIAL_PENDING							= 21;	//0x15; //specific to Model_Purchase_Sms_Online; wait for ACTON_TRIAL IPN to start trial period
    const STATUS_TRIAL									= 22;	//0x16; //specific to Model_Purchase_Sms_Online; trial period has started
    const STATUS_EXPIRED								= 23;	//0x17; //cancel a not payed purchase 

    
    /**
     * to Verify IPN
     * @return 
     *  - a Json
     */
    public function verifyIPN() {
        $obj = new recurring();

        /** Log Time */
        $logDate = new DateTime();
        $logDate = $logDate->format("y:m:d h:i:s");
                    
        /**
        * Default IPN response, 
        * will change if there is any problem$orderLog = 'payment was confirmed; deliver goods';
        */
        $outputData = array(
            'errorType'		=> self::ERROR_TYPE_NONE,
            'errorCode' 	=> null,
            'errorMessage'	=> ''
        );

        /**
        *  Fetch all HTTP request headers
        */
        $aHeaders = $this->getApacheHeader();

        write_log("----------IPN---------------");
        write_log($aHeaders);
        write_log("----------------------------");

        $verificationToken = $this->getVerificationToken($aHeaders);        
        

        /**
        * check if header has Apikey
        */
        if(!$this->validHeader($aHeaders) || empty($verificationToken)) {
            if($this->hasXapikeyHeader($aHeaders)) {
               $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
               $outputData['errorCode']	= self::RECURRING_ERROR_CODE_NEED_VERIFY;
               $outputData['errorMessage']	= 'Add as Arhive';
               return $outputData;
            } else {
                /** Log IPN */
                write_log("IPN__header is not an valid HTTP HEADER");
                echo 'IPN__header is not an valid HTTP HEADER' . PHP_EOL;
                exit;
            }            
        } 
        

        /**
        *  fetch Verification-token from HTTP header 
        */
        $verificationToken = $this->getVerificationToken($aHeaders);
        if($verificationToken === null)
            {
            /** Log IPN */
            write_log("IPN__Verification-token is missing in HTTP HEADER");
            echo 'IPN__Verification-token is missing in HTTP HEADER' . PHP_EOL;
            exit;
            }

        /**
        * Analyzing verification token
        * Verification token is JWT & should used right encoding/decoding algorithm 
        */
        $tks = \explode('.', $verificationToken);
        if (\count($tks) != 3) {
            write_log("Has verification-token but is wrong JWT Token");
            throw new \Exception('Wrong_Verification_Token');
            exit;
        }
        list($headb64, $bodyb64, $cryptob64) = $tks;
        $jwtHeader = json_decode(base64_decode(\strtr($headb64, '-_', '+/')));
        
        if($jwtHeader->typ !== 'JWT') {
            write_log("Has verification-token but does not have right JWT Token Type");
            throw new \Exception('Wrong_Token_Type');
            exit; 
        }

        /**
        * check if publicKeyStr is defined
        */
        if(isset($this->publicKeyStr) && !is_null($this->publicKeyStr)){
            $publicKey = openssl_pkey_get_public($this->publicKeyStr);
            if($publicKey === false) {
                write_log("IPN__public key is not a valid public key");
                echo 'IPN__public key is not a valid public key' . PHP_EOL; 
                exit;
            }
        } else {
            write_log("IPN__Public key missing");
            echo "IPN__Public key missing" . PHP_EOL; 
            exit;
        }
        
        
        /**
        * Get raw data
        */
        $HTTP_RAW_POST_DATA = file_get_contents('php://input');

        /**
        * Verify JWT algorithm
        * Default alg is RS512$orderLog = 'payment was confirmed; deliver goods';
        */
        if(!isset($this->alg) || $this->alg==null){
            write_log("IDS_Service_IpnController__INVALID_JWT_ALG");
            throw new \Exception('IDS_Service_IpnController__INVALID_JWT_ALG');
            exit;
        }
        $jwtAlgorithm = !is_null($jwtHeader->alg) ? $jwtHeader->alg : $this->alg ;

        
        try {
            JWT::$timestamp = time() * 1000; 
            $objJwt = JWT::decode($verificationToken, $publicKey, array($jwtAlgorithm));
        
            if(strcmp($objJwt->iss, 'NETOPIA Payments') != 0)
                {
                write_log("IDS_Service_IpnController__E_VERIFICATION_FAILED_GENERAL");
                throw new \Exception('IDS_Service_IpnController__E_VERIFICATION_FAILED_GENERAL');
                exit;
                }
            
            /**
             * check active posSignature 
             * check if is in set of signature too
             */
            if(empty($objJwt->aud) || $objJwt->aud[0] != $this->activeKey){
                write_log("IDS_Service_IpnController__INVALID_SIGNATURE");
                throw new \Exception('IDS_Service_IpnController__INVALID_SIGNATURE');
                exit;
            }
        
            if(!in_array($objJwt->aud[0], $this->posSignatureSet,true)) {
                write_log("IDS_Service_IpnController__INVALID_SIGNATURE_SET");
                throw new \Exception('IDS_Service_IpnController__INVALID_SIGNATURE_SET');
                exit;
            }
            
            if(!isset($this->hashMethod) || $this->hashMethod==null){
                write_log("IDS_Service_IpnController__INVALID_HASH_METHOD");
                throw new \Exception('IDS_Service_IpnController__INVALID_HASH_METHOD');
                exit;
            }
            
            /**
             * GET HTTP HEADER
             */
            $payload = $HTTP_RAW_POST_DATA;
            /**
             * validate payload
             * sutable hash method is SHA512 
             */
            $payloadHash = base64_encode(hash ($this->hashMethod, $payload, true ));
            /**
             * check IPN data integrity
             */
        
            if(strcmp($payloadHash, $objJwt->sub) != 0)
                {
                write_log("IDS_Service_IpnController__E_VERIFICATION_FAILED_TAINTED_PAYLOAD");
                throw new \Exception('IDS_Service_IpnController__E_VERIFICATION_FAILED_TAINTED_PAYLOAD', E_VERIFICATION_FAILED_TAINTED_PAYLOAD);
                exit;
                }
        
            try
                {
                $objIpn = json_decode($payload, false);
                }
            catch(\Exception $e)
                {
                write_log("IDS_Service_IpnController__E_VERIFICATION_FAILED_PAYLOAD_FORMAT");
                throw new \Exception('IDS_Service_IpnController__E_VERIFICATION_FAILED_PAYLOAD_FORMAT', E_VERIFICATION_FAILED_PAYLOAD_FORMAT);
                }
            
            switch($objIpn->payment->status)
                {
                case self::STATUS_NEW:
                    /**
                     * new purchase status
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'new purchase status';
                break;
                // case self::STATUS_CHARGEBACK_INIT:                  // chargeback initiat
                // case self::STATUS_CHARGEBACK_ACCEPT:                // chargeback acceptat
                // case self::STATUS_SCHEDULED:
                case self::STATUS_3D_AUTH:
                    /**
                     * Status Pending for any case
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= '3DS - Need Verufy Auth ';
                break;
                // case self::STATUS_CHARGEBACK_REPRESENTMENT:
                // case self::STATUS_REVERSED:
                case self::STATUS_PENDING_ANY:
                     /**
                     * Status Pending for any case
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'Status Pending for any case';
                break;
                //case self::STATUS_PROGRAMMED_RECURRENT_PAYMENT:
                //case self::STATUS_CANCELED_PROGRAMMED_RECURRENT_PAYMENT:
                //case self::STATUS_TRIAL_PENDING:                    //specific to Model_Purchase_Sms_Online; wait for ACTON_TRIAL IPN to start trial period
                //case self::STATUS_TRIAL:                            //specific to Model_Purchase_Sms_Online; trial period has started
                case self::STATUS_EXPIRED:                          //cancel a not payed purchase 
                     /**
                     * Status Expired
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'cancel a not payed purchase';
                break;
                case self::STATUS_OPENED:                           // preauthorizate (card)
                     /**
                     * Status Opened
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'preauthorizate (card)';
                break;
                case self::STATUS_PENDING:
                     /**
                     * Status Pending
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'Status Pending';
                break;
                case self::STATUS_ERROR:                            // error
                     /**
                     * payment Error
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'payment general error';
                break;
                case self::STATUS_DECLINED:                         // declined
                    /**
                     * payment status is DECLINED
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'payment is declined';
                break;
                case self::STATUS_FRAUD:                            // fraud
                    /**
                     * payment status is in fraud, reviw the payment
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'payment in reviwing';
                break;
                case self::STATUS_PENDING_AUTH:                     // in asteptare de verificare pentru tranzactii autorizate
                    /**
                     * update payment status, last modified date&time in your system
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'update payment status, last modified date&time in your system';
                break;
                
                case self::STATUS_PAID:                             // capturate (card)
                case self::STATUS_CONFIRMED:
                    /**
                     * payment was confirmed; deliver goods
                     */
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'payment was confirmed; deliver goods';
                break;
                
                case self::STATUS_CREDIT:                           // capturate si apoi refund
                    /**
                     * a previously confirmed payment eas refinded; cancel goods delivery
                     */                    
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'a previously confirmed payment eas refinded; cancel goods delivery';
                break;
                
                case self::STATUS_CANCELED:                         // void
                    /**
                     * payment was cancelled; do not deliver goods
                     */
                    $outputData['errorType']	= self::ERROR_TYPE_TEMPORARY;
                    $outputData['errorCode']	= null;
                    $outputData['errorMessage']	= 'payment was cancelled; do not deliver goods';
                break;
                }
            
        } catch(\Exception $e)
        {
            $outputData['errorType']	= self::ERROR_TYPE_PERMANENT;
            $outputData['errorCode']	= ($e->getCode() != 0) ? $e->getCode() : self::E_VERIFICATION_FAILED_GENERAL;
            $outputData['errorMessage']	= $e->getMessage();
        }

        return $outputData;
    }


    /**
    *  Fetch all HTTP request headers
    */
    public function getApacheHeader() {
        $aHeaders = apache_request_headers();
        return $aHeaders;
    }

    /**
    * if header exist in HTTP request
    * and is a valid header
    * @return bool 
    */
    public function validHeader($httpHeader) {
        if(!is_array($httpHeader)){
            return false;
        } else {
            foreach($httpHeader as $key => $val) {
                if(strtolower($key) == strtolower('Verification-token')) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
    * if header exist in HTTP request
    * and is a valid header
    * @return bool 
    */
    public function hasXapikeyHeader($httpHeader) {
        if(!is_array($httpHeader)){
            return false;
        } else {
            foreach($httpHeader as $key => $val) {
                if(strtolower($key) == strtolower('X-Apikey')) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
    *  fetch Verification-token from HTTP header 
    */
    public function getVerificationToken($httpHeader) {
        foreach($httpHeader as $headerName=>$headerValue)
            {
                if(strcasecmp('Verification-token', $headerName) == 0)
                {
                    $verificationToken = $headerValue;
                    return $verificationToken;
                }
            }
        return null;
    }
}