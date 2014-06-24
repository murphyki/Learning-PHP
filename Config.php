<?php

// The application domain name
define("APP_DOMAIN",            "localhost");
define("APP_NAME",              "My New App");
define("WEBMASTER",             "webmaster@mynewapp.com");

define("FACEBOOK_APP_ID",       "");
define("FACEBOOK_APP_SECRET",   "");
define("FACEBOOK_PAGE_NAME",    "");

// The root of the web app
define('DIR_BASE',              dirname(__FILE__));

define('GALLERY_ALIAS',         'gallery');

// Physical directories
define("DIR_ADMIN",             DIR_BASE  . DIRECTORY_SEPARATOR . "admin");
define("DIR_LOGS",              DIR_ADMIN . DIRECTORY_SEPARATOR . "logs");
define('DIR_LIB',               DIR_BASE  . DIRECTORY_SEPARATOR . "cgi-bin" . DIRECTORY_SEPARATOR . "lib");
define("DIR_CONTENT",           DIR_BASE  . DIRECTORY_SEPARATOR . "cgi-bin" . DIRECTORY_SEPARATOR . "content");
define("DIR_CONTENT_GALLERY",   DIR_CONTENT  . DIRECTORY_SEPARATOR . GALLERY_ALIAS);
define("DIR_MEDIA",             DIR_BASE  . DIRECTORY_SEPARATOR . "media");
define("DIR_CSS",               DIR_MEDIA . DIRECTORY_SEPARATOR . "css");
define("DIR_IMAGES",            DIR_MEDIA . DIRECTORY_SEPARATOR . "images");
define("DIR_GALLERY",           DIR_MEDIA . DIRECTORY_SEPARATOR . "images");// default is same as images
define("DIR_DOWNLOADS",         DIR_BASE  . DIRECTORY_SEPARATOR . "downloads");
define("DIR_DOWNLOADS_FILES",   DIR_BASE  . DIRECTORY_SEPARATOR . "downloads" . DIRECTORY_SEPARATOR . "files");
define("DIR_BASE_TEMPLATES",    DIR_BASE  . DIRECTORY_SEPARATOR . "templates");

// File names
define("FILE_NAVIGATION_MENU",  DIR_BASE_TEMPLATES . DIRECTORY_SEPARATOR . "navigation.twig");
define("FILE_ERROR_LOG",        DIR_LOGS . DIRECTORY_SEPARATOR . "errors.log");
define("FILE_INFO_LOG",         DIR_LOGS . DIRECTORY_SEPARATOR . "info.log");
define("FILE_WARNING_LOG",      DIR_LOGS . DIRECTORY_SEPARATOR . "warnings.log");
define("FILE_APP_CSS",          DIR_CSS  . DIRECTORY_SEPARATOR . "app.css");

// Root URLs
define("MEDIA_URL",             "/media");
define("ADMIN_URL",             "/admin");
define("IMAGES_URL",            MEDIA_URL. "/images");
define("GALLERIES_URL",         MEDIA_URL. "/images"); // note: same as IMAGES_URL for now
define("HOME_URL",              "/");

define("TWIG_COMPILATION_CACHE", false);// or the folder name: DIR_BASE . 'compilation_cache'

define("GENERAL_ERROR_PAGE",    "/error.php");

define("PAGE_WIDTH",            960);
define("SLIDER_WIDTH",          388);
define("SLIDER_HEIGHT",         257);

define("GENERATE_SITEMAP",      true);

include_once(DIR_LIB . "/core/ArticleItem.php");

abstract class Config {
    public static function parse_template(array $template_dirs, $template_file, array $context) {
        // N.B. Assumes you have twig initialised...

        $theme = 'green_lemon.php'; //default.php';

        if (isset($context['DEMO']) && strcasecmp($context['DEMO'], 'demo') == 0) {
            $theme = $context['THEME'];
        }

        $item = new ArticleItem("left_content", "Left Content");
        $item->load_from_xml();

        $uber_context = array(
            'APP_NAME'=>APP_NAME,
            'APP_DOMAIN'=>APP_DOMAIN,
            'WEBMASTER'=>WEBMASTER,
            'MEDIA_URL'=>MEDIA_URL,
            'THEME'=>$theme,
            'LEFT_CONTENT'=>$item->getContent(),
            'CSS_CACHE_BUST'=>filemtime(DIR_MEDIA . "/css/app.css"),
            'JS_CACHE_BUST'=>filemtime(DIR_MEDIA . "/js/base.js")
        );

        foreach ($context as $key => $value) {
            $uber_context[$key] = $value;
        }

        $all_templates_dirs = array(
            DIR_BASE_TEMPLATES,
        );

        foreach ($template_dirs as $dir) {
            $all_templates_dirs[] = $dir;
        }

        $loader = new Twig_Loader_Filesystem($all_templates_dirs);
        $twig = new Twig_Environment($loader, array(
            'cache' => false, //'compilation_cache',
        ));

        $template = $twig->loadTemplate($template_file);

        return ($template->render($uber_context));
    }

    public static function load_template(array $template_dirs, $template_file, array $context) {
        echo(self::parse_template($template_dirs, $template_file, $context));
    }

    public static function get_admins() {
        $admins = array(
            array("Billy Bob", "billybob@mynewapp.com"),
            array("Billy Bob", "billy.bob.9275")
        );

        return $admins;
    }
    
    public static function is_localhost() {
        return strcasecmp(APP_DOMAIN, "localhost") == 0;
    }
};
?>
