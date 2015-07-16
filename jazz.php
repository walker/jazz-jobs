<?php
/*
Plugin Name: Jazz Jobs
Plugin URI: http://github.com/walker/jazz-jobs
Description: Jazz
Version: 0.0.2
Author: Walker Hamilton
Author URI: http://walkerhamilton.com/
*/

// Settings
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

// Define variables
if ( ! defined( 'JAZZ_VERSION' ) )
    define( 'JAZZ_VERSION', '0.0.2' );

if ( ! defined( 'JAZZ_API_VERSION' ) )
    define( 'JAZZ_API_VERSION', 'v1' );

if ( ! defined( 'JAZZ_API_BASE_URL' ) )
    define( 'JAZZ_API_BASE_URL', 'https://api.resumatorapi.com/' );

if ( ! defined( 'JAZZ_API_URL' ) )
    define( 'JAZZ_API_URL', JAZZ_API_BASE_URL.JAZZ_API_VERSION.'/' );

if ( ! defined( 'JAZZ_PLUGIN_BASENAME' ) )
    define( 'JAZZ_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'JAZZ_PLUGIN_NAME' ) )
    define( 'JAZZ_PLUGIN_NAME', trim( dirname( JAZZ_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'JAZZ_PLUGIN_DIR' ) )
    define( 'JAZZ_PLUGIN_DIR', WP_PLUGIN_DIR . DS . JAZZ_PLUGIN_NAME );

if ( ! defined( 'JAZZ_PLUGIN_URL' ) )
    define( 'JAZZ_PLUGIN_URL', WP_PLUGIN_URL . DS . JAZZ_PLUGIN_NAME );

// Bootstrap this plugin
require_once JAZZ_PLUGIN_DIR . DS . 'initialize.php';
