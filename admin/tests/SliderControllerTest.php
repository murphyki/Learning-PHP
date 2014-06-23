<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    require_once(DIR_ADMIN . "/simpletest/autorun.php");
    include_once(DIR_ADMIN . "/SliderController.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SliderControllerTest
 *
 * @author kieran
 */
class SliderControllerTest extends UnitTestCase {
    private $parent;
    private $category;
    private $image;
    private $source;
    private $description;
    private $link;
            
    public function setUp() {
        $this->image = "header.png";
        $this->source = "/media/images/design/" . $this->image;
        $this->description = "test slider image";
        $this->link = "http://www.example.com";
        $this->parent = "slider/";
        $this->category = $this->parent . $this->image;
        $controller = new SliderController();
        $controller->save_item($this->parent, 
            $this->source,
            $this->description,
            $this->link
        );
    }
    
    public function tearDown() {
        $controller = new SliderController();
        $controller->delete_item($this->parent, $this->category);
    }
    
    public function testFindItem() {
        $controller = new SliderController();
        $item = $controller->find_item($this->parent, $this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getImage(), $this->image);
    }
    
    public function testFindItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new SliderController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testFindItem_null_categotry() {
        $category = null;
        $controller = new SliderController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testGetItem() {
        $controller = new SliderController();
        $item = $controller->get_item($this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getImage(), $this->image);
    }
    
    public function testGetItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new SliderController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItem_null_categotry() {
        $category = null;
        $controller = new SliderController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItems() {
        $controller = new SliderController();
        $items = $controller->get_items($this->parent);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);
    }
    
    public function testGetItems_nonexistant_categotry() {
        $controller = new SliderController();
        $items = $controller->get_items("blah");
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);// SliderController has its own category
    }
    
    public function testGetItems_null_categotry() {
        $controller = new SliderController();
        $items = $controller->get_items(null);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testUpdateItem() {
        $controller = new SliderController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, $this->category, 
            $original_item->getSource(),
            "Updated Description",
            "http://www.ipsum.com"
        );
        $this->assertNotNull($updated_item);
        $this->assertEqual($updated_item->getImage(), $original_item->getImage());
        $this->assertEqual($updated_item->getSource(), $original_item->getSource());
        $this->assertEqual($updated_item->getDescription(), "Updated Description");
        $this->assertEqual($updated_item->getLink(),"http://www.ipsum.com");
    }
    
    public function testUpdateItem_nonexistant_categotry() {
        $controller = new SliderController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, 
            "blah", 
            $original_item->getSource(),
            "Updated Description",
            "http://www.ipsum.com"
        );
        $this->assertNull($updated_item);
    }
    
    public function testUpdateItem_null_categotry() {
        $controller = new SliderController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, 
            null, 
            $original_item->getSource(),
            "Updated Description",
            "http://www.ipsum.com"
        );
        $this->assertNull($updated_item);
    }
   
    public function testSaveItem() {
        $controller = new SliderController();
        $new_item = $controller->save_item($this->parent, 
            "/media/images/design/buttons.png", 
            "buttons.png image description",
            "http://www.ipsum.com"
        );
       
        $this->assertNotNull($new_item);
        $this->assertEqual($new_item->getImage(), "buttons.png");
        $this->assertEqual($new_item->getSource(), "/media/images/design/buttons.png");
        $this->assertEqual($new_item->getDescription(), "buttons.png image description");
        $this->assertEqual($new_item->getLink(), "http://www.ipsum.com");
        
        $deleted_item = $controller->delete_item($new_item->getParent(), $new_item->getDir());
        $this->assertNotNull($deleted_item);
    }
   
    public function testSaveItem_image_does_not_exist() {
        $controller = new SliderController();
        $new_item = $controller->save_item($this->parent, 
            "/media/images/design/fake.png", 
            "fake.png image description",
            "http://www.ipsum.com"
        );
        $this->assertNull($new_item);
    }
    
    public function testSaveItem_duplicate() {
        $controller = new SliderController();
        $new_item1 = $controller->save_item($this->parent, 
            "/media/images/design/buttons.png", 
            "buttons.png image description",
            "http://www.ipsum.com"
        );
        $this->assertNotNull($new_item1);
        
        $new_item2 = $controller->save_item($this->parent, 
            "/media/images/design/buttons.png", 
            "buttons.png image description",
            "http://www.ipsum.com"
        );
        $this->assertNotNull($new_item2);
        $this->assertEqual($new_item1, $new_item2);
                
        $deleted_item1 = $controller->delete_item($new_item1->getParent(), $new_item1->getDir());
        $this->assertNotNull($deleted_item1);
        
        // SliderController is allowing duplicate items but when deleting
        // it deletes all items with the same caption...
        // Do we want to allow this behaviour???
        // 
        //$deleted_item2 = $controller->delete_item($new_item2->getParent(), $new_item2->getDir());
        //$this->assertNotNull($deleted_item2);
    }
  
    public function testDeleteItem() {
        $controller = new SliderController();
        $new_item = $controller->save_item($this->parent, 
            "/media/images/design/buttons.png", 
            "buttons.png image description",
            "http://www.ipsum.com"
        );
        $this->assertNotNull($new_item);
        $deleted_item = $controller->delete_item($new_item->getParent(), $new_item->getDir());
        $this->assertNotNull($deleted_item);
    }
    
    public function testDeleteItem_nonexistant_item() {
        $controller = new SliderController();
        $deleted_item = $controller->delete_item($this->parent, "nonexistant_item");
        $this->assertNull($deleted_item);
    }
    
    public function testDeleteItem_null_item() {
        $controller = new SliderController();
        $deleted_item = $controller->delete_item($this->parent, null);
        $this->assertNull($deleted_item);
    }
}

?>
