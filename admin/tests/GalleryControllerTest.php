<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    require_once(DIR_ADMIN . "/simpletest/autorun.php");
    include_once(DIR_ADMIN . "/GalleryController.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GalleryControllerTest
 *
 * @author kieran
 */
class GalleryControllerTest extends UnitTestCase {
    private $parent;
    private $category;
    private $name;
    private $viewable;
            
    public function setUp() {
        $this->name = "design";
        $this->viewable = true;
        $this->parent = GALLERY_ALIAS . "/";
        $this->category = $this->parent . $this->name;
        $controller = new GalleryController();
        $controller->save_item($this->parent, 
            $this->name,
            $this->viewable
        );
    }
    
    public function tearDown() {
        $controller = new GalleryController();
        $controller->delete_item($this->parent, $this->category);
    }
    
    public function testFindItem() {
        $controller = new GalleryController();
        $item = $controller->find_item($this->parent, $this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getName(), $this->name);
    }
    
    public function testFindItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new GalleryController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testFindItem_null_categotry() {
        $category = null;
        $controller = new GalleryController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testGetItem() {
        $controller = new GalleryController();
        $item = $controller->get_item($this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getName(), $this->name);
    }
    
    public function testGetItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new GalleryController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItem_null_categotry() {
        $category = null;
        $controller = new GalleryController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItems() {
        $controller = new GalleryController();
        $items = $controller->get_items($this->parent);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);
    }
    
    public function testGetItems_nonexistant_categotry() {
        $controller = new GalleryController();
        $items = $controller->get_items("blah");
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);// GalleryController has its own category
    }
    
    public function testGetItems_null_categotry() {
        $controller = new GalleryController();
        $items = $controller->get_items(null);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testUpdateItem() {
        $controller = new GalleryController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, $this->category, 
            $original_item->getName(),
            true
        );
        $this->assertNotNull($updated_item);
        $this->assertEqual($updated_item->getName(), $original_item->getName());
        $this->assertEqual($updated_item->getViewable(), true);
    }
    
    public function testUpdateItem_nonexistant_categotry() {
        $controller = new GalleryController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, 
            "nonexistant_categotry",
            $original_item->getName(),
            true
        );
        $this->assertNull($updated_item);
    }
    
    public function testUpdateItem_null_categotry() {
        $controller = new GalleryController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, 
            null, 
            $original_item->getName(),
            true
        );
        $this->assertNull($updated_item);
    }
    
    public function testSaveItem() {
        $controller = new GalleryController();
        $new_item = $controller->save_item($this->parent, 
            "design", 
            true
        );
       
        $this->assertNotNull($new_item);
        $this->assertEqual($new_item->getName(), "design");
        $this->assertEqual($new_item->getViewable(), true);
        
        $deleted_item = $controller->delete_item($new_item->getParent(), $new_item->getDir());
        $this->assertNotNull($deleted_item);
    }
    
    public function testSaveItem_duplicate() {
        $controller = new GalleryController();
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
        
        // GalleryController is allowing duplicate items but when deleting
        // it deletes all items with the same caption...
        // Do we want to allow this behaviour???
        // 
        //$deleted_item2 = $controller->delete_item($new_item2->getParent(), $new_item2->getDir());
        //$this->assertNotNull($deleted_item2);
    }
    
    public function testDeleteItem() {
        $controller = new GalleryController();
        $new_item = $controller->save_item($this->parent, 
            "design", 
            true
        );
        $this->assertNotNull($new_item);
        $deleted_item = $controller->delete_item($new_item->getParent(), $new_item->getDir());
        $this->assertNotNull($deleted_item);
    }
    
    public function testDeleteItem_nonexistant_item() {
        $controller = new GalleryController();
        $deleted_item = $controller->delete_item($this->parent, "nonexistant_item");
        $this->assertNull($deleted_item);
    }
    
    public function testDeleteItem_null_item() {
        $controller = new GalleryController();
        $deleted_item = $controller->delete_item($this->parent, null);
        $this->assertNull($deleted_item);
    }
    
    public function testIs_valid_gallery() {
        $controller = new GalleryController();
        $result = $controller->is_valid_gallery("design", false);
        $this->assertTrue($result);
    }
    
    public function testIs_valid_gallery_not_viewable() {
        $controller = new GalleryController();
        $result = $controller->is_valid_gallery("design");
        $this->assertFalse($result);
    }
    
    public function testIs_valid_gallery_invalid_gallery() {
        $controller = new GalleryController();
        $result = $controller->is_valid_gallery("blah");
        $this->assertFalse($result);
    }
}

?>
