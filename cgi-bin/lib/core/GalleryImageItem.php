<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GalleryImageItem
 *
 * @author Kieran
 */
class GalleryImageItem {
    private $parent;
    private $image;
    private $imageWithCacheBust;
    private $thumbNail;
    private $name;
    private $title;
    private $description;
    private $viewable;
    
    public function __construct($parent) {
        $this->parent = $parent;
    }
    
    public function getParent() {
        return $this->parent;
    }
    
    public function getDir() {
        return $this->getParent() . "/" . $this->getName();
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function getEncodedImage() {
        return Utils::encode_url($this->image);
    }

    public function setImage($image) {
        $this->image = $image;
    }
    
    public function getImageWithCacheBust() {
        return $this->imageWithCacheBust;
    }
    
    public function getEncodedImageWithCacheBust() {
        return Utils::encode_url($this->imageWithCacheBust);
    }

    public function setImageWithCacheBust($imageWithCacheBust) {
        $this->imageWithCacheBust = $imageWithCacheBust;
    }

    public function getThumbNail() {
        return $this->thumbNail;
    }
    
    public function getEncodedThumbNail() {
        return Utils::encode_url($this->thumbNail);
    }

    public function setThumbNail($thumbNail) {
        $this->thumbNail = $thumbNail;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getViewable() {
        return $this->viewable;
    }

    public function setViewable($viewable) {
        $this->viewable = $viewable;
    }
    
}

?>
