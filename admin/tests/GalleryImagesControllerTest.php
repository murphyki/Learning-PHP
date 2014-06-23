<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    require_once(DIR_ADMIN . "/simpletest/autorun.php");
    include_once(DIR_ADMIN . "/GalleryImagesController.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GalleryImagesControllerTest
 *
 * @author kieran
 */
class GalleryImagesControllerTest extends UnitTestCase {
    private $parent;
    private $category;
    private $name;
    private $description;
    private $title;
    private $image;
    private $viewable;
            
    public function setUp() {
        $this->name = "header.png";
        $this->image = $this->name;
        $this->description = "The description";
        $this->title = "The title";
        $this->viewable = false;
        $this->parent = "gallerys/design";
        $this->category = $this->parent . "/" . $this->name;
        $controller = new GalleryImagesController();
        $controller->save_item($this->parent, 
            $this->name,
            $this->description,
            $this->title,
            $this->image,
            $this->viewable
        );
    }
    
    public function tearDown() {
        //$controller = new GalleryImagesController();
        //$controller->delete_item($this->parent, $this->category);
    }
    
    public function testFindItem() {
        $controller = new GalleryImagesController();
        $item = $controller->find_item($this->parent, $this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getName(), $this->name);
    }
    
    public function testFindItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new GalleryImagesController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testFindItem_null_categotry() {
        $category = null;
        $controller = new GalleryImagesController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testGetItem() {
        $controller = new GalleryImagesController();
        $item = $controller->get_item($this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getName(), $this->name);
    }
    
    public function testGetItem_nonexistant_categotry() {
        $category = "design/blah.png";
        $controller = new GalleryImagesController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItem_null_categotry() {
        $category = null;
        $controller = new GalleryImagesController();
        $item = $controller->get_item($category);
        $this->dump($item);
        $this->assertNull($item);
    }
    
    public function testGetItems() {
        $controller = new GalleryImagesController();
        $items = $controller->get_items($this->parent);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);
    }
    
    public function testGetItems_nonexistant_categotry() {
        $controller = new GalleryImagesController();
        $items = $controller->get_items("blah");
        $this->assertNotNull($items);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testGetItems_null_categotry() {
        $controller = new GalleryImagesController();
        $items = $controller->get_items(null);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testUpdateItem() {
        $controller = new GalleryImagesController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, $this->category, 
            $original_item->getName(),
            "updated description",
            "updated title",
            $original_item->getImage(),
            true
        );
        $this->assertNotNull($updated_item);
        $this->assertEqual($updated_item->getName(), $original_item->getName());
        $this->assertEqual($updated_item->getImage(), $original_item->getImage());
        $this->assertEqual($updated_item->getDescription(), "updated description");
        $this->assertEqual($updated_item->getTitle(), "updated title");
        $this->assertEqual($updated_item->getViewable(), true);
    }
    
    public function testUpdateItem_nonexistant_categotry() {
        $controller = new GalleryImagesController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, 
            "nonexistant_categotry",
            $original_item->getName(),
            "updated description",
            "updated title",
            $original_item->getImage(),
            true
        );
        $this->assertNull($updated_item);
    }
    
    public function testUpdateItem_null_categotry() {
        $controller = new GalleryImagesController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, 
            null, 
            $original_item->getName(),
            "updated description",
            "updated title",
            $original_item->getImage(),
            true
        );
        $this->assertNull($updated_item);
    }
    
    public function testSaveItem() {
        $controller = new GalleryImagesController();
        $new_item = $controller->save_item($this->parent, 
            "buttons.png",
            "The description",
            "The title",
            "buttons.png",
            true
        );
        
        $this->assertNotNull($new_item);
        $this->assertEqual($new_item->getName(), "buttons.png");
        $this->assertEqual($new_item->getDescription(), "The description");
        $this->assertEqual($new_item->getTitle(), "The title");
        $this->assertEqual($new_item->getImage(), GALLERIES_URL . "/design/buttons.png");
        $this->assertEqual($new_item->getViewable(), true);
    }
    /*
    public function testSaveItem_duplicate() {
        $controller = new GalleryImagesController();
        $new_item1 = $controller->save_item($this->parent, 
            "design", 
            true
        );
        $this->assertNotNull($new_item1);
        
        $new_item2 = $controller->save_item($this->parent, 
            "design", 
            true
        );
        $this->assertNotNull($new_item2);
        $this->assertEqual($new_item1, $new_item2);
                
        $deleted_item1 = $controller->delete_item($new_item1->getParent(), $new_item1->getDir());
        $this->assertNotNull($deleted_item1);
        
        // GalleryImagesController is allowing duplicate items but when deleting
        // it deletes all items with the same caption...
        // Do we want to allow this behaviour???
        // 
        //$deleted_item2 = $controller->delete_item($new_item2->getParent(), $new_item2->getDir());
        //$this->assertNotNull($deleted_item2);
    }
    */
    public function testDeleteItem() {
        $controller = new GalleryImagesController();
        $item = $controller->get_item($this->category);
        $this->assertNotNull($item);
        $deleted_item = $controller->delete_item($item->getParent(), $item->getDir());
        $this->assertNull($deleted_item); // delete not allowed
    }
    
    public function testDeleteItem_nonexistant_item() {
        $controller = new GalleryImagesController();
        $deleted_item = $controller->delete_item($this->parent, "nonexistant_item");
        $this->assertNull($deleted_item); // delete not allowed
    }
    
    public function testDeleteItem_null_item() {
        $controller = new GalleryImagesController();
        $deleted_item = $controller->delete_item($this->parent, null);
        $this->assertNull($deleted_item); // delete not allowed
    }
}

?>
