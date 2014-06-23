<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    require_once(DIR_ADMIN . "/simpletest/autorun.php");
    include_once(DIR_ADMIN . "/CategoryController.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CategoryControllerTest
 *
 * @author kieran
 */
class CategoryControllerTest extends UnitTestCase {
    private $category;
    private $parent;
    
    public function setUp() {
        $controller = new CategoryController();
        $this->category = "test";
        $this->parent = $controller->get_parent_category($this->category);
        $item = $controller->find_item($this->parent, $this->category);
        if ($item === null) {
            $controller->save_item($this->parent, $this->category, false, false);
        }
    }
    
    public function tearDown() {
        $controller = new CategoryController();
        $item = $controller->find_item($this->parent, $this->category);
        if ($item !== null) {
            $controller->delete_item($this->parent, $this->category);
        }
    }
    
    public function testFindItem() {
        $controller = new CategoryController();
        $item = $controller->find_item($this->parent, $this->category);
        $this->assertNotNull($item);
        $this->assertEqual($item->getName(), "test");
    }
    
    public function testFindItem_nonexistant_categotry() {
        $category = "blah";
        $controller = new CategoryController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testFindItem_null_categotry() {
        $category = null;
        $controller = new CategoryController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertNull($item);
    }
    
    public function testGetItem() {
        $controller = new CategoryController();
        $item = $controller->get_item($this->category);
        $this->assertNotNull($item);
    }
    
    public function testGetItem_nonexistant_categotry() {
        $category = "test/../../../";
        $controller = new CategoryController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItem_null_categotry() {
        $category = null;
        $controller = new CategoryController();
        $item = $controller->get_item($category);
        $this->assertNull($item);
    }
    
    public function testGetItems() {
        $controller = new CategoryController();
        $items = $controller->get_items($this->parent);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) > 0);
    }
    
    public function testGetItems_nonexistant_categotry() {
        $controller = new CategoryController();
        $items = $controller->get_items("blah");
        $this->assertNotNull($items);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testGetItems_null_categotry() {
        $controller = new CategoryController();
        $items = $controller->get_items(null);
        $this->assertNotNull($items);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testUpdateItem() {
        $controller = new CategoryController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, $this->category, $original_item->getName(), true, true);
        $this->assertNotNull($updated_item);
        $this->assertTrue(strcasecmp($updated_item->getName(), $original_item->getName()) == 0);
        $this->assertTrue($updated_item->getViewable());
        $this->assertTrue($updated_item->getHasSubContent());
    }
    
    public function testUpdateItem_nonexistant_categotry() {
        $controller = new CategoryController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, "blah", $original_item->getName(), true, true);
        $this->assertNull($updated_item);
    }
    
    public function testUpdateItem_null_categotry() {
        $controller = new CategoryController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($this->parent, null, $original_item->getName(), true, true);
        $this->assertNull($updated_item);
    }
    
    public function testSaveItem() {
        $controller = new CategoryController();
        $original_item = $controller->get_item($this->category);
        $this->assertNotNull($original_item);
        $updated_item = $controller->update_item($original_item->getParent(), $original_item->getDir(), $original_item->getName(), true, true);
        $this->assertNotNull($updated_item);
        $new_item = $controller->save_item("test", "test_child_1", false, false);
        $this->assertNotNull($new_item);
        $controller->delete_item($new_item->getParent(), $new_item->getDir());
    }
    
    public function testSaveItem_nonexistant_parent() {
        $controller = new CategoryController();
        $new_item = $controller->save_item("blah", "test_child_1", false, false);
        $this->assertNull($new_item);
    }
    
    public function testSaveItem_null_parent() {
        $controller = new CategoryController();
        $new_item = $controller->save_item(null, "test_child_1", false, false);
        $this->assertNull($new_item);
    }
    
    public function testSaveItem_duplicate() {
        $controller = new CategoryController();
        $new_item = $controller->save_item("", "test", false, false);
        $this->assertNull($new_item);
    }
    
    public function testDeleteItem() {
        $controller = new CategoryController();
        $new_item = $controller->save_item("test", "test_child_1", false, false);
        $this->assertNotNull($new_item);
        $deleted_item = $controller->delete_item($new_item->getParent(), $new_item->getDir());
        $this->assertNotNull($deleted_item);
    }
    
    public function testDeleteItem_nonexistant_parent() {
        $controller = new CategoryController();
        $deleted_item = $controller->delete_item("blah", "test_child_1");
        $this->assertNull($deleted_item);
    }
    
    public function testDeleteItem_null_parent() {
        $controller = new CategoryController();
        $deleted_item = $controller->delete_item(null, "test_child_1");
        $this->assertNull($deleted_item);
    }
    
    public function testDeleteItem_nonexistant_item() {
        $controller = new CategoryController();
        $deleted_item = $controller->delete_item("test", "test_child_1");
        $this->assertNull($deleted_item);
    }
    
    public function testDeleteItem_null_item() {
        $controller = new CategoryController();
        $deleted_item = $controller->delete_item("test", null);
        $this->assertNull($deleted_item);
    }
    
}

?>
