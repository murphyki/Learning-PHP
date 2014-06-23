<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CategoryItem
 *
 * @author Kieran
 */
class CategoryItem {
    private $parent;
    private $name;
    private $dir;
    private $viewable;
    private $hasSubContent = false;
    private $adminUrl;
    
    public function __construct($parent) {
        $this->parent = $parent;
    }
    
    public function getParent() {
        return $this->parent;
    }
    
    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getDir() {
        return $this->dir;
    }

    public function setDir($dir) {
        $this->dir = $dir;
    }
    
    public function getViewable() {
        return $this->viewable;
    }

    public function setViewable($viewable) {
        $this->viewable = $viewable;
    }
    
    public function getHasSubContent() {
        return $this->hasSubContent;
    }

    public function setHasSubContent($hasSubContent) {
        $this->hasSubContent = $hasSubContent;
    }

    public function getAdminUrl() {
        return $this->adminUrl;
    }

    public function setAdminUrl($adminUrl) {
        $this->adminUrl = $adminUrl;
    }
}

?>
