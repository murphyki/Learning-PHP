<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/ArticleItem.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RSSItem
 *
 * @author Kieran
 */
class RSSItem extends ArticleItem {
    public function __construct($category, $title) {
        parent::__construct($category, $title);
    }

    public function publish() {
        parent::publish();
        
        //$rss = new NewsFeedService($this->feed);
        //$rss->publish_item($this);
    }
}

?>
