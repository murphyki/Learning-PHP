<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/SecurityService.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logger
 *
 * @author Kieran
 */
class Logger {
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
        if (!session_id()) {
            session_start();
        }
        
        $_SESSION['ERROR_MSG'] = array();
        
        date_default_timezone_set("Europe/Dublin");
        
        // Set up our error reporting
        if (!Config::is_localhost()) {
            error_reporting(E_ALL | E_STRICT);
            ini_set('display_errors', '0');
            ini_set('log_errors', '0');
            set_error_handler("Logger::error_handler");
            set_exception_handler("Logger::exception_handler");
        }
    }
    
    public static function error_handler($errno, $errmsg, $filename, $linenum, $vars) {
        // timestamp for the error entry
        $dt = date("Y-m-d H:i:s");

        // define an assoc array of error string
        // in reality the only entries we should
        // consider are E_WARNING, E_NOTICE, E_USER_ERROR,
        // E_USER_WARNING and E_USER_NOTICE
        $errortype = array (
                    E_ERROR              => 'Error',
                    E_WARNING            => 'Warning',
                    E_PARSE              => 'Parsing Error',
                    E_NOTICE             => 'Notice',
                    E_CORE_ERROR         => 'Core Error',
                    E_CORE_WARNING       => 'Core Warning',
                    E_COMPILE_ERROR      => 'Compile Error',
                    E_COMPILE_WARNING    => 'Compile Warning',
                    E_USER_ERROR         => 'User Error',
                    E_USER_WARNING       => 'User Warning',
                    E_USER_NOTICE        => 'User Notice',
                    E_STRICT             => 'Runtime Notice',
                    E_RECOVERABLE_ERROR  => 'Catchable Fatal Error'
                    );
        // set of errors for which a var trace will be saved
        $user_errors = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

        $err = "<errorentry>\n";
        $err .= "\t<datetime>" . $dt . "</datetime>\n";
        $err .= "\t<errornum>" . $errno . "</errornum>\n";
        $err .= "\t<errortype>" . $errortype[$errno] . "</errortype>\n";
        $err .= "\t<errormsg>" . $errmsg . "</errormsg>\n";
        $err .= "\t<scriptname>" . $filename . "</scriptname>\n";
        $err .= "\t<scriptlinenum>" . $linenum . "</scriptlinenum>\n";

        if (in_array($errno, $user_errors) && function_exists("wddx_serialize_value")) {
            $err .= "\t<vartrace>" . wddx_serialize_value($vars, "Variables") . "</vartrace>\n";
        }
        $err .= "</errorentry>\n\n";

        // save to the error log
        self::log_error($err, $filename, $linenum);
        
        // Only redirect if this is not an ajax request
        $requestedWith = Utils::get_user_input("requestedWith");
        if (strcasecmp($requestedWith, "xmlhttprequest") != 0) {
            Utils::redirect(GENERAL_ERROR_PAGE);
        }
    }
    
    public static function exception_handler($exception) {
        $err = $exception->getMessage();
        self::log_error($err, $exception->getFile(), $exception->getLine());
        
        $requestedWith = Utils::get_user_input("requestedWith");
        if (strcasecmp($requestedWith, "xmlhttprequest") != 0) {
            Utils::redirect(GENERAL_ERROR_PAGE);
        }
    }
    
    public static function log_error($err, $filename, $linenum) {
        if (session_id()) {
            $_SESSION['ERROR_MSG'][] = $err;
        }
        
        if (file_exists(FILE_ERROR_LOG)) {
            $out  = date("Y-m-d H:i:s");
            $out .= "[" . SecurityService::get_loggedin_user_display_name() . "]";
            $out .= ": ";
            $out .= $filename;
            $out .= ": ";
            $out .= $linenum;
            $out .= "\n";
            $out .= $err;
            $out .= "\n\n";
            file_put_contents(FILE_ERROR_LOG, $out, FILE_APPEND);
            if (!Config::is_localhost()) {
                mail(WEBMASTER, "admin error", $out);
            }
        }
    }
    
    public static function log_info($msg, $filename, $linenum) {
        $out  = date("Y-m-d H:i:s");
        $out .= "[" . SecurityService::get_loggedin_user_display_name() . "]";
        $out .= ": ";
        $out .= $filename;
        $out .= ": ";
        $out .= $linenum;
        $out .= "\n";
        $out .= $msg;
        $out .= "\n\n";
        file_put_contents(FILE_INFO_LOG, $out, FILE_APPEND);
    }
}

?>
