<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/SecurityService.php");

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ControllerService
 *
 * @author Kieran
 */
class ControllerService {
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
    
    public static function render_response($output) {
        if (isset($_SESSION['ERROR_MSG']) && count($_SESSION['ERROR_MSG']) > 0) {
            header("HTTP/1.0 500 Server Error");
            if (SecurityService::is_admin_logged_in()) {
                echo(implode("\n", $_SESSION['ERROR_MSG']));
            } else {
                echo("An error occurred.");
            }
        } else {
            if (strlen($output) > 0) {
                echo($output);
            }
        }
    }
    
    public static function redirect($url_path) {
        //if (isset($_SESSION['ERROR_MSG']) && count($_SESSION['ERROR_MSG']) > 0) {
        //    Utils::redirect(GENERAL_ERROR_PAGE);
        //} else {
            Utils::redirect($url_path);
        //}
    }
    
    public static function get_status_context() {
        $status_context = array(
            "MESSAGES"=>"",
            "STATUS"=>""
        );
        
        if (isset($_SESSION["status_context"])) {
            $status_context = unserialize($_SESSION["status_context"]);
            unset($_SESSION["status_context"]);
        }
        
        $errors = self::get_error_messages();
        if (strlen($errors) > 0) {
            $status_msgs = $status_context["MESSAGES"];
            if (strlen($status_msgs) > 0) {
                $status_context["MESSAGES"] = $status_msgs . "<br/>" . $errors;
            } else {
                $status_context["MESSAGES"] = $errors;
            }
            
            $status_context["STATUS"] = "errors";
        }
        
        return $status_context;
    }
    
    public static function set_status_context($messages, $status) {
        $status_context = array(
            "MESSAGES"=>"",
            "STATUS"=>""
        );
        
        $errors = ControllerService::get_error_messages();
        if (strlen($errors) > 0) {
            $status_context["MESSAGES"] = $errors;
            $status_context["STATUS"] = "errors";
        } else {
            $status_context["MESSAGES"] = $messages;
            $status_context["STATUS"] = $status;
        }
    
        $_SESSION["status_context"] = serialize($status_context);
    }
    
    public static function get_error_messages() {
        if (isset($_SESSION['ERROR_MSG']) && count($_SESSION['ERROR_MSG']) > 0) {
            if (SecurityService::is_admin_logged_in()) {
                return implode("\n", $_SESSION['ERROR_MSG']);
            } else {
                return "An error occurred.";
            }
        } else {
            return "";
        }
    }
}

?>
