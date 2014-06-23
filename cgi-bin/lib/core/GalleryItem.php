<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GalleryItem
 *
 * @author Kieran
 */
class GalleryItem {
    private $parent;
    private $name;
    private $viewable;
    private $adminUrl;
    
    public function __construct($parent) {
        $this->parent = $parent;
    }
    
    public function getParent() {
        return $this->parent;
    }
    
    public function getDir() {
        return $this->getParent() . $this->getName();
    }
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getViewable() {
        return $this->viewable;
    }

    public function setViewable($viewable) {
        $this->viewable = $viewable;
    }
    
    public function getAdminUrl() {
        return $this->adminUrl;
    }

    public function setAdminUrl($adminUrl) {
        $this->adminUrl = $adminUrl;
    }

}

?>
