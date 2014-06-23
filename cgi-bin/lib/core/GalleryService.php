<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/GalleryController.php");
    include_once(DIR_ADMIN . "/GalleryImagesController.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GalleryService
 *
 * @author Kieran
 */
class GalleryService {
    private static $category;
    private static $controller = null;
    private static $image_controller = null;
    
    // The clone and wakeup methods prevents external instantiation of copies of the Singleton class,
    // thus eliminating the possibility of duplicate objects.
    public function __clone() {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    public function __wakeup() {
        trigger_error('Deserializing is not allowed.', E_USER_ERROR);
    }
    
    static function static_initialiser() {
        self::$category = GALLERY_ALIAS . "/";
        self::$controller = new GalleryController();
        self::$image_controller = new GalleryImagesController();
    }

    // private constructor
    private function __construct() {
    }
    
    public static function get_modified_time() {
        return self::$controller->get_modified_time();
    }
    
    public static function is_modified($token) {
        return self::$controller->is_modified($token);
    }
    
    public static function get_items() {
        return self::$controller->get_items(self::$category);
    }
    
    public static function get_viewable_items() {
        return self::$controller->get_viewable_items(self::$category);
    }
    
    public static function get_image_items($gallery) {
        return self::$image_controller->get_items("gallerys/{$gallery}");
    }
    
    public static function get_viewable_image_items($gallery) {
        return self::$image_controller->get_viewable_items("gallerys/{$gallery}");
    }
    
    public static function is_valid_gallery($gallery, $check_viewable = true) {
        return self::$controller->is_valid_gallery($gallery, $check_viewable);
    }
    
    public static function get_gallery_from_url($url) {
        return self::$controller->get_gallery_from_url($url);
    }
    
    public static function format_galleries_for_selector($selected_gallery) {
        $items = self::get_viewable_items(self::$category);
        $gallery = self::is_valid_gallery($selected_gallery) ? $selected_gallery : "";
        $html = "";
        foreach ($items as $item) {
            if ($item->getViewable()) {
                $name = $item->getName();
                if (strcasecmp($gallery, $name) == 0) {
                    $html .= "<option selected='selected' value='{$name}'>{$name}</option>";
                } else {
                    $html .= "<option value='{$name}'>{$name}</option>";
                }
            }
        }
        
        return $html;
    }
    
    public static function format_for_gallery_page($gallery) {
        $metadata = self::get_viewable_image_items($gallery);
        $filtered_metadata = array();
        foreach($metadata as $data) {
            if ($data->getViewable()) {
                $filtered_metadata[] = array(
                    "image"=>$data->getEncodedImageWithCacheBust(),//getImageWithCacheBust(), //getImage(),
                    "thumb"=>$data->getEncodedThumbNail(),//getThumbNail(),
                    "name"=>$data->getName(),        
                    "title"=>$data->getTitle(),
                    "description"=>$data->getDescription(),
                    "viewable"=>$data->getViewable() == true ? "yes" : "no"
                );
            }
        }
        
        $token = self::get_modified_time();
        
        $data = array(
            "token"=>$token,
            "items"=>$filtered_metadata
        );
        
        return json_encode($data);
    }
    
} GalleryService::static_initialiser();

?>
