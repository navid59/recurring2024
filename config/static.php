<?php 
const BASE_URL_RECURRING_API_LIVE       = "https://recurring-api-fqvtst6pfa-ew.a.run.app/api/v1/";
const BASE_URL_RECURRING_API_SANDBOX    = "https://recurring-api-fqvtst6pfa-ew.a.run.app/api/v1/";

// const BASE_URL_RECURRING_API_LIVE       = "http://localhost:8080/api/v1/"; // We keep it Temporary for Debugging 
// const BASE_URL_RECURRING_API_SANDBOX    = "http://localhost:8080/api/v1/"; // We keep it Temporary for Debugging 

const URL_NETOPIA_PAYMENTS              = "https://netopia-payments.com/";
const URL_NETOPIA_PAYMENTS_REGISTRATION = "https://netopia-payments.com/register/";


$pluginPath = plugins_url('/', __DIR__);
define('URL_NETOPIA_PAYMENTS_LOGO', $pluginPath . 'img/NETOPIA_Payments.svg');
define('URL_NETOPIA_PAYMENTS_LOGO_GLOBAL', $pluginPath . 'img/NETOPIA_Payments.svg');
define('PATH_VISA_MASTER_LOGO', $pluginPath . 'img/NETOPIA_Payments.svg');
?>