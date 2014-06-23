<?php
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  include_once(DIR_ADMIN . "/LinksController.php");
  include_once(DIR_LIB . "/core/LinkItem.php");
  include_once(DIR_LIB . "/core/Logger.php");
  
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LinksService
 *
 * @author Kieran
 */
class LinksService {
    private static $category = "links";
    private static $controller = null;
    
    // The clone and wakeup methods prevents external instantiation of copies of the Singleton class,
    // thus eliminating the possibility of duplicate objects.
    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    public function __wakeup() {
        trigger_error('Deserializing is not allowed.', E_USER_ERROR);
    }
    
    static function static_initialiser() {
        self::$controller = new LinksController();
    }

    // private constructor
    private function __construct() {
    }
    
    public static function get_items() {
        return self::$controller->get_items(self::$category);
    }
    
    public static function get_modified_time() {
        return self::$controller->get_modified_time();
    }
    
    public static function is_modified($token) {
        return self::$controller->is_modified($token);
    }
    
    public static function format_links_for_links_page() {
        $items = self::get_items();
        
        $now = self::get_modified_time();
        $html = "";
        $html .= "<input type='hidden' id='links-token' name='links-token' value='{$now}'/>";
        $html .= "<table border='0' cellpadding='0' cellspacing='0'>";
        $even = true;
        foreach ($items as $item) {
            $row_style = $even ? "even" : "odd";
            $html .= "<tr class='{$row_style}'>";
            $html .= "  <td>";
            $html .= "      <a href='{$item->getHref()}' target='{$item->getTarget()}'>{$item->getCaption()}</a>";
            $html .= "  </td>";
            $html .= "</tr>";
            $even = !$even;
        }
        $html .= "</table>";
        
        return $html;
    }
    
} LinksService::static_initialiser();

?>
