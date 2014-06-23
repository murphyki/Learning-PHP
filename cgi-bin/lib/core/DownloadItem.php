<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DownloadItem
 *
 * @author Kieran
 */
class DownloadItem {
    private $parent;
    private $file;
    private $lastmodified;
    private $size;
    private $link;
    private $viewable;
    
    public function __construct($parent) {
        $this->parent = $parent;
    }
    
    public function getParent() {
        return $this->parent;
    }
    
    public function getName() {
        return $this->file;
    }
    
    public function getDir() {
        return $this->getParent() . $this->getName();
    }
    
    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function getLastmodified() {
        return $this->lastmodified;
    }

    public function setLastmodified($lastmodified) {
        $this->lastmodified = $lastmodified;
    }

    public function getSize() {
        return $this->size;
    }

    public function setSize($size) {
        $this->size = $size;
    }

    public function getLink() {
        return $this->link;
    }

    public function setLink($link) {
        $this->link = $link;
    }
    
    public function isViewable() {
        return $this->viewable;
    }

    public function getViewable() {
        return $this->isViewable();
    }
    
    public function setViewable($viewable) {
        $this->viewable = $viewable;
    }

}

?>
