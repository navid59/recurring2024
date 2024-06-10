<?php
    class recurringFront extends recurring {
        function getPlan($planId){
            global $wpdb;
            $obj = new recurringFront();

            $plans = $wpdb->get_results("SELECT *
                                    FROM  ".$wpdb->prefix . $obj->getDbSourceName('plan')."
                                    WHERE PlanId = '".$planId."' 
                                    AND Status = 1 
                                    LIMIT 1", "ARRAY_A");
            $planInfo = count($plans) ? $plans[0] : null;

            if(!empty($planInfo)) {
                $resultData = [
                    'plan' => array(
                        'Key'               => '',
                        'Id'                => $planInfo['PlanId'],
                        'MerchantSignature' => self::getSignature(),
                        'Title'             => $planInfo['Title'],
                        'Description'       => $planInfo['Description'],
                        'Amount'            => $planInfo['Amount'],
                        'Currency'          => $planInfo['Currency'],
                        'RecurrenceType'    => $planInfo['Recurrence_Type'],
                        'Frequency'         => [
                                                'Type' => $planInfo['Frequency_Type'],
                                                'Value' => $planInfo['Frequency_Value']
                                                ],
                        'GracePeriod'       => $planInfo['Grace_Period'],
                        'Taxable'           => $planInfo['Taxable'], 
                        'InitialPayment'    => $planInfo['Initial_Payment'], 
                        'Status'            => $planInfo['Status'], 
                        'CreatedAt'         => $planInfo['CreatedAt'], 
                        'UpdatedAt'         => $planInfo['UpdatedAt'] 
                    )
                ];
                return $resultData;
            } else {
                return null;
            }
        }

        /**
         * The used in verify auth to know about type of Plan
         */
        function getPlan_LIVE($planId){
            $url = self::getApiUrl('plan/status');
            $data = array(
                'Signature' => self::getSignature(),
                "PlanId" => $planId+0
            );
        
            $postData = json_encode($data);
        
            $resultData = self::getData($url, $postData);
            return $resultData;
        }

        function setSubscription($formData){
            $url = self::getApiUrl('subscription'); 
            $postData = json_encode($formData);

            $resultData = self::getData($url, $postData);
            return $resultData;
        }

        function setUnsubscription($formData){
            $url = self::getApiUrl('subscription/cancel'); 
            $postData = json_encode($formData);
        
            $resultData = self::getData($url, $postData);
            return $resultData;
        }

        function setVerifyAuth($formData){
            /**
             * The Poet in some cases not sending the authenticationToken
             * So in WP we send without authentication token
             */
            // if(empty($formData['authenticationToken']) || empty($formData['ntpID'])) 
            if(empty($formData['ntpID'])) {
                $responseArr = [
                    "status" => false,
                    "msg"    => "Is Canceled. Not able to do Verify Auth. Missing ntpID",
                    "data"   => [
                        "code"    => "12",
                        "message" => "Error Verify Auth. Missing ntpID"
                    ]
                ];
                $responseJson = json_encode($responseArr);
                echo $responseJson;
                die();
            }

            $url = self::getApiUrl('3DS/verify-auth'); 
            $postData = json_encode($formData);

            // write_log("-- Payment Verify Auth Data --");
            // write_log($postData);

            $resultData = self::getData($url, $postData);
            return $resultData;
        }
    }
?>