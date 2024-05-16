<?php
/**
 * NETOPIA Payments - Recurring payment
 * Admin section
 */

add_action( 'admin_enqueue_scripts', 'enqueue_and_register_ntp_recurring_admin_js_scripts' );
function enqueue_and_register_ntp_recurring_admin_js_scripts(){
    wp_enqueue_style( 'ntp_recurring_admin_css', plugin_dir_url( __FILE__ ) . 'css/bootstrap/bootstrap.min.css',array(),'2.0' ,false);
    wp_enqueue_style( 'ntp_recurring_admin_css_fontawesome', plugin_dir_url( __FILE__ ) . 'css/fontawesome/css/all.css',array(),'2.0' ,false);
    wp_enqueue_style( 'ntp_recurring_admin_css_datatables', plugin_dir_url( __FILE__ ) . 'css/addons/datatables.min.css',array(),'2.0' ,false);
    wp_enqueue_style( 'ntp_recurring_admin_css_toastr', plugin_dir_url( __FILE__ ) . 'css/toastr.min.css',array(),'3.0.0' ,false);
    wp_enqueue_style( 'ntp_recurring_admin_css_custom', plugin_dir_url( __FILE__ ) . 'css/style.css',array(),'3.0.0' ,false);

    wp_register_script( 'ntp_recurring_admin_script-popper', plugin_dir_url( __FILE__ ) . 'js/popper.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'ntp_recurring_admin_script-popper' );

    wp_register_script( 'ntp_recurring_admin_script-bootstrap', plugin_dir_url( __FILE__ ) . 'js/bootstrap/bootstrap.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'ntp_recurring_admin_script-bootstrap' );

    wp_register_script( 'ntp_recurring_admin_script-mdb', plugin_dir_url( __FILE__ ) . 'js/mdb.js', array(), '1.0.0', true );
    wp_enqueue_script( 'ntp_recurring_admin_script-mdb' );

    wp_register_script( 'ntp_recurring_admin_script-datatables', plugin_dir_url( __FILE__ ) . 'js/addons/datatables.min.js', array(), '1.0.0', true );
    wp_enqueue_script( 'ntp_recurring_admin_script-datatables' );
    
    wp_register_script( 'ntp_recurring_admin_script-jquery-dataTables', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.js', array(), '1.0.0', true );
    wp_enqueue_script( 'ntp_recurring_admin_script-jquery-dataTables' );
    
    wp_register_script( 'ntp_recurring_admin_script-dataTables-scroller', plugin_dir_url( __FILE__ ) . 'js/dataTables.scroller.js', array(), '1.0.0', true );
    wp_enqueue_script( 'ntp_recurring_admin_script-dataTables-scroller' );

    wp_register_script( 'ntp_recurring_admin_script', plugin_dir_url( __FILE__ ) . 'js/recurringAdmin.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'ntp_recurring_admin_script' );

    wp_register_script( 'ntp_recurring_admin_toastr_script', plugin_dir_url( __FILE__ ) . 'js/toastr.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'ntp_recurring_admin_toastr_script' );
 }
 
class NetopiapaymentsRecurringPayment extends recurring
{
    protected $page_title = 'NETOPIA Payments Recurring Plugin';
    protected $menu_title = 'Recurring v1';
    protected $menuItems;
        
    public function __construct()
    {  
    add_action( 'admin_menu', array( $this, 'create_plugin_settings' ));
    add_action( 'admin_init', array( $this, 'recurring_setup_section' ));
    add_action( 'admin_init', array( $this, 'recurring_setup_fields' ));

    // add_action( 'admin_init', array( $this, 'recurring_notify_section' ));
    // add_action( 'admin_init', array( $this, 'recurring_notify_fields' ));
    
    add_action( 'admin_init', array( $this, 'recurring_account_section' ));
    add_action( 'admin_init', array( $this, 'recurring_account_fields' ));
    
    add_action( 'admin_init', array( $this, 'recurring_message_section' ));
    add_action( 'admin_init', array( $this, 'recurring_message_fields' ));
    }

    public function create_plugin_settings() {
        // Add the menu item and page
        $this->menuItems = array(
            'main'=> array(
            'capability' => 'manage_options',
            'callback' => array( $this, 'dashbord' ),
            'icon' => 'dashicons-format-status',
            'position' => 99,
            'menuTitle' => 'Dashbord',
            'menuSlug' => 'recurring_dashbord'
            ),            
        'subscriptionAjax'=> array(
            'capability' => 'manage_options',
            'callback' => array( $this, 'subscription_UI_Ajax' ),
            'icon' => 'dashicons-format-status',
            'position' => 101,
            'pageTitle' => __('Subscription Management','ntpRp'),
            'menuTitle' => __('Subscriptions','ntpRp'),
            'menuSlug' => 'recurring_subscription_ajax'
        ),         
        'plan'=> array(
            'capability' => 'manage_options',
            'callback' => array( $this, 'plan_UI' ),
            'icon' => 'dashicons-format-status',
            'position' => 102,
            'pageTitle' => __('Plan Management','ntpRp'),
            'menuTitle' => __('Plans','ntpRp'),
            'menuSlug' => 'recurring_plan'
        ),        
        'report'=> array(
            'capability' => 'manage_options',
            'callback' => array( $this, 'report_UI' ),
            'icon' => 'dashicons-format-status',
            'position' => 103,
            'pageTitle' => __('Report Management','ntpRp'),
            'menuTitle' => __('Reports','ntpRp'),
            'menuSlug' => 'recurring_repport'
        )           
    );


        add_menu_page( $this->page_title, $this->menu_title, $this->menuItems['main']['capability'], $this->slug, $this->menuItems['main']['callback'], $this->menuItems['main']['icon'], $this->menuItems['main']['position'] );
        add_submenu_page($this->slug, $this->menuItems['subscriptionAjax']['pageTitle'], $this->menuItems['subscriptionAjax']['menuTitle'], $this->menuItems['subscriptionAjax']['capability'], $this->menuItems['subscriptionAjax']['menuSlug'], $this->menuItems['subscriptionAjax']['callback'] );
        add_submenu_page($this->slug, $this->menuItems['plan']['pageTitle'], $this->menuItems['plan']['menuTitle'], $this->menuItems['plan']['capability'], $this->menuItems['plan']['menuSlug'], $this->menuItems['plan']['callback'] );
        add_submenu_page($this->slug, $this->menuItems['report']['pageTitle'], $this->menuItems['report']['menuTitle'], $this->menuItems['report']['capability'], $this->menuItems['report']['menuSlug'], $this->menuItems['report']['callback'] );
    }


    public function recurring_account_section() {
        add_settings_section( 'account', 'Account page setting ', array( $this, 'section_callback' ), 'account_management' );
    }

    public function recurring_account_fields() {
        $fields = array(
            array(
                'uid' => $this->slug.'_account_subtitle',
                'label' => __('Subtitle for Recurring account page'),
                'section' => 'account',
                'type' => 'text',
                'options' => false,
                'placeholder' => __('A title to display at top of the page'),
                'helper' => __(''),
                'supplemental' => __('Subtitle to display at top of recurring account page'),
                'default' => 'My subscription account'
            ),
            array(
                'uid' => $this->slug.'_account_paragraph_first',
                'label' => __('First paragraph to display at recurring account page body'),
                'section' => 'account',
                'type' => 'textarea',
                'options' => false,
                'placeholder' => __('A custom explaination for account page.'),
                'helper' => __(''),
                'supplemental' => __('A paragraph to explain account page to display in body of account page.'),
                'default' => ''
            ),
            array(
                'uid' => $this->slug.'_account_paragraph_secound',
                'label' => __('Secound paragraph to display at recurring account page body'),
                'section' => 'account',
                'type' => 'textarea',
                'options' => false,
                'placeholder' => __('Another custom explaination for account page'),
                'helper' => __(''),
                'supplemental' => __('Another paragraph to explain account page to display in body of account page.'),
                'default' => ''
            )
        );
        foreach( $fields as $field ){
            add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'account_management', $field['section'], $field );
            register_setting( 'account_management', $field['uid'] );
        }
    }

    public function recurring_message_section() {
        add_settings_section( 'message', 'Default message ', array( $this, 'section_callback' ), 'message_management' );
    }

    public function recurring_message_fields() {
        $fields = array(
            array(
                'uid' => $this->slug.'_subscription_reg_msg',
                'label' => __('Success message of subscription'),
                'section' => 'message',
                'type' => 'textarea',
                'options' => false,
                'placeholder' => __('A success message to display after subscription'),
                'helper' => __(''),
                'supplemental' => __('Success subscription message'),
                'default' => ''
            ),
            array(
                'uid' => $this->slug.'_subscription_reg_failed_msg',
                'label' => __('Failed message of subscription'),
                'section' => 'message',
                'type' => 'textarea',
                'options' => false,
                'placeholder' => __('A failed message to display after failed subscription'),
                'helper' => __(''),
                'supplemental' => __('Failed subscription message'),
                'default' => ''
            ),
            array(
                'uid' => $this->slug.'_unsubscription_msg',
                'label' => __('Unsubscription message'),
                'section' => 'message',
                'type' => 'textarea',
                'options' => false,
                'placeholder' => __('A message to display after unsubscription.'),
                'helper' => __(''),
                'supplemental' => __('Unsubscription message'),
                'default' => ''
            )
        );

        foreach( $fields as $field ){
            add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'message_management', $field['section'], $field );
            register_setting( 'message_management', $field['uid'] );
        }
    }

    public function recurring_setup_section() {
        add_settings_section( 'general', 'General section ', array( $this, 'section_callback' ), 'netopia_recurring' );
        add_settings_section( 'mood', 'Plugin work mood', array( $this, 'section_callback' ), 'netopia_recurring' );
        }

    
    public function recurring_setup_fields() {
        $fields = array(
            array(
                'uid' => $this->slug.'_api_key',
                'label' => __('Live API Key'),
                'section' => 'general',
                'type' => 'text',
                'options' => false,
                'placeholder' => __('~ 60 character - without space'),
                'helper' => __(''),
                'supplemental' => __('You have it from NETOPIA Payments platform'),
                'default' => ''
            ),
            array(
                'uid' => $this->slug.'_api_key_sandbox',
                'label' => __('Sandbox API Key'),
                'section' => 'general',
                'type' => 'text',
                'options' => false,
                'placeholder' => __('~ 60 character - without space'),
                'helper' => __(''),
                'supplemental' => __('You have it from NETOPIA Payments platform'),
                'default' => ''
            ),
            array(
                'uid' => $this->slug.'_signature',
                'label' => __('Signature ID'),
                'section' => 'general',
                'type' => 'text',
                'options' => false,
                'placeholder' => __('24 character - without space'),
                'helper' => __(''),
                'supplemental' => __('You have it from NETOPIA Payments platform'),
                'default' => ''
            ),
            array(
                'uid' => $this->slug.'_general_public_key',
                'label' => __('General public key'),
                'section' => 'certificate',
                'type' => 'file',
                'options' => false,
                'placeholder' => __(''),
                'helper' => __(''),
                'supplemental' => __('You have it from NETOPIA Payments platform'),
                'default' => ''
            ),
            array(
                'uid' => $this->slug.'_general_public_key_file_name',
                'label' => __(''),
                'section' => 'certificate',
                'type' => 'hidden',
                'options' => false,
                'placeholder' => __(''),
                'helper' => __(''),
                'supplemental' => __(''),
                'default' => ''
            ),
            array(
                'uid' => $this->slug.'_mood',
                'label' => '',
                'section' => 'mood',
                'type' => 'radio',
                'options' => array(
                    'live' => __('Live Mode. By setting it as Live, you define the plugin to work in the production environment.'),
                    'sandbox' => __('Sandbox Mode. Setting as sandbox allows you to test the plugin in TEST MODE, without actual payment.'),
                ),
                'helper' => '',
                'supplemental' => '',
                'default' => array('live')
            ),
            // array(
            //     'uid' => $this->slug.'_is_valid',
            //     'label' => __('Validate credential data'),
            //     'section' => 'general',
            //     'type' => 'verify_button',
            //     'options' => false,
            //     'placeholder' => "",
            //     'helper' => __(''),
            //     'supplemental' => "",
            //     'default' => 'false'
            // ),
        );
        foreach( $fields as $field ){
            add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'netopia_recurring', $field['section'], $field );
            register_setting( 'netopia_recurring', $field['uid'] );
        }
    }

    public function section_callback( $arguments )
    {
        switch( $arguments['id'] ){
            case 'general':
                echo __('The <b>Api key</b> is an authorization key to sign your request.','ntpRp');
                echo __('Keys are available in your NETOPIA account under Profile - Security.','ntpRp');
                echo __('<br>The <b>signature ID</b> is the merchant POS identifier, available in your NETOPIA account.','ntpRp');
                break;
            case 'mood':
                echo '';
                break;
            case 'certificate':
                echo 'Uploade the Certificate files';
                break;
            case 'message':
                echo __('To customize communication messages with your client during subscription and subscription, ...', 'ntpRp');
                break;
            case 'account':
                echo '';
                break;
            // case 'template':
            //     echo __('Custom notification emails; will be shared by NETOPIA', 'ntpRp');
            //     break;
            case 'urls':
                echo __('<b>Subscription notify url</b> is on the merchant side where NETOPIA will send subscription & payment result notifications', 'ntpRp');
                echo __('<br><b>Subscription login url</b> is page URL which your subscription can inform about their own activities.', 'ntpRp');
                break;
            default:
                echo '';
                break;
        }
    }

    public function field_callback( $arguments ) {
        $value = get_option( $arguments['uid'] ); // Get the current value, if there is one
        if( ! $value ) { // If no value exists
            $value = $arguments['default']; // Set to our default
        }

        // Check which type of field we want
        switch( $arguments['type'] ){
            case 'hidden': // If it is a hidden field
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'text': // If it is a text field
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'textarea': // If it is a textarea
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
                break;
            case 'select': // If it is a select dropdown
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value, $key, false ), $label );
                    }
                    printf( '<select name="%1$s" id="%1$s">%2$s</select>', $arguments['uid'], $options_markup );
                }
                break;
            case 'radio':
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        @$options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator, null, null );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
            case 'special_checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        @$options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], 'checkbox', $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator, null, null );
                    }
                    if( ! empty($arguments['items']) ){
                        foreach ($arguments['items'] as $item){
                            printf( '<li>%s</li>', $item );
                        }
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
            case 'link': // If it is a text field
                printf( '<span><b>%1$s%2$s%3$s</b></span>', 'https://',$_SERVER['HTTP_HOST'], '/' );
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], 'text', $arguments['placeholder'], $value );
                // printf('<button type="button" id="%2$s_verify" class="button button-primary">%1$s</button>', 'check', $arguments['uid']);
            break;
            case 'file': // If it is a File type for uploade files, ...
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
            break;
            // case 'verify_button':
            //     printf( '<input name="%1$s" id="%1$s" type="hidden" placeholder="%2$s" value="%3$s" />', $arguments['uid'], $arguments['placeholder'], $value );
            //     printf('<button type="button" id="%2$s_verify" class="button button-primary">%1$s</button>', 'check', $arguments['uid']);
            // break;
        }

        // If there is help text
        if( $helper = $arguments['helper'] ){
            printf( '<span class="helper"> %s</span>', $helper ); // Show it
        }

        // If there is supplemental text
        if( $supplimental = $arguments['supplemental'] ){
            printf( '<p id="description_%1$s" class="description">%2$s</p>', $arguments['uid'], $supplimental ); // Show it
        }
    }

    public function dashbord() {
        settings_errors();
        @$active_tab = $_GET[ 'tab' ] ? $_GET[ 'tab' ] : null;
        ?>
        <div class="wrap">
            <div class="row">
                <img src="<?php echo URL_NETOPIA_PAYMENTS_LOGO; ?>" width="150" style="padding: 20px 25px 0px 0px;">
                <span style="font-size: xx-large"><?=$this->page_title ?></span>
            </div>
            <h2 class="nav-tab-wrapper">
                <a href="?page=netopia_recurring&tab=display_about_plugin" class="nav-tab <?php echo $active_tab == 'display_about_plugin' ? 'nav-tab-active' : ''; ?>"><?php echo __('About plugin','ntpRp')?></a>
                <a href="?page=netopia_recurring&tab=display_setting" class="nav-tab <?php echo $active_tab == 'display_setting' ? 'nav-tab-active' : ''; ?>"><?php echo __('Settings','ntpRp')?></a>
                <a href="?page=netopia_recurring&tab=display_account_management" class="nav-tab <?php echo $active_tab == 'display_account_management' ? 'nav-tab-active' : ''; ?>"><?php echo __('Account page management','ntpRp')?></a>
                <a href="?page=netopia_recurring&tab=display_message_management" class="nav-tab <?php echo $active_tab == 'display_message_management' ? 'nav-tab-active' : ''; ?>"><?php echo __('Message management','ntpRp')?></a>
            </h2>
            <form method="post" enctype="multipart/form-data" action="options.php">
                <?php
                if( $active_tab == 'display_setting' ) {
                    settings_fields( 'netopia_recurring' );
                    do_settings_sections( 'netopia_recurring' );
                    submit_button();
                }elseif($active_tab == 'display_about_plugin') {
                    require_once ('include/about_plugin.php');
                }elseif($active_tab == 'display_send_all') {
                    settings_fields( 'netopia_recurring_sned2all' );
                    do_settings_sections( 'netopia_recurring_sned2all' );
                } elseif($active_tab == 'display_account_management') {
                    settings_fields( 'account_management' );
                    do_settings_sections( 'account_management' );
                    submit_button();
                } elseif($active_tab == 'display_message_management') {
                    settings_fields( 'message_management' );
                    do_settings_sections( 'message_management' );
                    submit_button();
                } else {
                    require_once ('include/welcomepage.php');
                }
                ?>
            </form>
        </div>
        <?php
    }

    public function subscription_UI_Ajax() {
        @$active_tab = $_GET[ 'tab' ] ? $_GET[ 'tab' ] : null;
        ?>
        <div class="wrap">
            <div class="row">
                <img src="<?php echo URL_NETOPIA_PAYMENTS_LOGO ?>" width="150" style="padding: 20px 25px 0px 0px;">
                <span style="font-size: xx-large"><?=$this->menuItems['subscriptionAjax']['pageTitle'] ?></span>
                <?php echo $this->getWarningAdmin();?>
            </div>
            <h2 class="nav-tab-wrapper">
                <a href="?page=recurring_subscription_ajax&tab=subscription_list" class="nav-tab <?php echo $active_tab == 'subscription_list' ? 'nav-tab-active' : ''; ?>"><?php echo __('Subscription list','ntpRp')?></a>
                <a href="?page=recurring_subscription_ajax&tab=next_scheduled_payments" class="nav-tab <?php echo $active_tab == 'next_scheduled_payments' ? 'nav-tab-active' : ''; ?>"><?php echo __('Scheduled Payments','ntpRp')?></a>
            </h2>
            
            <?php 
            if($active_tab == 'next_scheduled_payments') {
                include_once('include/nextScheduledPayments.php');
            }else {
                include_once('include/subscriptionsInfinite.php');
                include_once('include/partial/modalSubscriberInfo.php');
                include_once('include/partial/modalNextPayment.php');
                include_once('include/partial/modalUnsubscribeAdmin.php');
                include_once('include/partial/modalSubscriberHistory.php');
            }
            
            ?>
        <div>    
        <?php
    }

    public function plan_UI() {
        @$active_tab = $_GET[ 'tab' ] ? $_GET[ 'tab' ] : null;
        ?>
        <div class="wrap">
            <div class="row">
                <img src="<?php echo URL_NETOPIA_PAYMENTS_LOGO ?>" width="150" style="padding: 20px 25px 0px 0px;">
                <span style="font-size: xx-large"><?=$this->menuItems['plan']['pageTitle'] ?></span>
            </div>
            <h2 class="nav-tab-wrapper">
                <a href="?page=recurring_plan&tab=plan_list" class="nav-tab <?php echo $active_tab == 'plan_list' ? 'nav-tab-active' : ''; ?>"><?php echo __('Plan list','ntpRp')?></a>
                <a href="?page=recurring_plan&tab=add_plan" class="nav-tab <?php echo $active_tab == 'add_plan' ? 'nav-tab-active' : ''; ?>"><?php echo __('Add / Edit Plan','ntpRp')?></a>
            </h2>
            <?php 
            if($active_tab == 'add_plan') {
                include_once('include/planAddEditForm.php');
            }else {
                include_once('include/partial/modalClipboard.php');
                include_once('include/partial/modalDeletePlan.php');
                include_once('include/partial/modalEditPlan.php');
                include_once('include/plans.php');
            }
            ?>
        <div>
        <?php
    }

    public function report_UI() {
        ?>
        <div class="wrap">
            <div class="row">
                <img src="<?php echo URL_NETOPIA_PAYMENTS_LOGO ?>" width="150" style="padding: 20px 25px 0px 0px;">
                <span style="font-size: xx-large"><?=$this->menuItems['report']['pageTitle'] ?></span>
                <?php echo $this->getWarningAdmin();?>
            </div>
            <h2 class="nav-tab-wrapper">
                <a href="#" class="nav-tab nav-tab-active"><?php echo __('Payment history','ntpRp')?></a>
            </h2>
            
            <?php include_once('include/reports.php');?>
        <div> 
        <?php
    }

    function getWarningAdmin() {
        $obj = new recurringAdmin();
        if($obj->isLive()) {
            return '';
        } else {
            return '
                <div class="px-5">
                    <div class="alert alert alert-info alert-dismissible fade show" role="alert">
                        <strong>'.__('Warning!!!','ntpRp').'</strong> '.__('You are in test mod', 'ntpRp').'
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                ';
        }
    }
}