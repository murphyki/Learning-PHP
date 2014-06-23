<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    require_once(DIR_ADMIN . "/simpletest/autorun.php");
    include_once(DIR_ADMIN . "/DownloadsController.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DownloadsControllerTest
 *
 * @author kieran
 */
class DownloadsControllerTest extends UnitTestCase {
    private $parent;
    private $category;
    private $file;
            
    public function setUp() {
        $this->file = "gaa.jpg";
        $this->parent = "downloads/";
        $this->category = $this->parent . $this->file;
    }
    
    public function tearDown() {
        
    }
    
    public function testFindItem() {
        $controller = new DownloadsController();
        $item = $controller->find_item($this->parent, $this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getFile(), $this->file);
    }
    
    public function testFindItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new DownloadsController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testFindItem_null_categotry() {
        $category = null;
        $controller = new DownloadsController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testGetItem() {
        $controller = new DownloadsController();
        $item = $controller->get_item($this->category);
        $this->assertNotNull($item);
    }
    
    public function testGetItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new DownloadsController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItem_null_categotry() {
        $category = null;
        $controller = new DownloadsController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItems() {
        $controller = new DownloadsController();
        $items = $controller->get_items($this->parent);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);
    }
    
    public function testGetItems_nonexistant_categotry() {
        $controller = new DownloadsController();
        $items = $controller->get_items("blah");
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);// DownloadsController has its own category
    }
    
    public function testGetItems_null_categotry() {
        $controller = new DownloadsController();
        $items = $controller->get_items(null);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testUpdateItem() {
        $controller = new DownloadsController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, $this->category, $original_item->getFile(), true);
        $this->assertNotNull($updated_item);
        $this->assertTrue(strcasecmp($updated_item->getFile(), $original_item->getFile()) == 0);
        $this->assertTrue($updated_item->getViewable());
    }
    
    public function testUpdateItem_nonexistant_categotry() {
        $controller = new DownloadsController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, "blah", $original_item->getFile(), true);
        $this->assertNull($updated_item);
    }
    
    public function testUpdateItem_null_categotry() {
        $controller = new DownloadsController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, null, $original_item->getName(), true);
        $this->assertNull($updated_item);
    }
    
//   public function testSaveItem() {
//       $controller = new DownloadsController();
//       $original_item = $controller->get_item($this->category);
//       $this->assertNotNull($original_item);
//       $new_item = $controller->save_item($this->parent, "/media/images/design/buttons.png", true);
//       $this->assertNotNull($new_item);
//       $controller->delete_item($new_item->getParent(), $new_item->getDir());
//    }
//    
//    public function testSaveItem_duplicate() {
//        $controller = new DownloadsController();
//        $new_item = $controller->save_item("", "test", false, false);
//        $this->assertNull($new_item);
//    }
    
//    public function testDeleteItem() {
//        $controller = new DownloadsController();
//        $new_item = $controller->save_item("test", "test_child_1", false, false);
//        $this->assertNotNull($new_item);
//        $deleted_item = $controller->delete_item($new_item->getParent(), $new_item->getDir());
//        $this->assertNotNull($deleted_item);
//    }
    
    public function testDeleteItem_nonexistant_item() {
        $controller = new DownloadsController();
        $deleted_item = $controller->delete_item($this->parent, "nonexistant_item");
        $this->assertNull($deleted_item);
    }
    
    public function testDeleteItem_null_item() {
        $controller = new DownloadsController();
        $deleted_item = $controller->delete_item($this->parent, null);
        $this->assertNull($deleted_item);
    }
}

?>
