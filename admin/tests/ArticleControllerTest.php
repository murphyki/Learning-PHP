<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    require_once(DIR_ADMIN . "/simpletest/autorun.php");
    //require_once(DIR_ADMIN . "/simpletest/web_tester.php");
    include_once(DIR_ADMIN . "/ArticleController.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ArticleControllerTest
 *
 * @author kieran
 */
class ArticleControllerTest extends UnitTestCase /*WebTestCase*/ {
    private $category;
    private $parent;
    
    public function setUp() {
        $controller = new ArticleController();
        $this->category = "kieran";
        $this->parent = $controller->get_parent_category($this->category);
        $item = $controller->find_item($this->parent, $this->category);
        if ($item === null) {
            $controller->save_item($this->parent, $this->category);
        }
        
        //$this->dump($this->get("http://localhost:8080/admin/article/editor.php?category=kieran&article_title="));
    }
    
    public function tearDown() {
        $controller = new ArticleController();
        $item = $controller->find_item($this->parent, $this->category);
        if ($item !== null) {
            $controller->delete_item($this->parent, $this->category);
        }
    }
    
    public function testFindItem() {
        $controller = new ArticleController();
        $item = $controller->find_item($this->parent, $this->category);
        $this->assertTrue($item !== null);
    }
    
    public function testFindItem_nonexistant_categotry() {
        $category = "blah";
        $controller = new ArticleController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertTrue($item === null);
    }
    
    public function testFindItem_null_categotry() {
        $category = null;
        $controller = new ArticleController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertTrue($item === null);
    }
    
    public function testGetItem() {
        $controller = new ArticleController();
        $item = $controller->get_item($this->category);
        $this->assertTrue($item !== null);
    }
    
    public function testGetItem_nonexistant_categotry() {
        $category = "blah";
        $controller = new ArticleController();
        $item = $controller->get_item($category);
        $this->assertTrue($item === null);
    }
    
    public function testGetItem_null_categotry() {
        $category = null;
        $controller = new ArticleController();
        $item = $controller->get_item($category);
        $this->assertTrue($item === null);
    }
    
    public function testGetItems() {
        $controller = new ArticleController();
        $items = $controller->get_items($this->parent);
        $this->assertTrue($items !== null);
        $this->assertTrue(count($items) > 0);
    }
    
    public function testGetItems_nonexistant_categotry() {
        $controller = new ArticleController();
        $items = $controller->get_items("blah");
        $this->assertTrue($items !== null);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testGetItems_null_categotry() {
        $controller = new ArticleController();
        $items = $controller->get_items(null);
        $this->assertTrue($items !== null);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testUpdateItem() {
        $controller = new ArticleController();
        $original_item = $controller->save_item("", "save_test_1");
        $this->assertTrue($original_item !== null);
        $updated_item = $controller->update_item("", "save_test_1", "save_test_1");
        $this->assertNotNull($updated_item);
        $this->assertTrue(strcasecmp($updated_item->getTitle(), $original_item->getTitle()) == 0);
        $controller->delete_item("", "save_test_1");
    }
    
    public function testUpdateItem_nonexistant_categotry() {
        $controller = new ArticleController();
        $original_item = $controller->save_item("", "save_test_1");
        $this->assertTrue($original_item !== null);
        $updated_item = $controller->update_item("", "blah", "save_test_1");
        $this->assertNull($updated_item);
    }
    
    public function testUpdateItem_null_categotry() {
        $controller = new ArticleController();
        $original_item = $controller->save_item("", "save_test_1");
        $this->assertTrue($original_item !== null);
        $updated_item = $controller->update_item("", null, "save_test_1");
        $this->assertNull($updated_item);
    }
    
    public function testSaveItem() {
        $controller = new ArticleController();
        $item = $controller->save_item("", "save_test_1");
        $this->assertTrue($item !== null);
        $controller->delete_item("", "save_test_1");
    }
    
    public function testSaveItem_nonexistant_parent() {
        $controller = new ArticleController();
        $new_item = $controller->save_item("blah", "save_test_2");
        $this->assertNull($new_item);
    }
    
    public function testSaveItem_null_parent() {
        $controller = new ArticleController();
        $new_item = $controller->save_item(null, "save_test_3");
        $this->assertNull($new_item);
    }
    
    public function testSaveItem_duplicate() {
        $controller = new ArticleController();
        $item1 = $controller->save_item("", "save_test_1");
        $this->assertTrue($item1 !== null);
        $item1 = $controller->save_item("", "save_test_1");
        $this->assertTrue($item1 !== null); // Article controller turns a dupe into an update
        $controller->delete_item("", "save_test_1");
    }
}

?>
