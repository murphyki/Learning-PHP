<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/CategoryController.php");
    include_once(DIR_ADMIN . "/DownloadsController.php");
    include_once(DIR_ADMIN . "/LinksController.php");
    include_once(DIR_ADMIN . "/SliderController.php");
    include_once(DIR_ADMIN . "/GalleryController.php");
    include_once(DIR_ADMIN . "/GalleryImagesController.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/SecurityService.php");
    include_once(DIR_LIB . "/core/ControllerService.php");
    
abstract class AdminConfig {
    public static function parse_template(array $template_dirs, $template_file, array $context) {
        
        $category = Utils::get_user_input("category");
        
        $uber_context = array(
            "ADMIN_URL"=>ADMIN_URL,
            "CATEGORY"=>$category,
            "USER"=>SecurityService::get_loggedin_user_display_name(),
            "crumbs"=>self::generate_breadcrumb_trail($category)
        );
        
        foreach ($context as $key => $value) {
            $uber_context[$key] = $value;
        }
        
        $status_context = ControllerService::get_status_context();
        
        foreach ($status_context as $key => $value) {
            $uber_context[$key] = $value;
        }
        
        return Config::parse_template($template_dirs, $template_file, $uber_context);
    }
    
    public static function load_template(array $template_dirs, $template_file, array $context) {
        echo(AdminConfig::parse_template($template_dirs, $template_file, $context));
    }
    
    private static function generate_breadcrumb_trail($category) {
        $breadcrumbs = array(
            array("label"=>"Admin Home", "href"=>"")
        );
        
        $admin_opts = self::get_admin_config_options($category);
        $tokens = explode("/", $category);
        
        for($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            if (strlen(trim($token)) == 0) {
                continue;
            }
            
            $url = "";
            $label = "";
            
            if (strcasecmp("gallerys", $token) == 0 && $i == 0) {
                $label = GALLERY_ALIAS;
            } else {
                $label = str_replace("_", " ", $token);
            }
                
            for ($j = 0; $j <= $i; $j++) {
                $token = $tokens[$j];
                
                if (strlen(trim($token)) == 0) {
                    continue;
                }

                if (strcasecmp("gallerys", $token) == 0 && $i == 0) {
                    $url .= GALLERY_ALIAS ;
                } else {
                    $url .= "$token";
                }

                // Only append a slash to the second last item in the url
                if ($j < (count($tokens) - 2)) {
                    $url .= "/";
                }

                $breadcrumb0 = $admin_opts["breadcrumb0"];

                if (strcasecmp($breadcrumb0, "/") == 0) {
                    if (!preg_match("/\/$/i", $url)) {
                        $url .= $breadcrumb0;
                    }
                }
            }
            
            $breadcrumbs[] = array(
                "label"=>$label,
                "href"=>"index.php?category=${url}"
            );
        }
        
        return $breadcrumbs;
    }
    
    public static function get_admin_config_options($category, $name = "") {
        // Make the editor the default url
        $default_url = "/article/editor.php?category=" . $category . "&article_title=" . $name;
        
        $config = array(
            "admin_url"=>$default_url,          // Admin editor URL 
            "generate_toc"=>true,               // Flag to generate a table of contents
            "update_touch_file"=>false,         // Flag to update the touch file
            "toc_template"=>"toc_index.twig",   // Name of TOC template
            //"touch_file"=>"",                 // Name of touch file
            "breadcrumb0"=>"",                  // Base url to use in admin breadcrumb menu
            "show_subcategory_checkbox"=>true   // Show the subcategory checkbox on edit pages
        );
        
        $config_opts = array(
            "links"=>array(
                "admin_url"=>"/index.php?category=links/", 
                "generate_toc"=>false,
                "update_touch_file"=>false,
                "breadcrumb0"=>"/",
                "show_subcategory_checkbox"=>false
            ),
            "downloads"=>array(
                "admin_url"=>"/index.php?category=downloads/", 
                "generate_toc"=>false,
                "update_touch_file"=>false,
                "breadcrumb0"=>"/",
                "show_subcategory_checkbox"=>false
            ),
            GALLERY_ALIAS=>array(
                "admin_url"=>"/index.php?category=" . GALLERY_ALIAS . "/",
                "generate_toc"=>false,
                "update_touch_file"=>false,
                "breadcrumb0"=>"/",
                "show_subcategory_checkbox"=>false
            ),
            "gallerys"=>array(
                "admin_url"=>"/index.php?category=gallerys/", 
                "generate_toc"=>false,
                "update_touch_file"=>false,
                "breadcrumb0"=>"/",
                "show_subcategory_checkbox"=>false
            ),
            "slider"=>array(
                "admin_url"=>"/index.php?category=slider/", 
                "generate_toc"=>false,
                "update_touch_file"=>false,
                "breadcrumb0"=>"/",
                "show_subcategory_checkbox"=>false
            )
        );

        foreach ($config_opts as $key => $value) {
            $pattern = "/\b" . $key . "\b/i";// match word boundary + case insensitive
            if (preg_match($pattern, $category)) {
                $config = $value;
                break;
            }
        }
        
        return $config;
    }
    
    public static function get_controller($category) {
        $tokens = preg_split("/\//", $category);
        $parent = "";
        if (count($tokens) > 1) {
            $parent = implode("/", array_slice($tokens, 0, count($tokens) - 1));
        }
        
        $controllerClass = "CategoryController";
        
        if (strcasecmp($parent, "downloads") == 0) {
            $controllerClass = "DownloadsController";
        } else if (strcasecmp($parent, "links") == 0) {
            $controllerClass = "LinksController";
        } else if (strcasecmp($parent, "slider") == 0) {
            $controllerClass = "SliderController";
        } else if (strcasecmp($parent, GALLERY_ALIAS) == 0) {
            $controllerClass = "GalleryController";
        } else if (Utils::starts_with($parent, "gallerys")) {
            $controllerClass = "GalleryImagesController";
        }
        
        return new $controllerClass();
    }
    
    public static function authenticate() {
        $action = Utils::get_user_input("action");
        if (strlen($action) > 0 && strcasecmp($action, "logout") == 0) {
            SecurityService::logout();
            exit();
        }
        
        if (!SecurityService::is_logged_in()) {
            SecurityService::show_login_page();
            exit();
        }

        if (!SecurityService::is_admin_logged_in()) {
            $user = SecurityService::get_loggedin_user_display_name();
            SecurityService::logout(false);
            $context = array(
                "USER"=>$user,
                "WEBMASTER"=>WEBMASTER
            );
            $error = self::parse_template(array("templates"), "authourisation_error.twig", $context);
            echo($error);
            exit();
        }

        SecurityService::validate();
    }
}

?>
