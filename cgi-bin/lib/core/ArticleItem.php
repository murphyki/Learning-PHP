<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Item.php");
    include_once(DIR_LIB . "/core/Logger.php");
    require_once(DIR_LIB . "/Twig/Autoloader.php");
    
    Twig_Autoloader::register();
    
class ArticleItem extends Item {
    private $sub_title;
    private $sub_title2;
    
    public function __construct($category, $title) {
        parent::__construct($category, $title);
    }
    
    public function getSub_Title() {
        return $this->sub_title;
    }

    public function getSub_Title2() {
        return $this->sub_title2;
    }

    public function load_from_xml() {
        $file = $this->get_admin_file_name();
        if (file_exists($file)) {
            $sxml = simplexml_load_file($file);
            $this->pub_date = (string)$sxml->pub_date;
            $this->last_build_date = (string)$sxml->last_build_date;
            $this->title = (string)$sxml->title;
            $this->link = (string)$sxml->link;
            $this->sub_title = (string)$sxml->subTitle;
            $this->sub_title2 = (string)$sxml->subTitle2;
            $this->description = (string)$sxml->description;
            $this->content = (string)$sxml->content;
        } else {
            $this->load_from_request();
        }
    }
    
    public function load_from_request() {
        $this->pub_date = Utils::get_user_input("article_date");
        $this->last_build_date = Utils::get_user_input("article_date");
        $this->title = Utils::get_user_input("article_title");
        $this->sub_title = Utils::get_user_input("article_sub_title");
        $this->sub_title2 = Utils::get_user_input("article_sub_title2");
        $this->description = htmlspecialchars_decode(Utils::get_user_input("article_caption"));
        $this->content = htmlspecialchars_decode(Utils::get_user_input("article_content"));
        $this->link = $this->make_link();
    }
    
    public function save() {
        // Save the article as xml
        $dom = new DOMDocument();
        
        $pub_date = $dom->createElement('pub_date');
        $this->format_pub_date();
        $pub_date->appendChild($dom->createTextNode($this->pub_date));
        
        $last_build_date = $dom->createElement('last_build_date');
        $this->format_last_build_date();
        $last_build_date->appendChild($dom->createTextNode($this->last_build_date));
        
        $title = $dom->createElement('title');
        $title->appendChild($dom->createTextNode($this->title));
        
        $link = $dom->createElement('link');
        $link->appendChild($dom->createTextNode($this->link));
        
        $subTitle = $dom->createElement('subTitle');
        $subTitle->appendChild($dom->createTextNode($this->sub_title));
        
        $subTitle2 = $dom->createElement('subTitle2');
        $subTitle2->appendChild($dom->createTextNode($this->sub_title2));
        
        $description = $dom->createElement('description');
        $description->appendChild($dom->createTextNode($this->description));
        
        $content = $dom->createElement('content');
        $content->appendChild($dom->createTextNode($this->content));
        
        $article = $dom->createElement('article');
        $article->appendChild($pub_date);
        $article->appendChild($last_build_date);
        $article->appendChild($title);
        $article->appendChild($link);
        $article->appendChild($subTitle);
        $article->appendChild($subTitle2);
        $article->appendChild($description);
        $article->appendChild($content);
        
        $dom->appendChild($article);
        $buffer = $dom->saveXml();
    
        $file = $this->get_admin_file_name(true);
        if (file_put_contents($file, $buffer)) {
            chmod($file, 0640);
        } else {
            Logger::log_error("Problem saving article to file {$file}", __FILE__, __LINE__);
            return false;
        }
        
        return true;
    }
    
    public function publish() {
        // Pubish the article - if requested
        // Always publish from the saved xml document
        $this->load_from_xml();
        
        $context = array(
            "item_category"=>$this->getCategory(),
            "item_title"=>$this->getTitle()
        );
        
        $content = Config::parse_template(array(), "index.twig", $context);
        
        $file = $this->get_publish_file_name(true);
        if (file_put_contents($file, $content)) {
            chmod($file, 0644);
        } else {
            Logger::log_error("Problem publishing article to file {$file}", __FILE__, __LINE__);
            return false;
        }
        
        return true;
    }
    
    public function __toString() {
        $str = parent::__toString();
        $str .= "sub_title: " . $this->sub_title . "<br/>";
        $str .= "sub_title2: " . $this->sub_title2 . "<br/>";
        return $str;
    }
}

?>
