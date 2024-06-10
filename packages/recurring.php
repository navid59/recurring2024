<?php
    class recurring {
        protected $slug = 'netopia_recurring';

        
        /**
        *  get Public Key
        */
        function getPublicKey() {
            return get_option($this->slug.'_general_public_key_file_name', array());
        }

        function getSignature() {
            return get_option($this->slug.'_signature', array());
        }

        function getApiKey() {
            if($this->isLive()) {
                return get_option($this->slug.'_api_key', array());
            } else {
                return get_option($this->slug.'_api_key_sandbox', array());
            }
            
        }

        function isLive() {
            $mood = get_option($this->slug.'_mood', array());
            if(count($mood)){
                return $mood[0] == 'live' ? true : false;
            } else {
                /* Do nothing 
                *  Is just for handel PHP Error Notify on Merchant Server
                */
                return;
            }  
        }

        function getNTPID() {            
            $ntpRpNtpID = $_COOKIE['ntpRp-NtpID'];
            
            /** Log */
            if(empty($ntpRpNtpID) || is_null($ntpRpNtpID)) {
                write_log('cookie ntpRp-NtpID is empty!');
            }
            return $ntpRpNtpID;
        }

        function getAuthenticationToken() {   
            if(!isset($_COOKIE['ntpRp-AuthenticationToken'])) {
                /** Log */
                if(empty($ntpRpAuthenticationToken) || is_null($ntpRpAuthenticationToken)) {
                    write_log('cookie ntpRp-AuthenticationToken is empty!');
                }
                return ("");
            }

            $ntpRpAuthenticationToken = $_COOKIE['ntpRp-AuthenticationToken'];
            return $ntpRpAuthenticationToken;
        }

        function getSubscriptionData() {
            $ntpRpSubscriptionData = $_COOKIE['ntpRp-cookies-json'];
            
            /** Log */
            if(empty($ntpRpSubscriptionData) || is_null($ntpRpSubscriptionData)) {
                write_log('cookie ntpRp-cookies-json is empty!');
            }

            return (stripslashes($ntpRpSubscriptionData));
        }

        function getApiUrl($action){
            if($this->isLive()) {
                $Url = BASE_URL_RECURRING_API_LIVE.$action;
            } else {
                $Url = BASE_URL_RECURRING_API_SANDBOX.$action;
            }
            return $Url;
        }

        function getNotifyUrl() {
            return get_site_url()."/index.php/wp-json/ntp-recurring/v1/notify";
        }

        function getBackUrl($planId) {
            $parts = parse_url( home_url() );
            $current_uri = "{$parts['scheme']}://{$parts['host']}" . add_query_arg(array('planId' => $planId ));
            $backUrl = $current_uri;
            return $backUrl;
        }

        function getSuccessMessagePayment() {
            $msg = get_option($this->slug.'_subscription_reg_msg');
            return $msg; 
        }

        function getFailedMessagePayment() {
            $msg = get_option($this->slug.'_subscription_reg_failed_msg');
            return $msg; 
        }

        function getUnsuccessMessage() {
            $msg = get_option($this->slug.'_unsubscription_msg');
            return $msg; 
        }

        function getAccountPageSetting() {
            $accountPageSubtitle = get_option($this->slug.'_account_subtitle', array());
            $accountPageFirstParagraph = get_option($this->slug.'_account_paragraph_first', array());
            $accountPageSecoundParagraph = get_option($this->slug.'_account_paragraph_secound', array());
            return array(
                "subtitle" => !empty($accountPageSubtitle) ? $accountPageSubtitle : __('My subscription account','ntpRp') ,
                "firstParagraph" => !empty($accountPageFirstParagraph) ? $accountPageFirstParagraph : __('Welcome to recurring account page','ntpRp'),
                "secoundParagraph" => !empty($accountPageSecoundParagraph) ? $accountPageSecoundParagraph : __('To get more information about your recurring situation, use the menu','ntpRp')
            );
        }

        function getLoginUrl() {
            $baseURL = get_site_url();
            return $baseURL.'/subscription-account';
        }

        function getData($url, $requestData) {
            
            $authenticationToken = $this->getApiKey();

            $ch = curl_init($url); 
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','token: '.$authenticationToken));
            $result = curl_exec($ch);

            // Check for errors
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                // Log or handle error
                error_log('cURL error: ' . $error_msg);
                curl_close($ch);
                return false; // or handle error as needed
            }

            curl_close($ch);

            $jsonDate = $result;
            $arrayData = json_decode($jsonDate, true);

            return($arrayData);
        }


        function getStatusStr($section, $statusCode) {
            switch ($section) {
                case 'subscription':
                    switch ($statusCode) {
                        case '0':
                            $statusStr = 'Not Approved yet!';
                            break;
                        case '1':
                            $statusStr = 'Active';
                            break;
                        case '2':
                            $statusStr = 'Unsubscribed';
                            break;
                        case '3':
                            $statusStr = 'Suspend';
                            break;
                        default:
                            $statusStr = $statusCode;
                    }
                break;
                case 'plan':
                    switch ($statusCode) {
                        case '1':
                            $statusStr = __('Active Plan','ntpRp');
                            break;
                        case '2':
                            $statusStr = __('Inactive Plan','ntpRp');
                            break;
                        default:
                            $statusStr = $statusCode;
                    }
                break;
                case 'report':
                    switch ($statusCode) {
                        case '00':
                            $statusStr = 'Confirmed';
                            break;
                        case '2':
                            $statusStr = 'Unsubscribed';
                            break;
                        case '30':
                            $statusStr = 'Suspend';
                            break;
                        case '3':
                            $statusStr = 'Paid';
                            break;
                        case '12':
                            $statusStr = 'Not paid';
                            break;
                        case '11':
                            $statusStr = 'Error - Not paid';
                            break;
                        case '15':
                            $statusStr = 'Need verify payment';
                            break;
                        case null:
                            $statusStr = '-';
                            break;
                        default:
                            $statusStr = $statusCode;
                    }
                break;
            }
            return $statusStr;
         }
   

        public function informMember($subject, $message) {
            if (empty($subject) || empty($message))
                return false;

            $current_user = wp_get_current_user();
            $to = $current_user->user_email;
            $mailResult = false;
            $mailResult = wp_mail( $to, $subject, $message );
            
        } 

        public function getDbSourceName($section) {
            $mod = $this->isLive() ? "live" : "sandbox";
            $DbSrc = '';
            switch ($mod) {
                case "live":
                    switch ($section) {
                        case "plan":
                            $DbSrc = "ntp_plans";
                        break;
                        case "subscription":
                            $DbSrc = "ntp_subscriptions";
                        break;
                        case "history":
                            $DbSrc = "ntp_history";
                        break;
                        default:
                            throw new \Exception('NTP recurring -> '.$section.' Connection problem!');
                    }
                break;
                case "sandbox":
                    switch ($section) {
                        case "plan":
                            $DbSrc = "ntp_plans";
                        break;
                        case "subscription":
                            $DbSrc = "ntp_subscriptions_sandbox";
                        break;
                        case "history":
                            $DbSrc = "ntp_history_sandbox";
                        break;
                        default:
                            throw new \Exception('NTP recurring -> '.$section.' Connection problem!');
                    }
                break;
                default:
                    throw new \Exception('NTP recurring Connection problem!');
            }
            return $DbSrc;
        }
    }    
?>