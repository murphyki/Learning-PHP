<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Utils
 *
 * @author murphyk
 */
class Utils {

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

    public static function get_user_input($input, $allowable_tags = null, $default_value = "") {
        $value = $default_value;
        if (isset($_REQUEST[$input])) {
            $value = $_REQUEST[$input];
        } else {
            $input = str_replace("'", "\'", $input);
            if (isset($_REQUEST[$input])) {
                $value = $_REQUEST[$input];
            }
        }
        
        return self::sanitize_value($value);
    }
    
    public static function get_user_input_array($input) {
        $values = array();
        if (isset($_REQUEST[$input])) {
            $values = $_REQUEST[$input];
        } else {
            $input = str_replace("'", "\'", $input);
            if (isset($_REQUEST[$input])) {
                $values = $_REQUEST[$input];
            }
        }
        
        $cleaned = array();
        if ($values !== null && count($values) > 0) {
            foreach($values as $value) {
                if (is_array($value)) {
                    $cleaned[] = self::sanitize_array($value);
                } else {
                    $cleaned[] = self::sanitize_value($value);
                }
            }
        }
        
        return $cleaned;
    }
    
    public static function encript_string($str, $salt = null) {
        return crypt($str, $salt);
    }
    
    public static function encrypt_string($str, $key) {
        $block = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);
        return mcrypt_encrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
    }

    public static function decrypt_string($str, $key) {   
        $str = mcrypt_decrypt(MCRYPT_DES, $key, $str, MCRYPT_MODE_ECB);
        $block = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_ECB);
        $pad = ord($str[($len = strlen($str)) - 1]);
        return substr($str, 0, strlen($str) - $pad);
    }
    
    public static function sanitize_array($arr) {
        $cleaned = array();
        
        foreach($arr as $value) {
            $cleaned[] = self::sanitize_value($value);
        }
        
        return $cleaned;
    }
    
    public static function sanitize_value($value) {
        $v = $value;
        if ($v !== null && strlen($v) > 0) {
            $v = rawurldecode($v);
            $v = stripslashes($v);
            $replace = "";
            $v = preg_replace('#('.preg_quote("<script>").')(.*)('.preg_quote("</script>").')#si', '$1'." $replace".'$3', $v);
            $v = trim($v);
            //$value = strip_tags($value, $allowable_tags);
            $v = htmlspecialchars($v, ENT_QUOTES);
        }
        
        return $v;
    }
    
    public static function sanitize_name($name) {
        $text = str_replace(" ", "_", $name);
        return preg_replace("/[^a-zA-Z0-9\s]/", "_", $text);
    }
    
    public static function make_dir($dir, $mode = 0755, $recursive = true) {
        if (!file_exists($dir)) {
            mkdir($dir, $mode, $recursive);
        }
        
        return $dir;
    }
    
    public static function replace_tag($tag, $value, $source, $start_delim = "<!--", $end_delim = "-->") {
        $start = $start_delim . " __" . $tag . "_START__ " . $end_delim;
        $end = $start_delim . " __" . $tag . "_END__ " . $end_delim;
        return preg_replace('#('.preg_quote($start).')(.*)('.preg_quote($end).')#si', '$1'." $value".'$3', $source);
    }
    
    public static function redirect($url_path) {
        $url = "http://" . APP_DOMAIN . $url_path;
        header("Status: 200");
        header("Location: {$url}");
        exit();
    }
    
    public static function encode_url($url) {
        // raw_urlencode will encode '/', ':', '=' characters
        // For data on a url convert back the '='.
        // for the main url convert back '/' and ':'
        $tokens = preg_split("/\?/", $url);
        $encoded_url = rawurlencode($tokens[0]);
        $encoded_url = preg_replace("/%2F/", "/", $encoded_url);
        $encoded_url = preg_replace("/%3A/", ":", $encoded_url);
        $encoded_url = preg_replace("/%3D/", "=", $encoded_url);
        
        if (count($tokens) > 1) {
            $encoded_url .= "?" . rawurlencode($tokens[1]);
            $encoded_url = preg_replace("/%3D/", "=", $encoded_url);
        }
        
        return $encoded_url;
    }
    
    public static function starts_with($haystack, $needle) {
        return !strncmp($haystack, $needle, strlen($needle));
    }
    
    public static function ends_with($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
?>
