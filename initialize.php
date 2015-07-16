<?php

require_once(dirname(__FILE__).'/lib/Jazz.class.php');

if(!function_exists('handle_resumator_shortcodes')) {
    function handle_resumator_shortcodes($atts=null, $content, $tag) {
        if($atts)
            extract( $atts );
        $tag_type = (isset($type)) ? $type : '';
        switch($tag_type) {
            case 'job':
                # Show one job by ID
                break;
            case 'applicant':
                # create, edit (retrieve) applicant info
                break;
            case 'jobs':
            default:
                # Show a list of jobs

        }
    }
}


if(!function_exists('resumator_autoutput_handler')) {
    function resumator_autoutput_handler($request) {
        $resumator = new Jazz();
        $resumator->autOutput($request);
    }
}

if(is_admin()) {

  function resumator_admin_menu() {
    Jazz::addMenuItem();
  }

  function resumator_admin_form() {
    $resumator = new Jazz();
    $resumator->adminForm();
  }

  add_action('admin_menu', 'resumator_admin_menu');
} else {
    $resumator_api_key = get_option('resumator_api_key');
    $resumator_url_path = get_option('resumator_url_path');
    add_shortcode('resumator', 'handle_resumator_shortcodes');
    add_action('parse_request', 'resumator_autoutput_handler');
}
