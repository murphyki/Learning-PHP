<?php
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  include_once(DIR_ADMIN . "/SliderController.php");
  include_once(DIR_LIB . "/core/Logger.php");
  
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SliderService
 *
 * @author Kieran
 */
class SliderService {
    private static $category = "slider";
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
        self::$controller = new SliderController();
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
    
    public static function format_slider_images_for_homepage() {
        $items = self::get_items();
        $html = "";
        foreach($items as $item) {
            $src = $item->getSource();
            $alt = $item->getDescription();
            $link = $item->getLink();
            if ($link === null || strlen($link) == 0) {
                // No link provided so try and figure one out
                // based on the image url
                $gallery = GalleryService::get_gallery_from_url($src);
                if ($gallery !== null && strlen($gallery) > 0) {
                    $gallery = rawurlencode($gallery);
                    $link = GALLERIES_URL . "/index.php?gallery={$gallery}";
                }
            }
            
            if ($link !== null) {
                $html .= "<a href='{$link}' alt='{$alt}'><img src='{$src}' alt='{$alt}'/></a>";
            } else {
                $html .= "<img src='{$src}' alt='{$alt}'/>";
            }
        }
        
        return $html;
    }
    
} SliderService::static_initialiser();

?>
