<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/RSS2FeedService.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Item
 *
 * @author Kieran
 */
abstract class Item {
    protected $link;
    protected $title;
    protected $description;
    protected $content;
    protected $pub_date;
    protected $last_build_date;
    protected $category;
    protected $admin_file_name;
    protected $publish_file_name;
    
    public function __construct($category, $title) {
        $this->category = $category;
        $this->title = $title;
        $this->admin_file_name = "index.xml";
        $this->publish_file_name = "index.php";
    }
    
    public function getCategory() {
        return $this->category;
    }
    
    public function getLink() {
        return $this->link;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getContent() {
        return $this->content;
    }
    
    public function getPub_Date() {
        return $this->pub_date;
    }
    
    public function getLast_Build_Date() {
       return $this->last_build_date;
    }
    
    abstract public function load_from_xml();
    abstract public function load_from_request();
    abstract public function save();
    abstract public function publish();
    
    public function __toString() {
        $str = "link: " . $this->link . "<br/>";
        $str .= "title: " . $this->title . "<br/>";
        $str .= "description: " . $this->description . "<br/>";
        $str .= "pub_date: " . $this->pub_date . "<br/>";
        $str .= "last_build_date: " . $this->last_build_date . "<br/>";
        $str .= "content: " . $this->content . "<br/>";
        return $str;
    }
    
    public function exists() {
        $filename = $this->get_admin_file_name();
        return file_exists($filename);
    }
    
    protected function get_admin_file_name($createDir = false) {
        if (strlen($this->category) == 0) {
            trigger_error("No Category specified.");
        }
        
        $dir = $this->get_valid_path(DIR_CONTENT . "/{$this->category}", DIR_CONTENT);
        
        if ($createDir) {
            $dir = Utils::make_dir($dir, 0750);
        }
        
        return "{$dir}/{$this->admin_file_name}";
    }
    
    protected function get_publish_file_name($createDir = false) {
        if (strlen($this->category) == 0) {
            trigger_error("No Category specified.");
        }
        
        $dir = $this->get_valid_path(DIR_BASE . "/{$this->category}", DIR_BASE);
        
        if ($createDir) {
            $dir = Utils::make_dir($dir);
        }
        
        return "{$dir}/{$this->publish_file_name}";
    }
    
    protected function get_valid_path($path, $root_dir) {
        if ($root_dir === null || strlen($root_dir) == 0) {
            Logger::log_error("bad request", __FILE__, __LINE__);
            die();
        }
        
        if ($path === null || strlen($path) == 0) {
            return $path;
        }
        
        $root = realpath($root_dir);
        if ($root === false) {
            Logger::log_error("bad request", __FILE__, __LINE__);
            die();
        }
        
        $dir = realpath($path);
        if ($dir === false) {
            return $path; // path does not exist yet
        }
        
        $test = substr($dir, 0, strlen($root));
        
        if (strcasecmp($test, $root) == 0) {
            return $dir;
        }
        
        Logger::log_error("bad request", __FILE__, __LINE__);
        die();
    }
    
    protected function make_link() {
        $domain = APP_DOMAIN;
        
        if (strlen($this->category) == 0) {
            trigger_error("No Category specified.");
        }
        
        $link = "http://{$domain}/{$this->category}/index.php";
        
        return $link;
    }
    
    protected function format_pub_date() {
        $time = date("H:i:s");
        $date = date("Y-m-d", strtotime($this->pub_date));
        $this->pub_date = "{$date} {$time}";
    }
    
    protected function format_last_build_date() {
        $time = date("H:i:s");
        $date = date("Y-m-d", strtotime($this->last_build_date));
        $this->last_build_date = "{$date} {$time}";
    }
}

?>
