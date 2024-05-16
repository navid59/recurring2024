<?php
include_once( 'packages/recurring.php' );
include_once( 'packages/ipn.php' );
include_once( 'packages/debugging.php' );

/**
 * *****************************
 * *****************************
 * New Solution for Notify URL *
 * *****************************
 * *****************************
 */
function notAllowed() {
    status_header(200);
    return "Not allowed request!";
}
function notify() {
    status_header(200);
    return getHeaderRequest();
}

add_action('rest_api_init', function() {
    @register_rest_route('ntp-recurring/v1', 'notify', [
        'methods' => 'GET',
        'callback' => 'notAllowed',
    ]);
    
    @register_rest_route('ntp-recurring/v1', 'notify', [
        'methods' => 'POST',
        'callback' => 'notify',
    ]);
});

function getHeaderRequest() {
    global $wpdb;
    $obj = new recurring();
    
    
    $ntpIpn = new IPN();
    $ntpIpn->activeKey         = $obj->getSignature(); // activeKey or posSignature
    $ntpIpn->posSignatureSet[] = $ntpIpn->activeKey; // Array of another posSignature, if exist
    
    $ntpIpn->hashMethod        = 'SHA512';
    $ntpIpn->alg               = 'RS512';
    $ntpIpn->publicKeyStr = "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAy6pUDAFLVul4y499gz1P\ngGSvTSc82U3/ih3e5FDUs/F0Jvfzc4cew8TrBDrw7Y+AYZS37D2i+Xi5nYpzQpu7\nryS4W+qvgAA1SEjiU1Sk2a4+A1HeH+vfZo0gDrIYTh2NSAQnDSDxk5T475ukSSwX\nL9tYwO6CpdAv3BtpMT5YhyS3ipgPEnGIQKXjh8GMgLSmRFbgoCTRWlCvu7XOg94N\nfS8l4it2qrEldU8VEdfPDfFLlxl3lUoLEmCncCjmF1wRVtk4cNu+WtWQ4mBgxpt0\ntX2aJkqp4PV3o5kI4bqHq/MS7HVJ7yxtj/p8kawlVYipGsQj3ypgltQ3bnYV/LRq\n8QIDAQAB\n-----END PUBLIC KEY-----\n";


    $ipnResponse = $ntpIpn->verifyIPN();

    /**
    * IPN Output
    */
    echo json_encode($ipnResponse);   

    /**  
    * IPN is checked 
    * Base on IPN result the Archive will be add in DB , ...  
    */
    if(($ipnResponse['errorType'] == IPN::ERROR_TYPE_TEMPORARY) && ($ipnResponse['errorCode'] == IPN::RECURRING_ERROR_CODE_NEED_VERIFY)) {
        $headers = apache_request_headers();
        if(hasToken($headers)) {            
            $data = file_get_contents('php://input');
            $arrDate = json_decode($data, true);
            if($arrDate['NotifySubscription']['Action'] == "Unsubscribe") {
                $wpdb->insert( 
                    $wpdb->prefix . $obj->getDbSourceName('history'), 
                    array( 
                        'Subscription_Id'=> $arrDate['NotifySubscription']['SubscriptionID'],
                        'TransactionID'  => $arrDate['NotifyOrder']['orderID'],
                        'NotifyContent'  => $data,
                        'Comment'        => $arrDate['NotifyPayment']['Message'],
                        'Status'         => 2,
                        'CreatedAt'      => date("Y-m-d H:i:s")
                    )
                );
            } else {
                $lonkToPay = !empty($arrDate['NotifyPayment']['PaymentURL']) ? "(Link to pay : ".$arrDate['NotifyPayment']['PaymentURL'].")" : "";
                $insertResult = $wpdb->insert( 
                    $wpdb->prefix . $obj->getDbSourceName('history'), 
                    array( 
                        'Subscription_Id'=> $arrDate['NotifySubscription']['SubscriptionID'],
                        'TransactionID'  => $arrDate['NotifyOrder']['orderID'],
                        'NotifyContent'  => $data,
                        'Comment'        => $arrDate['NotifyPayment']['Message'].$lonkToPay,
                        'Status'         => $arrDate['NotifyPayment']['Status'],
                        'CreatedAt'      => date("Y-m-d H:i:s")
                    )
                ); 
            }

            /** To add suspended in history */
            if($arrDate['NotifySubscription']['Status'] == 3) {
                $wpdb->insert( 
                    $wpdb->prefix . $obj->getDbSourceName('history'), 
                    array( 
                        'Subscription_Id'=> $arrDate['NotifySubscription']['SubscriptionID'],
                        'TransactionID'  => ' - ',
                        'NotifyContent'  => $data,
                        'Comment'        => __('Suspended subscription', 'ntpRp'),
                        'Status'         => 30,
                        'CreatedAt'      => date("Y-m-d H:i:s")
                    )
                );
                $wpdb->update( 
                    $wpdb->prefix . $obj->getDbSourceName('subscription'), 
                    array( 
                        'Status'          => 3,
                        'UpdatedAt'       => date("Y-m-d")
                    ),
                    array(
                        'Subscription_Id' => $arrDate['NotifySubscription']['SubscriptionID']
                    )
                );
            }
        } else {
            /** Log IPN */
            // IPN - Request is not a valid request 
        }        
    } elseif($ipnResponse['errorType'] == IPN::ERROR_TYPE_NONE) {
        /** Log */
        // Payment respunse is ZERO - it mean is paid, ;-)
    } else {
        /** Log */
        // Other type of response
    }
}

function hasToken($header) {
    $ntpIpn = new IPN();
    if($ntpIpn->hasXapikeyHeader($header)) {
        $apikeyInHeader = getApiKeyFromHeader($header);
        if($apikeyInHeader) {
            if(isValidToken($apikeyInHeader)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }       
    } else {
        return false;
    }
}

function getApiKeyFromHeader($header) {
    foreach($header as $key => $value) {
        if(strtolower($key) == strtolower('X-Apikey')) {
            return $value;
        }
    }
    return false;
}

function isValidToken($apiKey) {
    $obj = new recurring();
    if(ltrim($obj->getApiKey()) == ltrim($apiKey)) {
        return true;
    } else {
        return false;
    }
}