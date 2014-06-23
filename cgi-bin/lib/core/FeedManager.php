<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FeedManager
 *
 * @author Kieran
 */
class FeedManager {
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

    public static function get_feeds($category) {
        $feeds = array();
        $dir = DIR_CONTENT . "/{$category}/";
        if (file_exists($dir)) {
            $dh  = opendir($dir);
            while (false !== ($feed = readdir($dh))) {
                if ($feed !== '.' && $feed !== '..' && is_dir($dir . $feed)) {
                    $created = filectime($dir . $feed);
                    $feeds[$created] = $feed;
                }
            }

            closedir($dh);
        }
        
        ksort($feeds, SORT_NUMERIC);
        
        return array_reverse(array_values($feeds));
    }
    
    public static function get_feed_items($category) {
        $items = array();
        $dir = DIR_CONTENT . "/{$category}/";
        if (file_exists($dir)) {
            $dh  = opendir($dir);
            while (false !== ($item = readdir($dh))) {
                if ($item !== '.' && $item !== '..' && is_dir($dir  . $item)) {
                    $created = filectime($dir  . $item);
                    $items[$created] = $item;
                }
            }

            closedir($dh);
        }
        
        ksort($items, SORT_NUMERIC);
        
        return array_reverse(array_values($items));
    }
    
    public static function get_feed_files($category) {
        $items = self::get_feed_items($category);
        $files = array();
        foreach($items as $item) {
            $item = DIR_CONTENT . "/{$category}/{$item}/index.xml";
            $files[] = $item;
        }
        return $files;
    }
    
    public static function make_feeds_select($category) {
        $feeds = self::get_feeds($category);
        
        $html = "";
        foreach($feeds as $thisFeed) {
            //if (strcasecmp($thisFeed, $feed) == 0) {
            //    $html .= "<option selected='selected' value='{$thisFeed}'>{$thisFeed}</option>";
            //} else {
                $html .= "<option value='{$category}/{$thisFeed}'>{$thisFeed}</option>";
            //}
        }
        if (strlen($html) == 0) {
            $html = "<option value=''></option>";
        }
        return $html;
    }
    
    public static function make_feed_items_select($category) {
        $items = self::get_feed_items($category);
        $files = self::get_feed_files($category, $items);
        
        $html = "";
        foreach($files as $file) {
            $sxml = simplexml_load_file($file);
            $title = htmlspecialchars(trim($sxml->title), ENT_QUOTES);
            if (strlen($title) > 0) 
            $html .= "<option value='{$category}/$title'>$title</option>";
        }
        if (strlen($html) == 0) {
            $html = "<option value=''></option>";
        }
        return $html;
    }
    
    public static function create_feed($category) {
        $dir = DIR_CONTENT . "/{$category}";
        $dir = Utils::make_dir($dir, 0750);
        return true;
    }
}

?>
