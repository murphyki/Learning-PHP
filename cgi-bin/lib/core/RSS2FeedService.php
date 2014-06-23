<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Item.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/FacebookService.php");
    include_once(DIR_LIB . "/core/FeedManager.php");
    
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class RSS2FeedService {
    protected $repository;
    protected $name;
    protected $channels = array();
    protected $items = array();
    protected $items_new = array();
    protected $file_name;
    
    const FILE_NAME = "feed.xml";
    
    public function __construct($repository, $name, $title, $description) {
        $this->repository = $repository;
        $this->name = $name;
        $this->file_name = DIR_BASE . "/{$repository}/" . self::FILE_NAME;
        $this->channels[0]['link'] = "http://" . APP_DOMAIN . "/{$repository}/" . self::FILE_NAME;
        $this->channels[0]['title'] = $title;
        $this->channels[0]['description'] = $description;
        $this->channels[0]['lang'] = 'en';
        $this->channels[0]['copyright'] = 'Copright ' . date('Y') .  ' ' . APP_NAME;
        $this->channels[0]['pubDate'] = date('r');
        $this->channels[0]['lastBuildDate'] = date('r');
        $this->channels[0]['lang'] = 'en';
        $this->items = $this->load_items();
    }
    
    public function get_modified_time() {
        if (file_exists($this->file_name)) {
            return filemtime($this->file_name);
        } else {
            return 0;
        }
    }
    
    public function is_modified($token) {
        $feed_modified_time = $this->get_modified_time();
        if ($feed_modified_time > $token) {
            return("true");
        } else {
            return("false");
        }
    }
    
    public function publish_item(Item $item) {
        if ($item === null) {
            Logger::log_error("Trying to publish an invalid item", __FILE__, __LINE__);
            return;
        }
        
        $post_to_social_media = $this->add_item($item);
        $this->output();
        
        if (true /*$post_to_social_media*/) {
            $fb = new FacebookService();
            $fb->publish_item($item, "{$this->name} Updated");
        }
    }
    
    public function add_item(Item $item) {
        $is_new = true;
        
        for($k = 0; $k < count($this->items); $k++) {
            if (strcasecmp($this->items[$k]['guid'], $item->getLink()) == 0) {
                $is_new = false;
                $this->items[$k]['link'] = $item->getLink();
                $this->items[$k]['title'] = $item->getTitle();
                $this->items[$k]['description'] = $item->getDescription();
                // If an existing item keep the original pub date
                //$this->items[$k]['pubDate'] = date('r', strtotime($item->getPub_Date()));
                // But update the lastBuildDate to be now
                $this->items[$k]['updated'] = date(DateTime::RFC3339);
                $this->items[$k]['guid'] = $item->getLink();
            }
        }
        
        for($k = 0; $k < count($this->items_new); $k++) {
            if (strcasecmp($this->items_new[$k]['guid'], $item->getLink()) == 0) {
                $is_new = false;
                $this->items_new[$k]['link'] = $item->getLink();
                $this->items_new[$k]['title'] = $item->getTitle();
                $this->items_new[$k]['description'] = $item->getDescription();
                $this->items_new[$k]['pubDate'] = date('r', strtotime($item->getPub_Date()));
                $this->items_new[$k]['updated'] = date(DateTime::RFC3339);
                $this->items_new[$k]['guid'] = $item->getLink();
            }
        }
        
        if ($is_new) {
            $num_items = count($this->items_new);
            $this->items_new[$num_items]['link'] = $item->getLink();
            $this->items_new[$num_items]['title'] = $item->getTitle();
            $this->items_new[$num_items]['description'] = $item->getDescription();
            $this->items_new[$num_items]['pubDate'] = date('r', strtotime($item->getPub_Date()));
            $this->items_new[$num_items]['updated'] = date(DateTime::RFC3339, strtotime($item->getLast_Build_Date()));
            $this->items_new[$num_items]['guid'] = $item->getLink();
        }
        
        return $is_new;
    }
    
    public function output() {
        $output =  '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $output .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
        $output .= '<channel>'."\n";
        $output .= '<title>'.$this->channels[0]['title'].'</title>'."\n";
        $output .= '<link>'.$this->channels[0]['link'].'</link>'."\n";
        $output .= '<description>'.$this->channels[0]['description'].'</description>'."\n";
        $output .= '<language>'.$this->channels[0]['lang'].'</language>'."\n";
        $output .= '<copyright>'.$this->channels[0]['copyright'].'</copyright>'."\n";
        $output .= '<pubDate>'.$this->channels[0]['pubDate'].'</pubDate>'."\n";
        $output .= '<lastBuildDate>'.$this->channels[0]['lastBuildDate'].'</lastBuildDate>'."\n";
        $output .= '<atom:link rel="self" type="application/rss+xml" href="'.$this->channels[0]['link'].'" />'."\n";
        
        for($k = 0; $k < count($this->items_new); $k++) {
            $output .= '<item>'."\n";
            $output .= '<title>'.$this->items_new[$k]['title'].'</title>'."\n";
            $output .= '<link>'.$this->items_new[$k]['link'].'</link>'."\n";
            $output .= '<description>'.$this->items_new[$k]['description'].'</description>'."\n";
            $output .= '<pubDate>'.$this->items_new[$k]['pubDate'].'</pubDate>'."\n";
            $output .= '<atom:updated>'.$this->items_new[$k]['updated'].'</atom:updated>'."\n";
            $output .= '<guid>'.$this->items_new[$k]['guid'].'</guid>'."\n";
            $output .= '</item>'."\n";
        };

        for($k = 0; $k < count($this->items); $k++) {
            $output .= '<item>'."\n";
            $output .= '<title>'.$this->items[$k]['title'].'</title>'."\n";
            $output .= '<link>'.$this->items[$k]['link'].'</link>'."\n";
            $output .= '<description>'.$this->items[$k]['description'].'</description>'."\n";
            $output .= '<pubDate>'.$this->items[$k]['pubDate'].'</pubDate>'."\n";
            $output .= '<atom:updated>'.$this->items[$k]['updated'].'</atom:updated>'."\n";
            $output .= '<guid>'.$this->items[$k]['guid'].'</guid>'."\n";
            $output .= '</item>'."\n";
        };
        $output .= '</channel>'."\n";
        $output .= '</rss>'."\n";
        
        if(file_put_contents($this->file_name, $output)) {
            chmod($this->file_name, 0644);
        } else {
            Logger::log_error("Error writing RSS feed {$this->file_name}", __FILE__, __LINE__);
        }
        
        return $output;
    }
    
    public function get_items($howMany = -1) {
        $this->load_items();
        $num_actual_items = count($this->items);
        $num_items = ($howMany == -1 ?  $num_actual_items : $howMany);
        if ($num_items > $num_actual_items) {
            $num_items = $num_actual_items;
        }
        
        return array_slice($this->items, 0, $num_items);
    }
    
    public function format_feeds_for_selector() {
        return FeedManager::make_feeds_select($this->repository);
    }
    
    private function load_items() {
        if (!file_exists($this->file_name)) {
            return array();
        }
        
        if (filesize($this->file_name) == 0) {
            return array();
        }

        $dom = new DOMDocument();
        if (!$dom->load($this->file_name)) {
            return array();
        }

        $rss = $dom->getElementsByTagName('rss')->item(0);
        $channel = $rss->getElementsByTagName('channel')->item(0);
        $items = $channel->getElementsByTagName('item');
        $orig_items = array();
        foreach ($items as $item) {
            $link = $item->getElementsByTagName('link')->item(0)->nodeValue;
            $title = $item->getElementsByTagName('title')->item(0)->nodeValue;
            $description = $item->getElementsByTagName('description')->item(0)->nodeValue;
            $pubDate = $item->getElementsByTagName('pubDate')->item(0)->nodeValue;
            $updated = $item->getElementsByTagName('updated')->item(0)->nodeValue;
            $guid = $item->getElementsByTagName('guid')->item(0)->nodeValue;
            $num_items = count($orig_items);
            $orig_items[$num_items]['link']=$link;
            $orig_items[$num_items]['title']=$title;
            $orig_items[$num_items]['description']=$description;
            $orig_items[$num_items]['pubDate']=$pubDate;
            $orig_items[$num_items]['guid']=$guid;
            $orig_items[$num_items]['updated']=$updated;
        }
        return $orig_items;
    }
}
?>
