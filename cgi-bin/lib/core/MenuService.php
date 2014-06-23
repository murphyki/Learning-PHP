<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/CategoryService.php");
    include_once(DIR_LIB . "/core/CategoryItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 * Description of MenuService
 *
 * @author Kieran
 */
class MenuService {
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
    
    public static function generate_menu() {
        $menus = self::make_menu();
        
        $html = "";
        foreach ($menus as $menu) {
            $html .= self::generate_menu_item($menu);
        }
        
        // Attempt to write the new men definition to the
        // template file
        $nav = file_get_contents(FILE_NAVIGATION_MENU);
        $nav = Utils::replace_tag("MENU_ITEMS", $html, $nav);
        if (!file_put_contents(FILE_NAVIGATION_MENU, $nav)) {
            Logger::log_error("Menu generation failed while writing data to the template file.", __FILE__, __LINE__);
        }
        
        // Set the menu width
        $width = floor(PAGE_WIDTH / count($menus));
        $css = file_get_contents(FILE_APP_CSS);
        $css = Utils::replace_tag("MENU_WIDTH", "{$width}px", $css, "/**", "**/");
        if (!file_put_contents(FILE_APP_CSS, $css)) {
            Logger::log_error("Menu generation failed while writing data to the css file.", __FILE__, __LINE__);
        }
        
        // Generate the sitemap if required
        if (GENERATE_SITEMAP) {
            self::generate_sitemap($menus);
        }
        
        return $html;
    }
    
    public static function generate_menu_item($menu) {
        $html = "<li><a href='" . $menu["href"] . "'>" . $menu["name"] . "</a>";
        $sub_menus = $menu["sub_menus"];
        if (count($sub_menus) > 0) {
            $html .= "<ul>\n";
            foreach($sub_menus as $sub_menu) {
                $html .= self::generate_menu_item($sub_menu);
            }
            $html .= "</ul>\n";
        }
        
        $html .= "</li>\n";
        
        return $html;
    }
    
    public static function make_menu() {
        $service = new CategoryService(null); // start at the root
        $items = $service->get_viewable_items();
        $menu = array();
        foreach ($items as $item) {
            $menu[] = self::make_menu_item($item, "");
        }
        
        return $menu;
    } 
    
    private static function make_menu_item(CategoryItem $parentItem, $parentCategory, $do_submneu = true) {
        if (!$parentItem->getViewable()) {
            return "";
        }
        
        $name = $parentItem->getName();
        $dir  = $parentItem->getDir();
        
        $sub_items = array();
        $menu = array();
        
        if ($parentItem->getHasSubContent()) {
            $service = new CategoryService($dir);
            $sub_items = $service->get_viewable_items();
        }
        
        if (strcasecmp($name, "home") == 0) {
            $menu = array(
                "name"=>$name,
                "href"=>HOME_URL,
                "sub_menus"=>array()
            );
        } else {
             if (count($sub_items) > 0 && $do_submneu) {
                $sub_menus = array();
                foreach ($sub_items as $item) {
                    // if an index file is defined in this dir
                    // dont continue making menu items
                    $index_file = DIR_BASE . DIRECTORY_SEPARATOR . $item->getDir() . DIRECTORY_SEPARATOR . "index.php";
                    $index_exists = file_exists($index_file);
                    $sub_menus[] = self::make_menu_item($item, $dir, !$index_exists);
                }
                $menu = array(
                    "name"=>$name,
                    "href"=>"#",
                    "sub_menus"=>$sub_menus
                );
             } else {
                 $file_exists = file_exists(DIR_CONTENT ."/{$dir}/");
                 $file_exists = $file_exists && file_exists(DIR_BASE ."/{$dir}/");
                 if ($file_exists) {
                     $menu = array(
                         "name"=>$name,
                         "href"=>"/{$dir}/",
                         "sub_menus"=>array()
                     );
                 } else {
                     Logger::log_error("Directory does not exist: " . $dir . "\nMenu generation aborted!", __FILE__, __LINE__);
                 }
             }
        }
        
        return $menu;
    }
    
    private static function generate_sitemap($menus) {
        $urls = array();
        foreach ($menus as $menu) {
            $urls = array_merge($urls, self::generate_sitemap_item($menu));
        }
        
        $context = array(
            "APP_DOMAIN"=>APP_DOMAIN,
            "urls"=>$urls
        );
        
        $sitemap = AdminConfig::parse_template(array(DIR_ADMIN . DIRECTORY_SEPARATOR . "templates"), "sitemap_xml.twig", $context);
        $filename = DIR_BASE . DIRECTORY_SEPARATOR . "sitemap.xml";
        file_put_contents($filename, $sitemap);
        
        return $urls;
    }
    
    private static function generate_sitemap_item($menu) {
        $urls = array();
        
        if (strcasecmp($menu["href"], "#") != 0) {
            $urls[] = $menu["href"];
        }
        
        $sub_menus = $menu["sub_menus"];
        if (count($sub_menus) > 0) {
            foreach($sub_menus as $sub_menu) {
                $urls = array_merge($urls, self::generate_sitemap_item($sub_menu));
            }
        }
        
        return $urls;
    }
}

?>
