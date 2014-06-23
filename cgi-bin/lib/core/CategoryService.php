<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/CategoryController.php");
    include_once(DIR_LIB . "/core/CategoryItem.php");
    include_once(DIR_LIB . "/core/Logger.php");

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CategoryService
 *
 * @author Kieran
 */
class CategoryService {
    private static $controller = null;
    
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
    
    static function static_initialiser() {
        self::$controller = new CategoryController();
    }
    
    public static function get_items($category) {
        return self::$controller->get_items($category);
    }
    
    public static function get_viewable_items($category) {
        return self::$controller->get_viewable_items($category);
    }
    
    public static function get_item($category) {
        return self::$controller->get_item($category);
    }
    
} CategoryService::static_initialiser();
?>