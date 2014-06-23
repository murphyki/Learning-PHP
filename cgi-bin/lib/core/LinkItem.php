<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LinkItem
 *
 * @author Kieran
 */
class LinkItem {
    private $parent;
    private $caption;
    private $href;
    private $title;
    private $target;
    
    public function __construct($parent) {
        $this->parent = $parent;
    }
    
    public function getParent() {
        return $this->parent;
    }
    
    public function getName() {
        return $this->caption;
    }
    
    public function getDir() {
        return $this->getParent() . $this->getName();
    }
    
    public function getCaption() {
        return $this->caption;
    }

    public function setCaption($caption) {
        $this->caption = $caption;
    }
    
    public function getHref() {
        return $this->href;
    }
    
    public function getEncodedHref() {
        return Utils::encode_url($this->href);
    }

    public function setHref($href) {
        $this->href = $href;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTarget() {
        return $this->target;
    }

    public function setTarget($target) {
        $this->target = $target;
    }
}

?>
