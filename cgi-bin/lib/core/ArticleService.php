<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/ArticleItem.php");
    include_once(DIR_LIB . "/core/Logger.php");

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NewsFeedService
 *
 * @author Kieran
 */
class ArticleService {
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
    
    private static function load_article_list($category) {
        $articles = array();
        $file = self::get_config_file($category, false);
        if (file_exists($file)) {
            $sxml = simplexml_load_file($file);
            foreach ($sxml->articles->children() as $article) {
                $articles[] = array(
                    "title"=>(string)$article->title, 
                    "category"=>(string)$article->category);
            }
        }
        
        return $articles;
    }
    
    public static function save_article_list($category, array $names, array $categories) {
        if (count($names) != count($categories)) {
            Logger::log_error("Error saving article list data...", __FILE__, __LINE__);
            return;
        }
        
        $tuples = array();
        for($i = 0; $i < count($names); $i++) {
            $tuples[] = array($names[$i], $categories[$i]);
        }
        
        $dom = new DOMDocument();
        $articles = $dom->createElement('articles');
        foreach($tuples as $tuple) {
            $title = $dom->createElement('title');
            $title->appendChild($dom->createTextNode($tuple[0]));
            $cat = $dom->createElement('category');
            $cat->appendChild($dom->createTextNode($tuple[1]));
            $article = $dom->createElement('article');
            $article->appendChild($title);
            $article->appendChild($cat);
            $articles->appendChild($article);
        }
        
        $root = $dom->createElement('articles_list');
        $root->appendChild($articles);
        
        $dom->appendChild($root);
        $buffer = $dom->saveXml();
    
        $file = self::get_config_file($category);
        if (file_put_contents($file, $buffer)) {
            chmod($file, 0640);
        } else {
            Logger::log_error("Problem saving article list data", __FILE__, __LINE__);
        }
    }
    
    public static function get_items($category) {
        $articles = self::load_article_list($category);
        $items = array();
        foreach ($articles as $article) {
            $item = new ArticleItem($article["category"], $article["title"]);
            $item->load_from_xml();
            $items[] = $item;
        }
        
        return $items;
    }
    
    private static function get_config_file($category, $createDir = true) {
        $dir = "";
        if ($category === null || 
            strlen($category) == 0 || 
            strcasecmp($category, "root") == 0) {
            $dir = DIR_CONTENT;
        } else {
            $dir = DIR_CONTENT . DIRECTORY_SEPARATOR . $category;
        }
        
        if ($createDir) {
            $dir = Utils::make_dir($dir, 0750);
        }
        
        return $dir . "/article_list.xml";
    }
}

?>
