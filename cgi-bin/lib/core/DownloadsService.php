<?php
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  include_once(DIR_ADMIN . "/DownloadsController.php");
  include_once(DIR_LIB . "/core/DownloadItem.php");
  include_once(DIR_LIB . "/core/Logger.php");
  
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DownloadsService
 *
 * @author Kieran
 */
class DownloadsService {
    private static $category = "downloads";
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
        self::$controller = new DownloadsController();
    }
    
    public static function get_items() {
        return self::$controller->get_items(self::$category);
    }
    
    public static function get_viewable_items() {
        return self::$controller->get_viewable_items(self::$category);
    }
    
    public static function get_modified_time() {
        return self::$controller->get_modified_time();
    }
    
    public static function is_modified($token) {
        return self::$controller->is_modified($token);
    }
    
    public static function is_valid_file($file) {
        return self::$controller->is_valid_file($file);
    }
    
    public static function format_files_for_download_page() {
        $items = self::get_viewable_items();
        
        $now = self::get_modified_time();
        $html = "";
        $html .= "<input type='hidden' id='downloads-token' name='downloads-token' value='{$now}'/>";
        $html .= "<table border='0' cellpadding='0' cellspacing='0'>";
        $html .= "<tr>";
        $html .= "  <th class='th_left'>Available Files</th>";
        $html .= "  <th>Last Modified</th>";
        $html .= "  <th>Size</th>";
        $html .= "  <th>&nbsp;</th>";
        $html .= "</tr>";
        $even = true;
        foreach ($items as $item) {
            $row_style = $even ? "even" : "odd";
            $html .= "<tr class='{$row_style}'>";
            $html .= "  <td class='td_left'>";
            $html .= "      <span>{$item->getFile()}</span>";
            $html .= "  </td>";
            
            $html .= "  <td>";
            $html .= "      <span>{$item->getLastmodified()}</span>";
            $html .= "  </td>";
            
            $html .= "  <td>";
            $html .= "      <span>{$item->getSize()}</span>";
            $html .= "  </td>";
            
            $html .= "  <td>";
            $file = urlencode($item->getFile());
            $html .= "      <span><a href='download.php?file={$file}'>download</a></span>";
            $html .= "  </td>";
            $html .= "</tr>";
            $even = !$even;
        }
        $html .= "</table>";
        
        return $html;
    }
    
} DownloadsService::static_initialiser();
?>
