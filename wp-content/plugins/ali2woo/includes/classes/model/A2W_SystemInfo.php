<?php

/**
 * Description of A2W_SystemInfo
 *
 * @author Andrey
 * 
 * @autoload: a2w_init
 */

if (!class_exists('A2W_SystemInfo')) {
    class A2W_SystemInfo {

        public function __construct() {
            add_action('wp_ajax_a2w_ping', array($this, 'ajax_ping'));
            add_action('wp_ajax_nopriv_a2w_ping', array($this, 'ajax_ping'));
        }

        public function ajax_ping() {
            echo json_encode(array('state'=>'ok'));
            wp_die();
        }

        public static function ping(){
            $result = array();
            $request = wp_remote_post( admin_url('admin-ajax.php')."?action=a2w_ping");
            if (is_wp_error($request)) {
                $result = A2W_ResultBuilder::buildError($request->get_error_message());    
            } else if (intval($request['response']['code']) != 200) {
                $result = A2W_ResultBuilder::buildError($request['response']['code'] . " " . $request['response']['message']);
            } else {
                $result = json_decode($request['body'], true);
            }
            return $result;
        }
       
        
        public static function server_ping(){
            $result = array();
            $ping_url = A2W_RequestHelper::build_request('ping', array('r' => mt_rand()));
            $request = a2w_remote_get($ping_url);
            if (is_wp_error($request)) {
                if(file_get_contents($ping_url)){
                    $result = A2W_ResultBuilder::buildError('a2w_remote_get error');
                }else{
                    $result = A2W_ResultBuilder::buildError($request->get_error_message());    
                }
            } else if (intval($request['response']['code']) != 200) {
                $result = A2W_ResultBuilder::buildError($request['response']['code']." ".$request['response']['message']);
            } else {
                $result = json_decode($request['body'], true);
            }

            return $result;
        }
        
        public static function php_check(){
            return A2W_ResultBuilder::buildOk();
        }

        public static function php_dom_check(){
            if (class_exists('DOMDocument')) {
                return A2W_ResultBuilder::buildOk();
            } else{
                return A2W_ResultBuilder::buildError('PHP DOM is disabled');
            }
        }
    }

}

