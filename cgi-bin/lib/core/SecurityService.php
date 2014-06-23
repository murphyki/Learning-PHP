<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/authentication/AuthenticationService.php");
    
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SecurityService
 *
 * @author murphyk
 */
class SecurityService {
    
  private static $auth_service = null;
  
    // The clone and wakeup methods prevents external instantiation of copies of the Singleton class,
    // thus eliminating the possibility of duplicate objects.
    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    public function __wakeup() {
        trigger_error('Deserializing is not allowed.', E_USER_ERROR);
    }

    // private constructor
    private function __construct() {
    }
    
    public static function init() {
        self::$auth_service = new AuthenticationService();
    }
    
    public static function is_logged_in() {
        return true; //self::$auth_service->is_logged_in();
    }
    
    public static function is_admin_logged_in() {
        return true; //self::$auth_service->is_admin_logged_in();
    }
    
    public static function is_member() {
        return self::$auth_service->is_member();
    }
    
    public static function is_member_logged_in() {
        return self::$auth_service->is_member_logged_in();
    }
    
    public static function add_member() {
        return self::$auth_service->add_member();
    }
    
    public static function remove_member() {
        return self::$auth_service->remove_member();
    }

    public static function show_login_page() {
        return self::$auth_service->show_login_page();
    }

    public static function login($provider) {
        return self::$auth_service->login($provider);
    }

    public static function logout($refer = true) {
        return self::$auth_service->logout($refer);
    }

    public static function get_loggedin_user_name() {
        return self::$auth_service->get_loggedin_user();
    }
    
    public static function get_loggedin_user_display_name() {
        return self::$auth_service->get_loggedin_user_display_name();
    }
    
    public static function validate() {
        if (!self::is_admin_logged_in()) {
            echo("Authorization failure...");
            exit();
        }
    }
    
} SecurityService::init();
?>
