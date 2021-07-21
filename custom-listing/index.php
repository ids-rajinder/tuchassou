<?php
/*
Plugin Name: Listing Form
Plugin URI: http://incredible-developers.com/
Description: Customized Plugin
Version: 1.0
Author: IDS
Author URI: http://incredible-developers.com/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
define( 'LIST_PATH', plugin_dir_path( __FILE__ ) );
define( 'LIST_URL', plugin_dir_url( __FILE__ ) );

function getCurrentBrowser(){
  $userAgent      =  $_SERVER['HTTP_USER_AGENT']; 
  return $browser = strpos($userAgent, 'Chrome') !== FALSE ?  true : false;
}


function GetIP(){
  foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key)
  {
      if (array_key_exists($key, $_SERVER) === true)
      {
          foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
          {
              if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false)
              {
                  return $ip;
              }
          }
      }
  }
}






require_once LIST_PATH.'classes/manage-list-form.php';
//require_once LIST_PATH.'classes/gf-post-creatipon.php';


# Tried including the below file inside plugins_loaded
# It didn't worked out. I think homey-core plugin is not using plugin_loaded hook
# Hence directly including the file
require_once LIST_PATH.'classes/function-override-homey.php';

add_action( 'plugins_loaded', function(){
  //add_filter("gform_init_scripts_footer", '__return_true'); 
});


