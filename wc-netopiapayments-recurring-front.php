<?php
 add_action('wp_enqueue_scripts', 'enqueue_and_register_ntp_recurring_js_scripts');
 add_action( 'wp_enqueue_scripts', 'ntp_recurring_enqueue_scripts', 10 );

 add_action('wp_ajax_addNewSubscription', 'recurring_addSubscription');
 add_action('wp_ajax_nopriv_addNewSubscription', 'recurring_addSubscription');

 add_action('wp_ajax_doVerifyAuth', 'recurring_verifyAuth');
 add_action('wp_ajax_nopriv_doVerifyAuth', 'recurring_verifyAuth');

 add_action('wp_ajax_updateSubscriberAccountDetails', 'recurring_updateSubscriberAccountDetails');
 add_action('wp_ajax_unsubscription', 'recurring_unsubscription');
 add_action('wp_ajax_getMySubscriptions', 'recurring_account_getMySubscriptions');
 add_action('wp_ajax_getMyHistory', 'recurring_account_getMyHistory');
 add_action('wp_ajax_getMyNextPayment', 'recurring_getMyNextPayment');
 add_action('wp_ajax_getMyAccountDetails', 'recurring_getMyAccountDetails');
//  add_action('wp_ajax_getMyCardDetails', 'recurring_getMyCardDetails');
 add_action('wp_ajax_logoutAccount', 'recurring_logoutAccount');

/** To assign shortcode */
add_shortcode('NTP-Recurring', 'assignToRecurring');
add_shortcode('NTP-Recurring-My-Account', 'ntpMyAccount');

/** Assign FrontJs , ajax_url */
add_action('wp_enqueue_scripts', 'frontResource');
function frontResource() {
   wp_enqueue_script('my-jquery',plugin_dir_url( __FILE__ ).'js/recurringFront.js', array('jquery'));
   wp_localize_script( 'my-jquery', 'frontAjax', array('ajax_url' => admin_url( 'admin-ajax.php' )));
}

/**
 * Assign stripe js payment card validation
 * To validat card in front, before submit
*/
add_action('wp_enqueue_scripts', 'stripePaymentJS');
function stripePaymentJS() {
   wp_enqueue_script('stripejQueryPayment',plugin_dir_url( __FILE__ ).'js/jquery.payment.js', array('jquery'));
   wp_localize_script( 'stripejQueryPayment', 'frontAjax', array('ajax_url' => admin_url( 'admin-ajax.php' )));
}

/** Assign CSS, JS for datatable */
function ntp_recurring_enqueue_scripts() {
    wp_enqueue_style  ('ntp_recurring_front_css_datatables', plugin_dir_url( __FILE__ ) . 'css/addons/datatables.min.css',array(),'2.0' ,false);
    wp_register_script('ntp_recurring_front_script_datatables', plugin_dir_url( __FILE__ ) . 'js/addons/datatables.min.js', array(), '1.0.0', true );
    wp_enqueue_script ('ntp_recurring_admin_script_datatables' );
}

/** Assign Bootstrap CSS, JS & 3DS in Fronend */
function enqueue_and_register_ntp_recurring_js_scripts(){
    if (is_page() && (has_shortcode(get_post()->post_content, 'NTP-Recurring') || has_shortcode(get_post()->post_content, 'NTP-Recurring-My-Account'))) {
        wp_enqueue_style  ( 'ntp_recurring_front_css', plugin_dir_url( __FILE__ ) . 'css/bootstrap/bootstrap.min.css',array(),'3.0' ,false);
        wp_register_script( 'ntp_recurring_script', plugin_dir_url( __FILE__ ) . 'js/bootstrap/bootstrap.bundle.min.js', array('jquery'), '1.1.0', true );
        wp_enqueue_script ( 'ntp_recurring_script' );
        wp_register_script( 'ntp_recurring_3ds', plugin_dir_url( __FILE__ ) . 'js/3DS.js', array('jquery'), '1.0.1', true );
        wp_enqueue_script ( 'ntp_recurring_3ds' ); 
    }
 }




