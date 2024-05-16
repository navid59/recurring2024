<?php
/**
 * @package NetopiaRecurringPaymentPlugin
 */

/*
Plugin Name: NETOPIA Payments - Recurring payment
Plugin URI: https://www.netopia-system.com.ro/
Description: An subscription managment, inclouding periodic payment.
Author: NETOPIA Payments
Version: 0.0.2
License: GPLv2 or later
Text Domain : ntpRp
Domain Path: /languages/
*/

defined( 'ABSPATH' ) or die('Access denied');


/** To loade the language */
load_plugin_textdomain( 'ntpRp', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages/' );

/** To make setting link*/
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'apd_settings_link' );
function apd_settings_link( array $links ) {
    $url = get_admin_url() . "admin.php?page=netopia_recurring&tab=display_setting";
    $settings_link = '<a href="' . $url . '">' . __('Settings', 'ntpRp') . '</a>';
      $links[] = $settings_link;
    return $links;
  }

include_once( 'config/static.php' );
include_once( 'packages/recurring.php' );
include_once( 'packages/recurring-admin.php' );
include_once( 'packages/recurring-front.php' );
include_once( 'packages/recurring-key.php' );
include_once( 'wc-netopiapayments-recurring-install.php' );
include_once( 'wc-netopiapayments-recurring.php' );
include_once( 'wc-netopiapayments-recurring-front.php' );
include_once( 'wc-netopiapayments-recurring-notify.php' );


$recurringPlugin = new NetopiapaymentsRecurringPayment();
register_activation_hook( __FILE__, 'recurring_install' );
register_activation_hook(__FILE__, 'recurring_account_page');

/** To make setting link*/
if (!function_exists('write_log')) {
  function write_log ( $log )  {
      if ( true === WP_DEBUG ) {
          if ( is_array( $log ) || is_object( $log ) ) {
              error_log( print_r( $log, true ) );
          } else {
              error_log( $log );
          }
      }
  }
}