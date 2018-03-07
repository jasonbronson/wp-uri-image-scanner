<?php
/**
 * @package uri-image-scanner
 * 
 */
/**
Plugin Name: URI Image Scanner
Plugin URI: https://github.com/jasonbronson/uri-image-scanner
Description: Loads images from a named directory
Version: 1.0.0
Author: Jason Bronson 
Author URI: https://github.com/jasonbronson
License: GPLv2 or later
Text Domain: uri-image-scanner
 */

/*
                    GNU GENERAL PUBLIC LICENSE
                       Version 2, June 1991

 Copyright (C) 1989, 1991 Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 Everyone is permitted to copy and distribute verbatim copies
 of this license document, but changing it is not allowed.
*/
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

spl_autoload_register( 'aot_autoloader' );
function aot_autoloader( $class_name ) {
  
    $classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'Libraries' . DIRECTORY_SEPARATOR;
    if(strpos($class_name, "\\")){
      $class_file = substr($class_name, strpos($class_name, "\\")+1 )  . '.php';
    }else{
      $class_file = $class_name . '.php';
    }
    
    if(file_exists($classes_dir . $class_file)){
      require_once $classes_dir . $class_file;
    }
    
}

class UriImageScanner {
  
  
  public function __construct() {
    //add_action( 'wp_head', array( $this, 'init' ), 10 );
  }

  public static function getPictures(){

    $url = parse_url($_SERVER['REQUEST_URI']);
    $slug = $url['path'];
    $uploadDir = wp_upload_dir();
    $d = new DirectoryScanFiles();
    return $d->ScanForFiles( $uploadDir['basedir']."$slug", $uploadDir['baseurl'].$slug );

  }
  
}

new UriImageScanner();