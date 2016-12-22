<?php
/*
<<<<<<< a07f07b8569c230cb22df4021fc438c7019e7041
Plugin Name: Acelle Mail
Plugin URI: http://demo.acellemail.com
Description: Acelle Mail for WorPress
Author: Luan Pham
Version: 1.0
=======
Plugin Name: Acelle
>>>>>>> wordpress - acelle menu linked
*/

// main Acelle app path
$parts = explode('/', plugin_dir_path( __FILE__ ));
$plugin_name = $parts[count($parts) - 2];
define("APP_PATH", plugins_url() . "/" .$plugin_name. "/public/");

add_action( 'admin_menu', 'acelle_admin_menu' );

// define acelle main menu action
function acelle_admin_menu() {
    // add top Acelle menu
	add_menu_page( 'Acelle Mail',
        'Acelle Mail',
        'manage_options',
        'acelle.php',
        'acelle_dashboard_menu',
        APP_PATH . 'images/wordpress-20x20.png',
        25
    );
    
    // add dashboard menu
    add_submenu_page( 'acelle.php',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'acelle.php',
        'acelle_dashboard_menu'
    );
    
    // add campaigns menu
    add_submenu_page( 'acelle.php',
        'Campaigns',
        'Campaigns',
        'manage_options',
        'acelle_campaigns.php',
        'acelle_campaigns_menu'
    );
    
    // add lists menu
    add_submenu_page( 'acelle.php',
        'Lists',
        'Lists',
        'manage_options',
        'acelle_lists.php',
        'acelle_lists_menu'
    );
	
	// add templates menu
    add_submenu_page( 'acelle.php',
        'Templates',
        'Templates',
        'manage_options',
        'acelle_templates.php',
        'acelle_templates_menu'
    );
	
	// add sending servers menu
    add_submenu_page( 'acelle.php',
        'Sending servers',
        'Sending servers',
        'manage_options',
        'acelle_sending_servers.php',
        'acelle_sending_servers_menu'
    );
	
	// add bounce handlers menu
    add_submenu_page( 'acelle.php',
        'Bounce handlers',
        'Bounce handlers',
        'manage_options',
        'acelle_bounce_handlers.php',
        'acelle_bounce_handlers_menu'
    );
	
	// add sending domains menu
    add_submenu_page( 'acelle.php',
        'Sending domains',
        'Sending domains',
        'manage_options',
        'acelle_sending_domains.php',
        'acelle_sending_domains_menu'
    );
	
	// add settings menu
    add_submenu_page( 'acelle.php',
        'Settings',
        'Settings',
        'manage_options',
        'acelle_settings.php',
        'acelle_settings_menu'
    );
	
	// add layouts menu
    add_submenu_page( 'acelle.php',
        'Layouts',
        'Layouts',
        'manage_options',
        'acelle_layouts.php',
        'acelle_layouts_menu'
    );
	
	// add logs menu
    add_submenu_page( 'acelle.php',
        'Tracking logs',
        'Tracking logs',
        'manage_options',
        'acelle_logs.php',
        'acelle_logs_menu'
    );
	
	// add api menu
    add_submenu_page( 'acelle.php',
        'API',
        'API',
        'manage_options',
        'acelle_api.php',
        'acelle_api_menu'
    );	
}

// redirect to the spevific url
function acelle_redirect($url){
    if (headers_sent()){
      die('<script type="text/javascript">window.location.href="' . $url . '";</script>');
    }else{
      header('Location: ' . $url);
      die();
    }    
}

// Acelle dashboard menu click
function acelle_dashboard_menu(){
	acelle_redirect(APP_PATH); /* Redirect browser */
}

// Acelle campaigns menu click
function acelle_campaigns_menu(){
	acelle_redirect(APP_PATH . "campaigns"); /* Redirect browser */
}

// Acelle lists menu click
function acelle_lists_menu(){
	acelle_redirect(APP_PATH . "lists"); /* Redirect browser */
}

// Acelle templates menu click
function acelle_templates_menu(){
	acelle_redirect(APP_PATH . "admin/templates"); /* Redirect browser */
}

// Acelle sending servers menu click
function acelle_sending_servers_menu(){
	acelle_redirect(APP_PATH . "admin/sending_servers"); /* Redirect browser */
}

// Acelle bounce handlers click
function acelle_bounce_handlers_menu(){
	acelle_redirect(APP_PATH . "admin/bounce_handlers"); /* Redirect browser */
}

// Acelle sending domains click
function acelle_sending_domains_menu(){
	acelle_redirect(APP_PATH . "admin/sending_domains"); /* Redirect browser */
}

// Acelle settings click
function acelle_settings_menu(){
	acelle_redirect(APP_PATH . "admin/settings/sending"); /* Redirect browser */
}

// Acelle layouts click
function acelle_layouts_menu(){
	acelle_redirect(APP_PATH . "admin/layouts"); /* Redirect browser */
}

// Acelle logs click
function acelle_logs_menu(){
	acelle_redirect(APP_PATH . "admin/tracking_log"); /* Redirect browser */
}

// Acelle logs click
function acelle_api_menu(){
	acelle_redirect(APP_PATH . "account/api"); /* Redirect browser */
}

// Activate hook
function acelle_activate()
{
	// check acelle app is not installed
    // unlink(ABSPATH . '/wp-content/plugins/acelle/storage/installed');
}
register_activation_hook( __FILE__, 'acelle_activate' );

