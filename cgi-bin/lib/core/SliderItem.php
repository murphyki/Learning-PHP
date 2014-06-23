<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SliderItem
 *
 * @author Kieran
 */
class SliderItem {
    private $parent;
    private $image; // The image 
    private $source;// Full path to image including image file
    private $sourceWithCacheBust;
    private $description;
    private $link;
    private $width;
    private $height;
    
    public function __construct($parent) {
        $this->parent = $parent;
    }
    
    public function getParent() {
        return $this->parent;
    }
    
    public function getName() {
        return $this->image;
    }
    
    public function getDir() {
        return $this->getParent() . $this->getName();
    }
    
    public function getImage() {
        return $this->image;
    }
    
    public function setImage($image) {
        $this->image = $image;
    }
    
    public function getSource() {
        return $this->source;
    }
    
    public function getEncodedSource() {
        return Utils::encode_url($this->source);
    }

    public function setSource($source) {
        $this->source = $source;
    }
    
    public function getSourceWithCacheBust() {
        return $this->sourceWithCacheBust;
    }
    
    public function getEncodedSourceWithCacheBust() {
        return Utils::encode_url($this->sourceWithCacheBust);
    }
    
    public function setSourceWithCacheBust($sourceWithCacheBust) {
        $this->sourceWithCacheBust = $sourceWithCacheBust;
    }
    
    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getLink() {
        return $this->link;
    }

    public function setLink($link) {
        $this->link = $link;
    }
    
    public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
    }

    public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
    }
}

?>
