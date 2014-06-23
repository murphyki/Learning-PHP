<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    require_once(DIR_ADMIN . "/simpletest/autorun.php");
    include_once(DIR_ADMIN . "/LinksController.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LinksControllerTest
 *
 * @author kieran
 */
class LinksControllerTest extends UnitTestCase {
    private $parent;
    private $category;
    private $caption;
    private $href;
    private $title;
    private $target;
            
    public function setUp() {
        $this->caption = "test link";
        $this->href = "http://www.example.com";
        $this->title = "test link title";
        $this->target = "_blank";
        $this->parent = "links/";
        $this->category = $this->parent . $this->caption;
        $controller = new LinksController();
        $controller->save_item($this->parent, 
            $this->caption,
            $this->href,
            $this->title,
            $this->target
        );
    }
    
    public function tearDown() {
        $controller = new LinksController();
        $controller->delete_item($this->parent, $this->category);
    }
    
    public function testFindItem() {
        $controller = new LinksController();
        $item = $controller->find_item($this->parent, $this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getCaption(), $this->caption);
    }
    
    public function testFindItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new LinksController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testFindItem_null_categotry() {
        $category = null;
        $controller = new LinksController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testGetItem() {
        $controller = new LinksController();
        $item = $controller->get_item($this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getCaption(), $this->caption);
    }
    
    public function testGetItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new LinksController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItem_null_categotry() {
        $category = null;
        $controller = new LinksController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItems() {
        $controller = new LinksController();
        $items = $controller->get_items($this->parent);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);
    }
    
    public function testGetItems_nonexistant_categotry() {
        $controller = new LinksController();
        $items = $controller->get_items("blah");
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);// LinksController has its own category
    }
    
    public function testGetItems_null_categotry() {
        $controller = new LinksController();
        $items = $controller->get_items(null);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testUpdateItem() {
        $controller = new LinksController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, $this->category, 
            $original_item->getCaption(),
            "http://www.ipsum.com",
            "Updated Title",
            "top"
        );
        $this->assertNotNull($updated_item);
        $this->assertEqual($updated_item->getCaption(), $original_item->getCaption());
        $this->assertEqual($updated_item->getHref(), "http://www.ipsum.com");
        $this->assertEqual($updated_item->getTitle(), "Updated Title");
        $this->assertEqual($updated_item->getTarget(), "top");
    }
    
    public function testUpdateItem_nonexistant_categotry() {
        $controller = new LinksController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, 
            "blah",
            "http://www.ipsum.com",
            "Updated Title",
            "top"
        );
        $this->assertNull($updated_item);
    }
    
    public function testUpdateItem_null_categotry() {
        $controller = new LinksController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, 
            null, 
            "http://www.ipsum.com",
            "Updated Title",
            "top"
        );
        $this->assertNull($updated_item);
    }
   
   public function testSaveItem() {
       $controller = new LinksController();
       $new_item = $controller->save_item($this->parent, 
            "New Link", 
            "http://www.ipsum.com",
            "New Title",
            "top"
        );
       
       $this->assertNotNull($new_item);
       $this->assertEqual($new_item->getCaption(), "New Link");
       $this->assertEqual($new_item->getHref(), "http://www.ipsum.com");
       $this->assertEqual($new_item->getTitle(), "New Title");
       $this->assertEqual($new_item->getTarget(), "top");
       
       $deleted_item = $controller->delete_item($new_item->getParent(), $new_item->getDir());
       $this->assertNotNull($deleted_item);
   }
    
    public function testSaveItem_duplicate() {
        $controller = new LinksController();
        $new_item1 = $controller->save_item($this->parent, 
            "New Link", 
            "http://www.ipsum.com",
            "New Title",
            "top"
        );
        $this->assertNotNull($new_item1);
        
        $new_item2 = $controller->save_item($this->parent, 
            "New Link", 
            "http://www.ipsum.com",
            "New Title",
            "top"
        );
        $this->assertNull($new_item2);
                
        $deleted_item1 = $controller->delete_item($new_item1->getParent(), $new_item1->getDir());
        $this->assertNotNull($deleted_item1);
    }
    
    public function testDeleteItem() {
        $controller = new LinksController();
        $new_item = $controller->save_item($this->parent, 
            "New Link", 
            "http://www.ipsum.com",
            "New Title",
            "top"
        );
        $this->assertNotNull($new_item);
        $deleted_item = $controller->delete_item($new_item->getParent(), $new_item->getDir());
        $this->assertNotNull($deleted_item);
    }
    
    public function testDeleteItem_nonexistant_item() {
        $controller = new LinksController();
        $deleted_item = $controller->delete_item($this->parent, "nonexistant_item");
        $this->assertNull($deleted_item);
    }
    
    public function testDeleteItem_null_item() {
        $controller = new LinksController();
        $deleted_item = $controller->delete_item($this->parent, null);
        $this->assertNull($deleted_item);
    }
}

?>