function recurring_verifyAuth(){
    
    /** Log */
    write_log('--- call -- recurring_verifyAuth() -- to Do Verifiy Auth----');
    write_log("ntpRp-NtpID : ".$_COOKIE['ntpRp-NtpID']);
    write_log("ntpRp-AuthenticationToken : " . $_COOKIE['ntpRp-AuthenticationToken']);

    global $wpdb;
    $obj = new recurringFront();

    if (!isset($_COOKIE['ntpRp-AuthenticationToken']) || !isset($_COOKIE['ntpRp-NtpID'])) {
        $verifyAuthResult = array(
            'status'=> false,
            'msg'=> "Missing Data for Verify Auth"."ntp ID : ".$_COOKIE['ntpRp-NtpID'] . "AuthenticationToken : " . $_COOKIE['ntpRp-AuthenticationToken'],
            'data' =>  $_REQUEST
            );
        echo json_encode($verifyAuthResult);
        die();
    }

    /** get subscription Info from cookie */
    $subscriptionDataJson = $obj->getSubscriptionData();
    $subscriptionDataObj = json_decode($subscriptionDataJson);

    /** To convert form data in a single Array */
    $singleArrayFormData = [];
    foreach ($_POST['formData'] as $formDataItem) {
        $singleArrayFormData[$formDataItem['name']] = $formDataItem['value']; 
    }

    $verifyAuthFormData = array(
        "signature"      => $obj->getSignature(),
        "subscriptionId" => $subscriptionDataObj->Subscription_Id,
        "planId"         => $subscriptionDataObj->PlanId+0,
        "authenticationToken" => $obj->getAuthenticationToken(),
        "ntpID"         => $obj->getNTPID(),
        "formData"      => $singleArrayFormData,
        "isTestMod"     => !$obj->isLive()
    );
    
    $jsonResultData = $obj->setVerifyAuth($verifyAuthFormData);

    write_log("---------WP recurring_verifyAuth ------------------");
    write_log($jsonResultData);
    write_log("---------------------------------------------------");

    /**
     *  Prepare data to be added in DB, ...
     */
    $current_user = wp_get_current_user();
    $memberInfos = $wpdb->get_results("SELECT *
                                    FROM  ".$wpdb->prefix . $obj->getDbSourceName('subscription')." as s 
                                    WHERE s.UserID = '".$current_user->user_login."' 
                                    ORDER BY s.id DESC 
                                    LIMIT 1", "ARRAY_A");
    if(count($memberInfos)) {
        $memberInfo = $memberInfos[0];

        $Member = array (
            "First_Name" => $memberInfo['First_Name'],
            "Last_Name"  => $memberInfo['Last_Name'],
            "UserID"     => $memberInfo['UserID'],
            "Email"      => $memberInfo['Email'],
            "Address"    => $memberInfo['Address'],
            "City"       => $memberInfo['City'],
            "Tel"        => $memberInfo['Tel']
        );        
    } else {
        $Member = array (
            "First_Name" => $subscriptionDataObj->First_Name,
            "Last_Name"  => $subscriptionDataObj->Last_Name,
            "UserID"     => $subscriptionDataObj->UserID,
            "Email"      => $subscriptionDataObj->Email,
            "Address"    => $subscriptionDataObj->Address,
            "City"       => $subscriptionDataObj->City,
            "Tel"        => strval($subscriptionDataObj->Tel)
        );
    }
    
    /** Base on verify Auth "00" / "19",...  Data will be Add in DB */
    if($jsonResultData['code']== "00") {
        $Member['Subscription_Id'] = $subscriptionDataObj->Subscription_Id;
        $Member['PlanId'] = $subscriptionDataObj->PlanId;
        $Member['Status'] = 1;
        $Member['NextPaymentDate'] = "";
        $Member['CreatedAt'] = date("Y-m-d");
        $Member['UpdatedAt'] = date("Y-m-d");
        $Member['StartDate'] = $subscriptionDataObj->StartDate;

        /**
         * @Navid
         * Missing add to Arhiv 
         */
        
        // Add subscription to DB 
        $wpdb->insert( $wpdb->prefix . $obj->getDbSourceName('subscription'), $Member );

        // Sned mail
        $obj->informMember(__('New subscription','ntpRp'), __('Congratulation you successfully subscribed','ntpRp'));

        // Response ntpRpVerifyAuthData
        $customMsg = $obj->getSuccessMessagePayment();
        $status = true;
        $detail = "";
        $msg = !empty($customMsg) ? $customMsg : $jsonResultData['message'];
    } else {
        $customMsg = $obj->getFailedMessagePayment();
        $status = false;
        $detail = "";
        $msg = !empty($customMsg) ? $customMsg : $jsonResultData['message'];
    }
    
    
    $verifyAuthResult = array(
            'status'=> isset($jsonResultData['code']) && $jsonResultData['code']!== "00" ? false : true,
            'msg'=> !empty($jsonResultData['message']) ? $jsonResultData['message'] : '',
            'data' =>  $jsonResultData
            );
    echo json_encode($verifyAuthResult);

    /** Log */
    write_log('-- Happy End - recurring_verifyAuth() --- DONE -- ');
    // wp_send_json();
    die();
}


function recurring_addSubscription() {
    global $wpdb;
    
    // set DateTimeZone() for Romania 
    // $timezone = new DateTimeZone( 'Europe/Bucharest' );

    $obj = new recurringFront();



    $current_user = wp_get_current_user();  
    if($current_user->ID == 0) {
        $Member = array (
            "Name" => $_POST['Name'],
            "LastName" => $_POST['LastName'],
            "UserID" => $_POST['UserID'],
            "Pass" => $_POST['Pass'],
            "Email" => $_POST['Email'],
            "Address" => $_POST['Address'],
            "City" => $_POST['City'],
            "Tel" => strval($_POST['Tel'])
        );
    } else {
        $MemberDetails = $wpdb->get_results("SELECT *
                                    FROM  ".$wpdb->prefix . $obj->getDbSourceName('subscription')." as s 
                                    WHERE s.UserID = '".$current_user->user_login."' 
                                    ORDER BY s.id DESC 
                                    LIMIT 1", "ARRAY_A");

        $Member = array (
            "Name" => $current_user->first_name,
            "LastName" => $current_user->last_name,
            "UserID" => $current_user->user_login,
            "Email" => $current_user->user_email,
            "Address" => !isset($MemberDetails[0]['Address']) ? NULL : $MemberDetails[0]['Address'],
            "City" => !isset($MemberDetails[0]['City']) ? NULL : $MemberDetails[0]['City'],
            "Tel" => !isset($MemberDetails[0]['Tel']) ? NULL : $MemberDetails[0]['Tel']
        );

        if(empty($MemberDetails[0]['Address']) || empty($MemberDetails[0]['City']) || empty($MemberDetails[0]['Tel'])) {
            $Member['Address'] = $_POST['Address'];
            $Member['City'] = $_POST['City'];
            $Member['Tel'] = strval($_POST['Tel']);
        }
    }
    

    /**
     *  Authenticate User
     *  If not exist will be create
     *  */
    authenticateUser($Member); 


    $obj3DS = json_decode(stripslashes($_POST['ThreeDS']));
    $arr3DS = (array)$obj3DS;
    
    /**
     * Make new subscription request
     */

    $subscriptionData = array(
        "Member" => array (
            "UserID" => $Member['UserID'],
            "Name" => $Member['Name'],
            "LastName" => $Member['LastName'],
            "Email" => $Member['Email'],
            "Address" => $Member['Address'],
            "City" => $Member['City'],
            "Tel" => strval($Member['Tel']),
            'IsTestMod' => $obj->isLive() ? false : true 
        ),
        "Merchant" => array(
            "Apikey"        => $obj->getApiKey(),
            "Signature"     => $obj->getSignature(),
            "NotifyUrl"     => $obj->getNotifyUrl(),
            "RedirectUrl"   => $_POST['BackUrl'],
            "Tolerance"     =>  true,
            "IntervalRetry" => 3
        ),
        "Plan" =>  array(
            "PlanId" => $_POST['PlanID']+0, 
            "StartDate" => date("Y-m-d")."T00:00:00-00:00",
            "EndDate" => ""
        ),
        "PaymentConfig" => array(
            "Instrument" => array (
                "Type" => "card",
                "Account" => strval($_POST['Account']),
                "ExpMonth" => $_POST['ExpMonth']+0,
                "ExpYear" => $_POST['ExpYear']+0,
                "SecretCode" => strval($_POST['SecretCode']),
                "Token" => ""
            ),
            "ThreeDS2" => $arr3DS
        )
      );   
     
    $jsonResultData = $obj->setSubscription($subscriptionData);

    write_log("---- recurring_addSubscription() --- RESULT ----");
    write_log($jsonResultData);
    write_log("------------------------------------------------");

    // Add subscription to DB 
    if($jsonResultData['code'] === "00") {
        $arrSubscriptionData = array( 
                'Subscription_Id' => $jsonResultData['data']['subscriptionId'],
                'First_Name'      => $Member['Name'],
                'Last_Name'       => $Member['LastName'],
                'Email'           => $Member['Email'],
                'Tel'             => $Member['Tel'],
                'Address'         => $Member['Address'],
                'City'            => $Member['City'],
                'UserID'          => $Member['UserID'],
                'NextPaymentDate' => date("Y-m-d"),
                'PlanId'          => $_POST['PlanID'],
                'StartDate'       => date("Y-m-d"),
                'EndDate'         => "",
                'Status'          => 1, // Payment is success
                'CreatedAt'       => date("Y-m-d"),
                'UpdatedAt'       => date("Y-m-d")
            );
        
        /**
         * IMPORTANT 
         * @Navid Missing Add to History 
         *  */    
        
         /**
          * NAVID to DO 
          * Check if user is exist with Status Zero for same Plan ID Then Update the USER Status
          * If is not exist Add IT 
          */

        // Add subscription to DB 
        $wpdb->insert( $wpdb->prefix . $obj->getDbSourceName('subscription'), $arrSubscriptionData );

        // Sned mail
        $obj->informMember(__('New subscription','ntpRp'), __('Congratulation you successfully subscribed','ntpRp'));
    } elseif ($jsonResultData['code'] === "19") {
        /** 
         *  Add user as none confirm user
         *  */  
        $arrSubscriptionData = array(
            'Subscription_Id' => $jsonResultData['data']['subscriptionId'],
            'First_Name'      => $Member['Name'],
            'Last_Name'       => $Member['LastName'],
            'Email'           => $Member['Email'],
            'Tel'             => $Member['Tel'],
            'Address'         => $Member['Address'],
            'City'            => $Member['City'],
            'UserID'          => $Member['UserID'],
            'NextPaymentDate' => date("Y-m-d"),
            'PlanId'          => $_POST['PlanID'],
            'StartDate'       => date("Y-m-d"),
            'EndDate'         => "",
            'Status'          => 0, // Payment is not approved | Signed | or IP is missed
            'CreatedAt'       => date("Y-m-d"),
            'UpdatedAt'       => date("Y-m-d")
            );

            // Add subscription POTENTIAL Subscribers to DB With STATUS ZERO 
            $wpdb->insert( $wpdb->prefix . $obj->getDbSourceName('subscription'), $arrSubscriptionData );

            
            $arrSubscriptionDataJson =  json_encode($arrSubscriptionData);
            
            /** Set Subscription info from cooke to be uesd it in countinu base on Payment result */
            setcookie('ntpRp-cookies-json', $arrSubscriptionDataJson, time() + 600 , '/');
            setcookie('ntpRp-AuthenticationToken', $jsonResultData['data']['details']['PaymentDetails']['AuthenticationToken'], time() + 600 , '/');
            setcookie('ntpRp-NtpID', $jsonResultData['data']['details']['PaymentDetails']['NtpID'], time() + 600 , '/');
    }
    
    if($jsonResultData['code'] === "00") {
        $customMsg = $obj->getSuccessMessagePayment();
        $status = true;
        $detail = "";
        $msg = !empty($customMsg) ? $customMsg : $jsonResultData['message'];
    } else {
        $customMsg = $obj->getFailedMessagePayment();
        $status = false;
        $detail = $jsonResultData['data']['details'];
        $msg = !empty($customMsg) ? $customMsg : $jsonResultData['message'];
    }

    $addSubscriptionResult = array(
        'status'=> $status,
        'msg'=> $msg,
        'detail'=> $detail,
        );
        wp_send_json($addSubscriptionResult) ;
}

function recurring_getMyNextPayment() {
    
    $a = new recurringAdmin();
    $nextPaymentData = array(
            "SubscriptionId" => $_POST['subscriptionId']+0
    );

    $jsonResultData = $a->getNextPayment($nextPaymentData);
    
    $myNextPaymentResult = array(
            'status'=> isset($jsonResultData['code']) && $jsonResultData['code']!== "00" ? false : true,
            'msg'=> !empty($jsonResultData['message']) ? $jsonResultData['message'] : '',
            'data' =>  $jsonResultData
            );
    echo json_encode($myNextPaymentResult);
    die();
}

function recurring_unsubscription() {
    global $wpdb;
    $obj = new recurringFront();

    $subscriptionData = array(
            "Signature" => $obj->getSignature(),
            "SubscriptionId" => $_POST['SubscriptionId']+0,
            "isTestMod" => !$obj->isLive() 
      );   
     
    $jsonResultData = $obj->setUnsubscription($subscriptionData);
    
    // Update subscription to DB 
    if($jsonResultData['code'] === "00") {
        $wpdb->update( 
            $wpdb->prefix . $obj->getDbSourceName('subscription'), 
            array( 
                'Status'          => 2,
                'UpdatedAt'       => date("Y-m-d")
            ),
            array(
                'id' => $_POST['Id'],
                'Subscription_Id' => $_POST['SubscriptionId']
            )
        );

        // Sned mail
        $obj->informMember(__('Unsubscription','ntpRp'), __('Hope to see you soon','ntpRp'));

    }

    if($jsonResultData['code'] === "00") {
        $customMsg = $obj->getUnsuccessMessage();
        $status = true;
        $msg = !empty($customMsg) ? $customMsg : $jsonResultData['message'];
    } else {
        $status = false;
        $msg = $jsonResultData['message'];
    }

    $unsubscribeResult = array(
        'status'=> $status,
        'msg'=> $msg,
        );
    
    wp_send_json($unsubscribeResult);
}

function recurring_logoutAccount() {
    wp_logout();
    
    $logoutArr = array(
        'status'=> true,
        'msg'=> __('Logout with success','ntpRp'),
        'redirectUrl'=> get_home_url().'/index.php/subscription-account',
    );
    
    wp_send_json($logoutArr);
}

function recurring_getMyAccountDetails() {
    global $wpdb;
    $obj = new recurringFront();

    /** Get Current user Info */
    $current_user = wp_get_current_user();
    // 1- get current user 
    // 2- know ID & User name
    $userName = $current_user->user_login;
    
    $MyDetails = $wpdb->get_results("SELECT *
                                    FROM  ".$wpdb->prefix . $obj->getDbSourceName('subscription')." as s 
                                    WHERE s.UserID = '".$current_user->user_login."'", "ARRAY_A");

    $subscriberPersonalData = array(
                                'status' => true,
                                'msg'    => '',
                                'data'   => '<div class="row" id="myAccountForm">
                                                <form class="needs-validation">
                                                    <h4 class="mb-3">'.__('Personal information').'</h4>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <input type="hidden" class="form-control" id="SubscriptionId" placeholder="" value="'.$MyDetails[0]['Subscription_Id'].'" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="firstName">'.__('First name','ntpRp').'</label>
                                                            <input type="text" class="form-control" id="firstName" placeholder="" value="'.$current_user->first_name.'" required>
                                                            <div class="invalid-feedback">
                                                            Valid first name is required.
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="lastName">'.__('Last name','ntpRp').'</label>
                                                            <input type="text" class="form-control" id="lastName" placeholder="" value="'.$current_user->last_name.'" required>
                                                            <div class="invalid-feedback">
                                                            Valid last name is required.
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row" >
                                                        <div class="col-md-4 mb-3">
                                                            <label for="username">'.__('Username','ntpRp').'</label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">@</span>
                                                                </div>
                                                                <input type="text" class="form-control" id="username" placeholder="Username" value="'.$current_user->user_login.'" required>
                                                                <div class="invalid-feedback" style="width: 100%;">
                                                                Your username is required.
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="password">'.__('Password','ntpRp').'</label>
                                                            <input type="password" class="form-control" id="password" required>
                                                            <div class="invalid-feedback">
                                                                Please enter a valid password.
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="email">'.__('Email','ntpRp').'</label>
                                                            <input type="email" class="form-control" id="email" placeholder="you@example.com" value="'.$current_user->user_email.'" required>
                                                            <div class="invalid-feedback">
                                                                Please enter a valid email address for shipping updates.
                                                            </div>
                                                        </div>
                                                    </div>
                                                
                                                    
                                                    <div class="mb-3">
                                                        <label for="address">'.__('Address','ntpRp').'</label>
                                                        <input type="text" class="form-control" id="address" placeholder="'.__('Your full address','ntpRp').'" value="'.$MyDetails[0]['Address'].'" required>
                                                        <div class="invalid-feedback">
                                                            Please enter your shipping address.
                                                        </div>
                                                    </div>
                                                
                                                    <div class="row">
                                                        <div class="col-md-5 mb-3">
                                                            <label for="tel">'.__('Tel','ntpRp').'</label>
                                                            <input type="text" class="form-control" id="tel" placeholder="" value="'.$MyDetails[0]['Tel'].'" required>
                                                            <div class="invalid-feedback">
                                                            Phone required.
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label for="country">'.__('Country','ntpRp').'</label>
                                                            <select class="custom-select d-block w-100" id="country" required>
                                                            <option value="">Choose...</option>
                                                            <option value="642" selected>Romania</option>
                                                            </select>
                                                            <div class="invalid-feedback">
                                                            Please select a valid country.
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4 mb-3">
                                                            <label for="state">'.__('State','ntpRp').'</label>
                                                            <select class="custom-select d-block w-100" id="state" name="state" required>'
                                                            .getJudete($MyDetails[0]['City']).
                                                            '</select>
                                                            <div class="invalid-feedback">
                                                            Please provide a valid state.
                                                            </div>
                                                        </div>                        
                                                    </div>
                                                    <hr class="mb-99000091842147684">
                                                    <button class="btn btn-primary btn-lg btn-block" type="button" id="" onclick="updateMyAccountDetails(); return false;" >'.__('Update', 'ntpRp').'</button>
                                                </form>
                                            </div>    
                                            <div class="jumbotron text-center alert alert-dismissible fade" id="msgBlock" role="alert">
                                                <h1 id="alertTitle" class="display-5"></h1>
                                                <p class="lead">
                                                    <strong>
                                                        <span id="msgContent"></span>
                                                    </strong>
                                                </p>
                                                <div id="myAccountGoToHome">
                                                    <hr>
                                                    <p class="lead">
                                                        <a class="btn btn-primary btn-sm" href="'.get_home_url().'" role="button">'.__('Continue to homepage','ntpRp').'</a>
                                                    </p>
                                                </div>
                                            </div>',
                                );

    wp_send_json($subscriberPersonalData);
}

/**
 * Update card data
 * NOt implimented yet
 */
/*
function recurring_getMyCardDetails() {
    global $wpdb;
    // 1- Get masked card data 
    // 2- Display form to update de card
    // 3- update the card
    
    $renewCard = array(
                        'status' => true,
                        'msg'    => '',
                        'data'   => getCardInfoHtml(),
                        );

    wp_send_json($renewCard);
}
*/

function recurring_updateSubscriberAccountDetails() {
    global $wpdb;
    $msg = '';
    $obj = new recurringFront();

    $subscriptionAccountDetails = array (
        "SubscriptionId" => $_POST['SubscriptionId'],
        "Name" => $_POST['Name'],
        "LastName" => $_POST['LastName'],
        "UserID" => $_POST['UserID'],
        "Pass" => $_POST['Pass'],
        "Email" => $_POST['Email'],
        "Address" => $_POST['Address'],
        "City" => $_POST['City'],
        "Tel" => strval($_POST['Tel'])
    );


    /**
     *  Authenticate User
     *  If user is that one who is already logined.
     *  if choeasen email is exist 
     *  */
    
    $current_user = wp_get_current_user();
    
    if($current_user->user_login != $subscriptionAccountDetails['UserID']) {
        $validateAuthResult = array(
            'status'=> false,
            'msg'=> __('You are not allowded to change Username','ntpRp')
        );
        wp_send_json($validateAuthResult);
    }

    if(!is_email($subscriptionAccountDetails['Email'])) {
        $validateEmailFormat = array(
            'status'=> false,
            'msg'=> __('The email address is not correct!', 'ntpRp')
        );
        wp_send_json($validateEmailFormat);
    }

    $validateChosenEmail = email_exists( $subscriptionAccountDetails['Email']);
    if($validateChosenEmail != false && $validateChosenEmail != $current_user->id) {
        $validateEmailResult = array(
            'status'=> false,
            'msg'=> __('The email is already exist', 'ntpRp')
        );
        wp_send_json($validateEmailResult);
    }

    if($subscriptionAccountDetails['Pass'] != "") {
        if(!isStrongPass($subscriptionAccountDetails['Pass'])) {
            $validatePassLenght = array(
                'status'=> false,
                'msg'=> __('The password is not a suitable password!','ntpRp')
            );
            wp_send_json($validatePassLenght);
        } else {
            /*
            * ChangePassword
            */
            $hash = wp_hash_password($subscriptionAccountDetails['Pass']);
            $passChangeStatus = $wpdb->update(
                $wpdb->prefix . "users",
                array(
                    'user_pass'           => $hash,
                    'user_activation_key' => '',
                ),
                array( 'ID' => $current_user->ID )
            );

            /* 
            * Clear cache of current user
            * Logout & Then Login 
            */ 
            if($passChangeStatus != false ) {
                clean_user_cache($current_user->ID);
                wp_clear_auth_cookie();
                wp_set_current_user($current_user->ID);
                wp_set_auth_cookie($current_user->ID, true, false);

                $user = get_user_by('id', $current_user->ID);
                update_user_caches($user);

                $msg = __('Password is changed . ','ntpRp');
            } else {
                $msg = __('Password is not changed . ','ntpRp');
            }
        }
    }
    
   
    /*
    * First SHOULD Update the subscriber info on Server by API
    * Then update the local data
    * BUT Temporary, just update local data
    */
    
    $updateResult = $wpdb->update( 
                        $wpdb->prefix . $obj->getDbSourceName('subscription'), 
                        array( 
                            'First_Name'      => $subscriptionAccountDetails['Name'],
                            'Last_Name'       => $subscriptionAccountDetails['LastName'],
                            'Email'           => $subscriptionAccountDetails['Email'],
                            'Address'         => $subscriptionAccountDetails['Address'],
                            'City'            => $subscriptionAccountDetails['City'],
                            'Tel'             => $subscriptionAccountDetails['Tel'],
                            'UpdatedAt'       => date("Y-m-d")
                        ),
                        array(
                            'UserID' => $subscriptionAccountDetails['UserID'] 
                        )
                    );
    
    if($updateResult != false) {
        update_user_meta( $current_user->id, "first_name",  $subscriptionAccountDetails['Name'] ) ;
        update_user_meta( $current_user->id, "last_name",  $subscriptionAccountDetails['LastName'] ) ;

        $args = array(
            'ID'         => $current_user->id,
            'user_email' => esc_attr( $subscriptionAccountDetails['Email'] )
        );
        wp_update_user( $args );

        
        $msg.=__( 'Data is updated successfully!','ntpRp');
        
    }

    $updateSubscriberAccountResult = array(
        'status'=> true,
        'msg'=> $msg
    );
    
    wp_send_json($updateSubscriberAccountResult);
}

function recurring_account_getMyHistory() {
    global $wpdb;
    $obj = new recurringFront();

    /** Get Current user Info */
    $current_user = wp_get_current_user();
    $htmlThem = 'Payment History';

    $userPaymentHistory = $wpdb->get_results("SELECT 
                                                    s.UserId,
                                                    h.Subscription_Id,
                                                    h.TransactionID,
                                                    h.Comment,
                                                    h.Status,
                                                    h.CreatedAt,
                                                    p.Title,
                                                    p.Amount
                                                FROM ".$wpdb->prefix . $obj->getDbSourceName('subscription')." as s
                                                INNER JOIN ".$wpdb->prefix . $obj->getDbSourceName('history')." as h
                                                ON h.Subscription_Id = s.Subscription_Id 
                                                INNER JOIN ".$wpdb->prefix . $obj->getDbSourceName('plan')." as p
                                                ON s.PlanId = p.PlanId
                                                WHERE s.UserId = '$current_user->user_login'
                                                ORDER BY `CreatedAt` DESC", "ARRAY_A");

        $obj = new recurringAdmin();
        for($i = 0; $i < count($userPaymentHistory); $i++) {
            $userPaymentHistory[$i]['Status'] = $obj->getStatusStr('report', $userPaymentHistory[$i]['Status']);
        }
    
    $myHistoryResult = array(
        'status' => true,
        'msg'    => '',
        'data'   => '<div class="row">
                        <h3 class="card-title">'. __($htmlThem,'ntpRp').'<span id="who"></span></h3>
                        </div>
                        <div class="row">
                            <table id="myHistoryDataTable" class="table" width="100%">
                                <thead>
                                <tr>
                                    <th>'. __('Date','ntpRp').'</th>
                                    <th>'. __('Title & Amount','ntpRp').'</th>
                                    <!-- <th>'. __('Transaction ID','ntpRp').'</th> -->
                                    <!-- <th>'. __('Comment','ntpRp').'</th> -->
                                    <th>'. __('Status','ntpRp').'</th> 
                                </tr>
                                </thead>
                                <tbody id="mySubscriberPaymentHistoryList">
                                    <!-- History List -->
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>'. __('Date','ntpRp').'</th>
                                    <th>'. __('Title & Amount','ntpRp').'</th>
                                    <!-- <th>'. __('Transaction ID','ntpRp').'</th> -->
                                    <!-- <th>'. __('Comment','ntpRp').'</th> -->
                                    <th>'. __('Status','ntpRp').'</th> 
                                </tr>
                                </tfoot>
                            </table>
                        </div>',
        'histories'   => $userPaymentHistory,
        );
    wp_send_json($myHistoryResult);
}

function recurring_account_getMySubscriptions() {
    global $wpdb;
    $obj = new recurringFront();

    /** Get Current user Info */
    $current_user = wp_get_current_user();

    $myPlans = $wpdb->get_results("SELECT p.id,
                                          p.PlanId,
                                          p.Title,
                                          p.Amount,
                                          p.Currency,
                                          p.Description,
                                          p.Recurrence_Type,
                                          p.Frequency_Type,
                                          p.Frequency_Value,
                                          p.Grace_Period,
                                          p.Initial_Payment,
                                          p.Status,
                                          s.id as userId,
                                          s.First_Name,
                                          s.Last_Name,
                                          s.Status as userStatus,
                                          s.Subscription_Id
                                    FROM  ".$wpdb->prefix . $obj->getDbSourceName('plan')." as p 
                                    LEFT JOIN ".$wpdb->prefix . $obj->getDbSourceName('subscription')." as s 
                                    ON p.PlanId = s.PlanId 
                                    WHERE s.UserID = '".$current_user->user_login."' AND s.Status <> 2", "ARRAY_A");

    $htmlThem = '';
    if(count($myPlans)) {
        foreach($myPlans as $plan) {
            if($plan['userStatus'] == 3 ) {
                $htmlThem.= '<div class="col-sm-6 pb-2">
                            <div class="card">
                                <div class="card-body">
                                <h2 class="card-title">'.$plan['Title'].'</h2>
                                <h3 class="card-title">'.$plan['Amount'].' '.$plan['Currency'].'</h3>
                                <h4 class="card-title">'.$plan['Frequency_Type'].' / '.$plan['Frequency_Value'].'</h4>
                                <p class="card-text">'.$plan['Description'].'</p>
                                <div class="alert alert-warning">
                                <strong>'.__('Warning!','ntpRp').'</strong>
                                '.$plan['Title'].
                                __(' inactivated for you','ntpRp').'
                                </div>
                                </div>
                            </div>
                        </div>';
            } elseif($plan['userStatus'] == 0 ) {
                $htmlThem.= '<div class="col-sm-6 pb-2">
                            <div class="card">
                                <div class="card-body">
                                <h2 class="card-title">'.$plan['Title'].'</h2>
                                <h3 class="card-title">'.$plan['Amount'].' '.$plan['Currency'].'</h3>
                                <h4 class="card-title">'.$plan['Frequency_Type'].' / '.$plan['Frequency_Value'].'</h4>
                                <p class="card-text">'.$plan['Description'].'</p>
                                <div class="alert alert-danger" role="alert">
                                '.__('Not Approved','ntpRp').'
                                </div>
                                </div>
                            </div>
                        </div>';
            }else {
                $htmlThem.= '<div class="col-sm-6 pb-2">
                            <div class="card">
                                <div class="card-body">
                                <h2 class="card-title">'.$plan['Title'].'</h2>
                                <h3 class="card-title">'.$plan['Amount'].' '.$plan['Currency'].'</h3>
                                <h4 class="card-title">'.$plan['Frequency_Type'].' / '.$plan['Frequency_Value'].'</h4>
                                <p class="card-text">'.$plan['Description'].'</p>
                                <button type="button" class="btn btn-primary unsubscriptionMyAccounButton" title="'.__('Unsubscribe','ntpRp').'" data-subscriptionId="'.$plan['Subscription_Id'].'" data-userId="'.$plan['userId'].'" data-planTitle="'.$plan['Title'].'" data-toggle="modal" data-target="#unsubscriptionMyAccountModal" >
                                    '.__('Unsubscription','ntpRp').'
                                </button>
                                <button type="button" class="btn btn-info" title="'.__('Check next payment','ntpRp').'" onclick="frontSubscriptionNextPayment('.$plan['Subscription_Id'].','.$plan['id'].',\''.$plan['Title'].'\')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                                        <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"></path>
                                        <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"></path>
                                    </svg>
                                </button>
                                </div>
                            </div>
                        </div>';
            }
        }
    } else {
        $htmlThem = '<h4>'.__('You are not subscribe in any of our plans!','ntpRp').'</h4>';
        $htmlThem .= '<h5>'.__('Please, check them out!','ntpRp').'</h5>';
    }
    
    $frontNextPayment = '<!-- Modal -->
                        <div id="nextPaymentModal" class="modal fade" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                    <h2 class="modal-title" id="nextPaymentModalLabel">'. __('Payment Schedule','ntpRp') .'</h2>
                                    <div id="nextPaymentByFrontLoading" class="spinner-grow" style="color: darkgreen" role="status" aria-hidden="true"></div>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    </div>
                                    <div class="modal-body">
                                    <div id="">
                                        '. __('Next payment schedule for ', 'ntpRp').'
                                        <strong>
                                            <span id="subscriberName"></span>
                                        </strong>
                                    </div>        
                                    <div>
                                        <h5>'. __('Date', 'ntpRp') .' : <span id="nextPaymentDate"> - </span></h5>
                                        <h5>'. __('Status', 'ntpRp').' : <span id="nextPaymentStatus"> - </span></h5>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                                    </div>
                                    <div class="alert alert-dismissible fade" id="msgBlock" role="alert">
                                        <strong id="alertTitle">!</strong> <span id="msgContent"></span>.
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>';

    $frontModalUnsubscriptionHtml ='<!-- Modal -->
                                    <div class="modal fade" id="unsubscriptionMyAccountModal" tabindex="-1" aria-labelledby="unsubscriptionRecurringModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <img src="'.URL_NETOPIA_PAYMENTS_LOGO_GLOBAL.'" width="100" style="padding: 5px 15px 0px 0px;">
                                                    <h2 class="modal-title" id="unsubscriptionRecurringModalLabel">'.__('Unsubscription', 'ntpRp').'</h2>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">            
                                                    <div class="row">
                                                        <div class="col-md-12 order-md-1">
                                                            <form id="unsubscription-form" class="needs-validation">
                                                                '.__('Are you sure to unsubscribe from ','ntpRp').'
                                                                <span id="PlanTitle" > - </span> !?
                                                                <br>
                                                                '.__('To unsubscribe click on unsubscribe button.','ntpRp').' '.__('Otherwise close the window','ntpRp').'
                                                                <hr>
                                                                <input type="hidden" class="form-control" id="Id" value="" readonly>
                                                                <input type="hidden" class="form-control" id="Subscription_Id" value="" readonly>
                                                                <button id="unsubscriptionButton" class="btn btn-secondary" type="button" onclick="unsubscriptionMyAccount(); return false;">Unsubscribe</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <div id="loading" class="d-flex align-items-center fade">
                                                        <strong>'.__('Loading...','ntpRp').'</strong>
                                                        <div class="spinner-border ml-auto" role="status" aria-hidden="true"></div>
                                                    </div>
                                                    <div class="alert alert-dismissible fade" id="myAccountMsgBlock" role="alert">
                                                        <strong id="myAccountAlertTitle">!</strong> <span id="myAccountMsgContent"></span>.
                                                    </div>                                
                                                </div>
                                                <div class="modal-footer">
                                                    '.__('Supported by NETOPIA Payments').'
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    ';

    $frontModalAddSubscriptionHtml = '';
                                        
    $mySubscriptionsResult = array(
        'status' => true,
        'msg'    => '',
        'data'   => '<div class="row">
                        '.$htmlThem.'
                    </div>
                    <div class="row">
                    '.$frontNextPayment.'
                    '.$frontModalUnsubscriptionHtml.'
                    '.$frontModalAddSubscriptionHtml.'
                    </div>',
        );


    wp_send_json($mySubscriptionsResult);
}

function assignToRecurring ($data) { 
            $obj = new recurringFront();
            $title  = isset($data['title']) && $data['title'] !== null ? $data['title'] : null;
            $button = isset($data['button']) && $data['button'] !== null ? $data['button'] : null;
            $planId = isset($data['planid']) && $data['planid'] !== null ? $data['planid'] : null;
            
            if(!is_null($planId)) {
                $str = recurringModal ($planId, $button, $title);
            } else {
                $str = ''; 
            } 
    return $str;
}

function ntpMyAccount() {
    $obj = new recurringFront();
    $accountPageContent = $obj->getAccountPageSetting();

    if(is_user_logged_in()) {
        $strHTML = '<div class="row">
                        <div class="" id="">
                            <ul class="nav nav-pills nav-flush flex-column bg-light">
                                <li class="nav-item">
                                    <a href="#" class="nav-link border-bottom" id="frontAccountMysubscription" ><i class="fa fa-bell" style="padding-right:15px;"></i> '.__('My subscriptions', 'ntpRp').'</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link border-bottom" id="frontAccountMyPaymentHistory" ><i class="fas fa-file-invoice-dollar" style="padding-right:15px;"></i> '.__('My History', 'ntpRp').'</a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link border-bottom" id="frontAccountDetails" ><i class="fa fa-user-circle" style="padding-right:15px;"></i> '.__('Account details', 'ntpRp').'</a>
                                </li>
                                <!--<li class="nav-item">
                                    <a href="#" class="nav-link border-bottom" id="frontCardInfoUpdate" ><i class="fa fa-user-circle" style="padding-right:15px;"></i> '.__('Update Card', 'ntpRp').'</a>
                                </li>-->
                                <li class="nav-item">
                                    <a href="#" class="nav-link border-bottom" id="frontAccountLogout" ><i class="fas fa-sign-out-alt" style="padding-right:15px;"></i> '.__('Logout', 'ntpRp').'</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col" id="ntpAccount">
                            <h2 id="ntpAccountSubtitle">'.$accountPageContent['subtitle'].'</h2>
                            <div class="col" id="ntpAccountBody">
                                <p id="ntpAccountP1">'.$accountPageContent['firstParagraph'].'</p>
                                <p id="ntpAccountP2">'.$accountPageContent['secoundParagraph'].'</p>
                            </div>
                        </div>
                    </div>';
    } else {
        $strHTML = '
                        <div class="row" id="ntpAccountLogOut">
                        </div>
                        <div class="row">
                            '.wp_login_form().'
                            <p class="">'.__('Forgot password? Click','ntpRp').' <a href="'.wp_lostpassword_url().'.">'.__('here', 'ntpRp').'</a> '.__('to reset it', 'ntpRp').'.</p>
                        </div>
                        <div class="row" >
                            <div class="col jumbotron text-center alert alert-dismissible fade" id="msgBlock" role="alert">
                                <h1 id="alertTitle" class="display-5"></h1>
                                <p class="lead">
                                    <strong>
                                        <span id="msgContent"></span>
                                    </strong>
                                </p>
                            </div>
                        </div>
                        <script>
                        jQuery("#ntpAccountLogOut").html(jQuery("#loginform"));
                        </script>';
    }
    ob_start();
    echo $strHTML;
    return ob_get_clean();
}

function recurringModal($planId , $button, $title) {
    global $wpdb;
    $obj = new recurringFront();

    /** Get Current user Info */
    $current_user = wp_get_current_user();

    /** Get Plan Info */
    $planData = planInfo($planId);
    $isActivePlan = count($planData) && $planData['Status'] == 1 ? true : false;

    $modalHtml = '';
    $verifyAuthFormHtml = '';
    $suspendedAlertTitile = __('Warning!','ntpRp');
    $suspendedAlertMessage = __(' inactivated for you','ntpRp');
    $notApprovedButtonTitile = __('Not approved yet!','ntpRp');
    $unsubscriptionButtonTitile = __('Unsubscription','ntpRp');
    $unsubscriptionTitle = __('Unsubscription','ntpRp'); 
    
    $buttonTitile = !is_null($button) ? $button : __('Subscription','ntpRp');
    $modalTitle = !is_null($title) ? $title : __('Subscription details','ntpRp');

    $isLoggedIn = $current_user->ID != 0 ? true : false;

    if($isActivePlan) {
        if($isLoggedIn) {    
            /** Check if user already exist */
            $subscription = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix.$obj->getDbSourceName('subscription')."` WHERE `Email` LIKE '".$current_user->user_email."' and `PlanId` = $planId and `Status` <> 2 LIMIT 1");
            if(count($subscription)) {
                if ($subscription[0]->Status == "3") {
                    /** Display Inactive alarm */
                    $buttonHtml = '
                    <!-- Alert trigger -->
                    <div class="alert alert-warning">
                        <strong>'.$suspendedAlertTitile.'</strong> '.$planData['Title'].$suspendedAlertMessage.'.
                    </div>';
                } elseif ($subscription[0]->Status == "0") {
                    $buttonHtml = '
                    <!-- Button Disable -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="" disabled>
                        '.$notApprovedButtonTitile.'
                    </button>';
                } else {
                    /** Display Unsubscriptiuon */
                    $buttonHtml = '
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#unsubscriptionRecurringModal'.$planId.'">
                        '.$unsubscriptionButtonTitile.'
                    </button>';
                $modalHtml = getUnsubscribeModalHtml($planId, $unsubscriptionTitle, $planData, $subscription);
                }
            } else {
                /** Display Subscribe buttomn & Modal subscription for Logedin users */    
                $buttonHtml = '
                <!-- Button trigger modal -->
                <button type="button" id="'.$planId.'" class="btn btn-primary" data-toggle="modal" data-target="#recurringModal'.$planId.'">
                    '.$buttonTitile.'
                </button>';


            if(isset($_POST) && !empty($_POST) && isset($_GET['planId']) && ($_GET['planId'] == $planId)) {
                
                /** Build Verify Auth Form */
                $verifyAuthFormHtml .= generateVerifyAuthForm($_POST, $planId);

                /** call verify auth */
                $buttonHtml .= '<div id="loading-verifyAuthForm'.$planId.'" class="d-flex align-items-center fade show" role="alert">
                                    <strong>'.__('Completing the process...','ntpRp').'</strong>
                                    <div class="spinner-border ml-auto" role="status" aria-hidden="true"></div>
                                </div>
                                <div class="alert alert-dismissible fade" id="msgBlock-verifyAuthForm'.$planId.'" role="alert">
                                    <strong id="alertTitle-verifyAuthForm'.$planId.'">!</strong> <span id="msgContent-verifyAuthForm'.$planId.'"></span>.
                                </div> ';

                $buttonHtml .= $verifyAuthFormHtml;
            }


            
        
            $cardInfo    = getCardInfoHtml($planId);
            $threeDsForm = get3DsFormHtml($planId);
            $userInfo    = getMemberInfoHtml();
            $authInfo    = getAuthFromHtml($isLoggedIn);
            $modalHtml   = getModalHtml($planId, $modalTitle, $planData, $userInfo, $authInfo, $cardInfo, $threeDsForm);
            }
        } else {
            /** Display Subscribe buttomn & Modal subscription for Guest users */    
            $buttonHtml = '
                <!-- Button trigger modal -->
                <button type="button" id="'.$planId.'" class="btn btn-primary" data-toggle="modal" data-target="#recurringModal'.$planId.'">
                    '.$buttonTitile.'
                </button>';

            if(isset($_POST) && !empty($_POST) && isset($_GET['planId']) && ($_GET['planId'] == $planId)) {
                /** Build Verify Auth Form */
                $verifyAuthFormHtml .= generateVerifyAuthForm($_POST, $planId);
                
                /** call verify auth */
                $buttonHtml .= '<div id="loading-verifyAuthForm'.$planId.'" class="d-flex align-items-center fade show" role="alert">
                                    <strong>'.__('Completing the process...','ntpRp').'</strong>
                                    <div class="spinner-border ml-auto" role="status" aria-hidden="true"></div>
                                </div>
                                <div class="alert alert-dismissible fade" id="msgBlock-verifyAuthForm'.$planId.'" role="alert">
                                    <strong id="alertTitle-verifyAuthForm'.$planId.'">!</strong> <span id="msgContent-verifyAuthForm'.$planId.'"></span>.
                                </div> ';

                $buttonHtml .= $verifyAuthFormHtml;
            }          
           
            $cardInfo    = getCardInfoHtml($planId);
            $threeDsForm = get3DsFormHtml($planId);
            $userInfo    = getMemberInfoHtml();
            $authInfo    = getAuthFromHtml($isLoggedIn);
            $modalHtml   = getModalHtml($planId, $modalTitle, $planData, $userInfo, $authInfo, $cardInfo, $threeDsForm);
        }
    } else {
        // Plan is not active / not exist / The licence is expired,... so don't display subscribe / Unsubscribe Botton
        $buttonHtml = '';
    }

    return $modalHtml.$buttonHtml;
}

function getUnsubscribeModalHtml ($planId, $unsubscriptionTitle, $planData, $subscription) {
    return '<!-- Unsubscription Modal -->
            <div class="modal fade unsubscriptionRecurringModal" id="unsubscriptionRecurringModal'.$planId.'" tabindex="-1" aria-labelledby="unsubscriptionRecurringModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <img src="'.URL_NETOPIA_PAYMENTS_LOGO_GLOBAL.'" width="100" style="padding: 5px 15px 0px 0px;">
                            <h2 class="modal-title" id="unsubscriptionRecurringModalLabel">'.$unsubscriptionTitle.'</h2>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">            
                            <div class="row">
                                <div class="col-md-12 order-md-1">
                                    <form id="unsubscription-form'.$subscription[0]->Subscription_Id.'" class="unsubscription-form needs-validation">
                                        '.__('Are you sure to unsubscribe from ','ntpRp').'
                                        '.$planData['Title'].' !?
                                        <br>
                                        '.__('To unsubscribe click on unsubscribe button.','ntpRp').' '.__('Otherwise close the window','ntpRp').'
                                        <hr>
                                        <input type="hidden" class="form-control" id="planId" name="planId" value="'.$planId.'" readonly>
                                        <input type="hidden" class="form-control" id="Id" name="Id" value="'.$subscription[0]->id.'" readonly>
                                        <input type="hidden" class="form-control" id="Subscription_Id" name="Subscription_Id" value="'.$subscription[0]->Subscription_Id.'" readonly>
                                        <button id="unsubscriptionButton'.$planId.'" class="btn btn-secondary" type="submit" >Unsubscribe</button>
                                    </form>
                                </div>
                            </div>
                            <div id="loading'.$subscription[0]->Subscription_Id.'" class="d-flex align-items-center fade">
                                <strong>'.__('Loading...','ntpRp').'</strong>
                                <div class="spinner-border ml-auto" role="status" aria-hidden="true"></div>
                            </div>
                            <div class="alert alert-dismissible fade" id="msgBlock'.$subscription[0]->Subscription_Id.'" role="alert">
                                <strong id="alertTitle'.$subscription[0]->Subscription_Id.'">!</strong> <span id="msgContent'.$subscription[0]->Subscription_Id.'"></span>.
                            </div>                                
                        </div>
                        '.getWarningFront().'
                        <div class="modal-footer">
                            '.__('Supported by NETOPIA Payments').'
                        </div>
                    </div>
                </div>
            </div>
            ';
}

function getModalHtml($planId, $modalTitle, $planData, $userInfo, $authInfo, $cardInfo, $threeDsForm) {
    if($planData['GracePeriod'] == 0 ) {
        $gracePeriodStr = '';
    } else {
        $gracePeriodStr = '<h5 class="card-title ">'.__("Grace period","ntpRp").' : '.$planData['GracePeriod'] . " " . $planData['Frequency']['Type'].'</h5>';
    }

    
    return '<!-- Modal -->
    <div class="modal fade recurringModal" id="recurringModal'.$planId.'" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <img src="'.URL_NETOPIA_PAYMENTS_LOGO_GLOBAL.'" width="100" style="padding: 5px 15px 0px 0px;">
                    <h2 class="modal-title" id="recurringModalLabel">'.$modalTitle.'</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">            
                    <div class="row">
                        <div class="col-md-12 order-md-1">
                        <h4 class="mb-3">'.__('Subscription detail','ntpRp').'</h4>
                        <form id="subscription-form'.$planId.'" class="add-subscription-form needs-validation">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <h3><b>'.$planData['Title'].'</b></h3>
                                        <h4>'.$planData['Description'].'</h4>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card mb-4 box-shadow">
                                        <div class="card-header">
                                            <h4 class="my-0 font-weight-normal">'.__('Amount','ntpRp').'</h4>
                                        </div>
                                        <div class="card-body">
                                            <h3 class="card-title pricing-card-title">'.$planData['Amount'].' '.$planData['Currency'].' <small class="text-muted">/ '.$planData['Frequency']['Value'].' '.$planData['Frequency']['Type'].'</small></h3>
                                            '.$gracePeriodStr.'
                                            <input type="hidden" class="form-control" id="planID" name="planID" value="'.$planId.'">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            '.
                            $userInfo
                            .
                            $authInfo
                            .'
                            <hr class="mb-4">
                            '.
                            $cardInfo
                            .'
                            <hr class="mb-4">
                            <button id="addSubscriptionButton'.$planId.'" class="btn btn-primary btn-lg btn-block ntpRecurringCheckout" type="submit">Continue to checkout</button>
                        </form>
                        </div>
                    </div>
                    <div id="loading'.$planId.'" class="d-flex align-items-center fade">
                        <strong>'.__('Loading...','ntpRp').'</strong>
                        <div class="spinner-border ml-auto" role="status" aria-hidden="true"></div>
                    </div>
                        <div class="alert alert-dismissible fade" id="msgBlock'.$planId.'" role="alert">
                            <strong id="alertTitle'.$planId.'">!</strong> <span id="msgContent'.$planId.'"></span>.
                            <div id="spinner'.$planId.'" class="spinner-grow text-primary d-none"  role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="align-items-center">
                    '.
                    $threeDsForm
                    .'
                    </div>
                    '.getWarningFront().'
                <div class="modal-footer">
                    '.__('Supported by NETOPIA Payments','ntpRp').'
                </div>
            </div>
        </div>
    </div>';
}

function getAuthFromHtml($isLoggedIn) {
    if($isLoggedIn) {
        return null;
    } else {
        /** Get Current user Info */
        $current_user = wp_get_current_user();

        return '
        <hr class="mb-4">
        <h4 class="mb-3">'.__('Auth information').'</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="username">'.__('Username','ntpRp').'</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">@</span>
                    </div>
                    <input type="text" class="form-control" id="username" name="username" pattern=".{5,}" placeholder="Username" value="'.$current_user->user_login.'" title="5 characters minimum" required>
                    <div class="invalid-feedback" style="width: 100%;">
                    '.__('Your username is required.','ntpRp').'
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="password">'.__('Password','ntpRp').'</label>
                <input type="password" class="form-control" id="password" name="password" pattern="(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])\S{6,}" title="6 characters minimum. Contain at least one mixed-case leter and a number." required>
                <div class="invalid-feedback">
                    '.__('Please enter a valid password.','ntpRp').'
                </div>
            </div>
        </div>
        ';
        }
}

function getMemberInfoHtml() {
    global $wpdb;
    $obj = new recurringFront();
    
    /** Get Current user Info */
    $current_user = wp_get_current_user();
    $isLoggedIn = $current_user->ID != 0 ? true : false;

    /** Warrning for un complited info */
    $htmlCompletPersonalData = '
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                '.__('Dear', 'ntpRp').' <strong>'.$current_user->user_login.'!</strong> '.__('You should complete your personal data,like <b>Address, City, Tel</b>, ... from', 'ntpRp').'  <a href="subscription-account">'.__('Account details', 'ntpRp').'</a> '.__('before subscription.', 'ntpRp').'
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                ';

    /**
     *  Get Current user Info 
     *  If subscriptioin pesonal Data will not found, 
     *  Then user personal data will added during subscription.
     *  If Personal data will found in DB but is not complited user will recive an Alert to complete personal data
     * */
    if($isLoggedIn) {
        $subscriptionInfo = $wpdb->get_results("SELECT * FROM `".$wpdb->prefix.$obj->getDbSourceName('subscription')."` WHERE `UserID` LIKE '".$current_user->user_login."'");
        if(count($subscriptionInfo)) {
            $subscriptionInfo = $subscriptionInfo[0];
            if(empty($subscriptionInfo->Address) || empty($subscriptionInfo->City) || empty($subscriptionInfo->Tel)) {
                // subscriptioin pesonal Data is not complited, So must completed the data first
                echo ($htmlCompletPersonalData);
            } 
        }
    }

    $firstName = !is_null($current_user->first_name) ? $current_user->first_name : "";
    $lastName = !is_null($current_user->last_name) ? $current_user->last_name : "";
    $userEmail = !is_null($current_user->user_email) ? $current_user->user_email : "";
    $userAddress  = isset($subscriptionInfo->Address) && !is_null($subscriptionInfo->Address) ? $subscriptionInfo->Address : "";
    $userCity = isset($subscriptionInfo->City) && !is_null($subscriptionInfo->City) ? $subscriptionInfo->City : "";
    $userTel = isset($subscriptionInfo->Tel) && !is_null($subscriptionInfo->Tel) ? $subscriptionInfo->Tel : "";
    
    
    /** Generate HTML form */
    $memberInfoHtmlForm = '
                            <hr class="mb-4">
                            <h4 class="mb-3">'.__('Personal information').'</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName">'.__('First name','ntpRp').'</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" pattern=".{3,}"  title="'.__('please, fill out with full name (3 characters minimum)','ntpRp').' placeholder="" value="'.$firstName.'" required>
                                    <div class="valid-feedback">
                                    '.__('Looks good!').'
                                    </div>
                                    <div class="invalid-feedback">
                                        '.__('Valid first name is required.','ntpRp').'
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName">'.__('Last name','ntpRp').'</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" pattern=".{4,}" title="'.__('please, fill out with full last name (4 characters minimum)','ntpRp').' placeholder="" value="'.$lastName.'" required>
                                    <div class="valid-feedback">
                                    '.__('Looks good!').'
                                    </div>
                                    <div class="invalid-feedback">
                                        '.__('Valid last name is required.','ntpRp').'
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-8 mb-9">
                                    <label for="address">'.__('Address','ntpRp').'</label>
                                    <input type="text" class="form-control" id="address" name="address" pattern=".{10,}" title="'.__('please, fill out with full address (10 characters minimum)','ntpRp').' placeholder="'.__('Subscription address, Ex. Main street, Floor, Nr,... ','ntpRp').'" value="'.$userAddress.'" required>
                                    <div class="invalid-feedback">
                                        '.__('Please enter your shipping address.','ntpRp').'
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="email">'.__('Email','ntpRp').'</label>
                                    <input type="email" class="form-control" id="email" name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" placeholder="you@example.com" value="'.$userEmail.'" required>
                                    <div class="invalid-feedback">
                                        '.__('Please enter a valid email address for shipping updates.','ntpRp').'
                                    </div>
                                </div>
                            </div>    
                            
                            <div class="row">
                                <div class="col-md-5 mb-3">
                                    <label for="tel">'.__('Tel','ntpRp').'</label>
                                    <input type="text" class="form-control" id="tel" name="tel" placeholder="" pattern="[0-9]{5,14}" title="'.__('please, fill out with correct phone number! (only digit)','ntpRp').'" value="'.$userTel.'" required>
                                    <div class="invalid-feedback">
                                        '.__('Phone required.','ntpRp').'
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="country">'.__('Country','ntpRp').'</label>
                                    <select class="custom-select d-block w-100" id="country" required>
                                    <option value="">Choose...</option>
                                    <option value="642" selected>Romania</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        '.__('Please select a valid country.','ntpRp').'
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state">'.__('State','ntpRp').'</label>
                                    <select class="custom-select d-block w-100" id="state" name="state" required>'
                                    .getJudete().
                                    '</select>
                                    <div class="invalid-feedback">
                                        '.__('Please provide a valid state.','ntpRp').'
                                    </div>
                                </div>                        
                            </div>';

        /** Missing Personal Item HTML form */
        $memberMissingInfoHtmlForm = '
        <hr class="mb-4">
        <h4 class="mb-3">'.__('Personal information').'</h4>
        
        
        <div class="row">
            <div class="col">
                <label for="address">'.__('Address','ntpRp').'</label>
                <input type="text" class="form-control" id="address" name="address" pattern=".{10,}" title="'.__('please, fill out with full address (10 characters minimum)','ntpRp').' placeholder="'.__('Subscription address, Ex. Main street, Floor, Nr,... ','ntpRp').'" value="'.$userAddress.'" required>
                <div class="invalid-feedback">
                    '.__('Please enter your shipping address.','ntpRp').'
                </div>
            </div>
        </div>    
        
        <div class="row">
            <div class="col-md-5 mb-3">
                <label for="tel">'.__('Tel','ntpRp').'</label>
                <input type="text" class="form-control" id="tel" name="tel" placeholder="" pattern="[0-9]{5,14}" title="'.__('please, fill out with correct phone number! (only digit)','ntpRp').'" value="'.$userTel.'" required>
                <div class="invalid-feedback">
                    '.__('Phone required.','ntpRp').'
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="country">'.__('Country','ntpRp').'</label>
                <select class="custom-select d-block w-100" id="country" required>
                <option value="">Choose...</option>
                <option value="642" selected>Romania</option>
                </select>
                <div class="invalid-feedback">
                    '.__('Please select a valid country.','ntpRp').'
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="state">'.__('State','ntpRp').'</label>
                <select class="custom-select d-block w-100" id="state" name="state" required>'
                .getJudete().
                '</select>
                <div class="invalid-feedback">
                    '.__('Please provide a valid state.','ntpRp').'
                </div>
            </div>                        
        </div>';

    if($isLoggedIn) {
        if(empty($subscriptionInfo->Address) || empty($subscriptionInfo->City) || empty($subscriptionInfo->Tel)) {
            // subscriptioin pesonal Data is not complited, 
            // Should be display form &  completed the data Durring subscription
            return $memberMissingInfoHtmlForm;
        } else {
            return null;
        }       
    } else {
        return $memberInfoHtmlForm;
    }
}

function generateVerifyAuthForm($postParams, $planId) {
    $tmpHtml = "
        <script language='Javascript' >
            var verifyAuthDynamicForm = document.createElement('form');
            verifyAuthDynamicForm.setAttribute('method', 'post');
            verifyAuthDynamicForm.setAttribute('id', 'verifyAuthForm".$planId."');
            verifyAuthDynamicForm.setAttribute('name', 'verifyAuthForm".$planId."');
            ";
                
        // create input elements for dynamic form
        foreach($postParams as $key => $val){
            $tmpHtml .= "
            var tmpVar".$key." = document.createElement('input');
                tmpVar".$key.".setAttribute('type', 'hidden');
                tmpVar".$key.".setAttribute('name', '".$key."');
                tmpVar".$key.".setAttribute('value','".$val."');
                verifyAuthDynamicForm.appendChild(tmpVar".$key.");
                ";
        }        

        $tmpHtml .= "
        // create a submit button
            var s = document.createElement('input');
                s.setAttribute('type', 'button');
                s.setAttribute('value', 'Verify-Auth submit');
                s.setAttribute('id', 'VerifyAuthButton".$planId."');
                s.setAttribute('name', 'VerifyAuthSubmmit".$planId."');
                s.setAttribute('style', 'display : none');
                s.setAttribute('class', 'verify-action-botton');

            // Append the form elements to the form
            verifyAuthDynamicForm.appendChild(s);

            document.getElementsByTagName('body')[0].appendChild(verifyAuthDynamicForm);

            /**
            * Redirect the authorizeForm to bank for Authorize
            */
             VerifyAuthAction(s, ".$planId.");            
        </script>";
    return $tmpHtml;
}

function getCardInfoHtml($planId) {
    $minYear = Date('Y');
    $maxYear = date('Y', strtotime('+10 year'));
    return '<h4 class="mb-3">'.__('Payment information', 'ntpRp').'</h4>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label for="cc-name">Name on card</label>
            <input type="text" class="form-control" id="cc-name" name="cc-name" pattern=".{3,}" title="'.__('please, fill out with full name!','ntpRp').' placeholder="" required>
            <small class="text-muted">Full name as displayed on card</small>
            <div class="invalid-feedback">
                Name on card is required
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <label for="cc-number">Credit card number</label>
            <input type="text" class="form-control" id="cc-number" name="cc-number" pattern="[0-9]{16}" placeholder="" title="'.__('Card number must contain 16 number (Only number)','ntpRp').'" required>
            <div class="invalid-feedback">
                Credit card number is required
            </div>
        </div>
        <div class="col-md-2 mb-2">
            <label for="cc-expiration-month">'.__('Exp Date','ntpRp').'</label>
            <input id="cc-exp" type="tel" class="cc-exp cc-exp_recurring_'.$planId.' form-control" placeholder="MM / YY" autocompletetype="cc-exp" required="required">
            <input type="hidden" min="1" max="12" class="form-control" id="cc-expiration-month_'.$planId.'" name="cc-expiration-month" placeholder=""  title="'.__('Month moust be a number between 1 and 12','ntpRp').'" required>
            <input type="hidden" min="'.$minYear.'" max="'.$maxYear.'" class="form-control" id="cc-expiration-year_'.$planId.'" name="cc-expiration-year" pattern="[0-9]{4}" placeholder="" title="'.__('Expire year must contain 4 number','ntpRp').'" required>
        </div>
        <div class="col-md-2 mb-2">
            <label for="cc-expiration">CVV</label>
            <input type="text" maxlength="4"  class="form-control" id="cc-cvv" name="cc-cvv" pattern="[0-9]{3,4}" placeholder="" title="'.__('CVV must number and contain min 3 digit','ntpRp').'" required>
            <div class="invalid-feedback">
                Security code required
            </div>
        </div>
    </div>';
}

function get3DsFormHtml($planId) {
    $obj = new recurringFront();
    $backUrl = $obj->getBackUrl($planId);
    $clientIP = getClientIP();
    return '
        <div id="dynamicForm'.$planId.'"></div>
        <input type="hidden" class="form-control" id="backUrl'.$planId.'" name="backUrl" value="'.$backUrl.'" title="independent element" readonly >
        <input type="hidden" class="form-control" id="clientIP" name="clientIP" value="'.$clientIP['ip'].'" title="independent element" readonly >
    ';
}

function getWarningFront() {
    $obj = new recurringFront();
    if($obj->isLive()) {
        return '';
    } else {
        return '<div class="alert alert-warning alert-dismissible fade show float-left" role="alert">
                <strong>'.__('Warning!','ntpRp').'</strong> '.__('You are in test mod', 'ntpRp').'
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    }
}

function getJudete($selectedStr = "") {
    $judete = array(
        'Alba' => 'Alba',
        'Argeș' => 'Argeș',
        'Arad' => 'Arad',
        'București' => 'București',
        'Bacău' => 'Bacău',
        'Bihor' => 'Bihor',
        'Bistrița Năsăud' => 'Bistrița Năsăud',
        'Brăila' => 'Brăila',
        'Botoșani' => 'Botoșani',
        'Brașov' => 'Brașov',
        'Buzău' => 'Buzău',
        'Cluj' => 'Cluj',
        'Călărași' => 'Călărași',
        'Caraș-Severin' => 'Caraș-Severin',
        'Constanța' => 'Constanța',
        'Covasna' => 'Covasna',
        'Dâmbovița' => 'Dâmbovița',
        'Dolj' => 'Dolj',
        'Gorj' => 'Gorj',
        'Galați' => 'Galați',
        'Giurgiu' => 'Giurgiu',
        'Hunedoara' => 'Hunedoara',
        'Harghita' => 'Harghita',
        'Ilfov' => 'Ilfov',
        'Ialomița' => 'Ialomița',
        'Iași' => 'Iași',
        'Mehedinți' => 'Mehedinți',
        'Maramureș' => 'Maramureș',
        'Mureș' => 'Mureș',
        'Neamț' => 'Neamț',
        'Olt' => 'Olt',
        'Prahova' => 'Prahova',
        'Sibiu' => 'Sibiu',
        'Sălaj' => 'Sălaj',
        'Satu-Mare' => 'Satu-Mare',
        'Suceava' => 'Suceava',
        'Tulcea' => 'Tulcea',
        'Timiș' => 'Timiș',
        'Teleorman' => 'Teleorman',
        'Vâlcea' => 'Vâlcea',
        'Vrancea' => 'Vrancea',
        'Vaslui' => 'Vaslui'
        );
    $strObtion = '<option value="">Choose...</option>';        
    foreach($judete as $key => $value) {
        if($selectedStr == "") {
            $strObtion .='<option value="'.$key.'">'.$value.'</option>';
        } else {
            if($selectedStr == $value ) {
                $strObtion .='<option value="'.$key.'" selected >'.$value.'</option>';
            } else {
                $strObtion .='<option value="'.$key.'" >'.$value.'</option>';
            }
        }
    }
    return $strObtion;
}

function planInfo($planId) {
    $a = new recurringFront();
    $arrayData = $a->getPlan($planId);

    if(is_null($arrayData) || isset($arrayData['code']) && in_array($arrayData['code'], array(11, 12, 404))) {
        $planData = array();
    } else {
        $plan = $arrayData['plan'];
        $planData = array(
            "Title" => $plan['Title'],
            "Description" => $plan['Description'],
            "Amount" => $plan['Amount'],
            "Currency" => $plan['Currency'],
            "RecurrenceType" => $plan['RecurrenceType'],
            "Frequency" => array (
                "Type" => $plan['Frequency']['Type'],
                "Value" => $plan['Frequency']['Value']
            ),
            "GracePeriod" => $plan['GracePeriod'],
            "InitialPayment" => $plan['InitialPayment'],
            "Status" => $plan['Status']
        );
    } 
    return $planData;
}

function authenticateUser($userInfo) {

    global $wpdb;
    $current_user = wp_get_current_user();
    if($current_user->ID == 0) {
        if(is_email($userInfo['Email']) != false ) {
            // Create User
            createUser($userInfo);
        } else {
            $authenticateResult = array(
                'status'=> false,
                'msg'=> __('Email is not correct!', 'ntpRp'),
                );
            // Can't use wp_send_json must be die
            echo json_encode($authenticateResult);
            wp_die();
        }        
    } else {
        if($userInfo['UserID'] != $current_user->user_login || $userInfo['Email'] != $current_user->user_email ) {
            $authenticateResult = array(
                'status'=> false,
                'msg'=> __('Username or Email is not correct! | You already have an account.', 'ntpRp'),
                );
            // Can't use wp_send_json must be die
            echo json_encode($authenticateResult);
            wp_die();
        }
    }
}

function createUser($userInfo) {
    if(email_exists($userInfo['Email']) || username_exists($userInfo['UserID'])) {
        $obj = new recurringFront();
        $loginUrlLink = $obj->getLoginUrl();
        $userExist = array(
            'status'=> false,
            'msg'=> email_exists($userInfo['Email']) && username_exists($userInfo['UserID']) ? __('This user is already exist! Please Signin first.','ntpRp').'<a href="'.$loginUrlLink.'">'.__('Sign In here', 'ntpRp').'</a>' : __('The user or email are already exist!.', 'ntpRp'),
            );

        // Can't use wp_send_json must be die
        echo json_encode($userExist);
        wp_die();
    } else {
        $createdUserID = wp_create_user( $userInfo['UserID'], $userInfo['Pass'], $userInfo['Email'] );
        if($createdUserID) {
            update_user_meta( $createdUserID, "first_name",  $userInfo['Name'] ) ;
            update_user_meta( $createdUserID, "last_name",  $userInfo['LastName'] ) ;
            
            // Login auto the new user to wordpress
            clean_user_cache($createdUserID);
            wp_clear_auth_cookie();
            wp_set_current_user($createdUserID);
            wp_set_auth_cookie($createdUserID, true, false);

            $user = get_user_by('id', $createdUserID);
            update_user_caches($user);
        } else {
            /** Log */
            write_log('-- { Create User is FAILED!!!!  } ----------');
        }
    }
}

function isStrongPass($passwordStr) {
    $pattern = '/^(?=.*[!@#$%^&*-])(?=.*[0-9])(?=.*[A-Z]).{8,20}$/';
    if(preg_match($pattern, $passwordStr)){
        return true;
    } else {
        return false;
    }
}

function getClientIP() {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    return (array('ip' => $ipAddress));
    // echo json_encode(array('ip' => $ipAddress));
    // wp_die();
}
?>