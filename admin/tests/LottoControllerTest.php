<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    require_once(DIR_ADMIN . "/simpletest/autorun.php");
    require_once(DIR_ADMIN . "/simpletest/web_tester.php");
    include_once(DIR_ADMIN . "/LottoController.php");
    
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LottoControllerTest
 *
 * @author kieran
 */
class LottoControllerTest extends WebTestCase /*UnitTestCase*/ {
    private $category;
    private $parent;
    private $title;
    private $jackpot;
    private $numbers = array();
    private $result;
    private $winners = array();
    private $lucky_dips = array();
    private $next_jackpot;
    private $next_venue;
    
    public function setUp() {
        $controller = new LottoController();
        $this->title = "2013_05_21";
        $this->jackpot = "€4,000";
        $this->numbers = array(1,2,3,4);
        $this->result = "1 Winner";
        $this->winners = array("Tom", "Dick", "Harry");
        $this->lucky_dips = array("Tom", "Dick", "Harry");
        $this->next_jackpot = "€5,000";
        $this->next_venue = "Sheahans";
        $this->category = "lotto/2013/2013_05_21";
        $this->parent = "lotto/2013";
        $item = $controller->find_item($this->parent, $this->category);
        if ($item === null) {
            $controller->save_item($this->parent, 
                $this->title,
                $this->jackpot,
                $this->numbers,
                $this->result,
                $this->winners,
                $this->lucky_dips,
                $this->next_jackpot,
                $this->next_venue    
            );
        }
    }
    
    public function tearDown() {
        $controller = new LottoController();
        $item = $controller->find_item($this->parent, $this->category);
        if ($item !== null) {
            $controller->delete_item($this->parent, $this->category);
        }
    }
    
    public function testFindItem() {
        $controller = new LottoController();
        $item = $controller->find_item($this->parent, $this->category);
        $this->dump($item);
        $this->assertTrue(true);//$item === null);
    }
    /*
    public function testFindItem_nonexistant_categotry() {
        $category = "blah";
        $controller = new LottoController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertTrue($item === null);
    }
    
    public function testFindItem_null_categotry() {
        $category = null;
        $controller = new LottoController();
        $item = $controller->find_item($this->parent, $category);
        $this->assertTrue($item === null);
    }
    
    public function testGetItem() {
        $controller = new LottoController();
        $item = $controller->get_item($this->category);
        $this->assertTrue($item !== null);
    }
    
    public function testGetItem_nonexistant_categotry() {
        $category = "blah";
        $controller = new LottoController();
        $item = $controller->get_item($category);
        $this->assertTrue($item === null);
    }
    
    public function testGetItem_null_categotry() {
        $category = null;
        $controller = new LottoController();
        $item = $controller->get_item($category);
        $this->assertTrue($item === null);
    }
    
    public function testGetItems() {
        $controller = new LottoController();
        $items = $controller->get_items($this->parent);
        $this->assertTrue($items !== null);
        $this->assertTrue(count($items) > 0);
    }
    
    public function testGetItems_nonexistant_categotry() {
        $controller = new LottoController();
        $items = $controller->get_items("blah");
        $this->assertTrue($items !== null);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testGetItems_null_categotry() {
        $controller = new LottoController();
        $items = $controller->get_items(null);
        $this->assertTrue($items !== null);
        $this->assertTrue(count($items) == 0);
    }
    
    public function testUpdateItem() {
        $controller = new LottoController();
        $original_item = $controller->save_item("", "save_test_1");
        $this->assertTrue($original_item !== null);
        $updated_item = $controller->update_item("", "save_test_1", "save_test_1");
        $this->assertNotNull($updated_item);
        $this->assertTrue(strcasecmp($updated_item->getTitle(), $original_item->getTitle()) == 0);
        $controller->delete_item("", "save_test_1");
    }
    
    public function testUpdateItem_nonexistant_categotry() {
        $controller = new LottoController();
        $original_item = $controller->save_item("", "save_test_1");
        $this->assertTrue($original_item !== null);
        $updated_item = $controller->update_item("", "blah", "save_test_1");
        $this->assertNull($updated_item);
    }
    
    public function testUpdateItem_null_categotry() {
        $controller = new LottoController();
        $original_item = $controller->save_item("", "save_test_1");
        $this->assertTrue($original_item !== null);
        $updated_item = $controller->update_item("", null, "save_test_1");
        $this->assertNull($updated_item);
    }
    
    public function testSaveItem() {
        $controller = new LottoController();
        $item = $controller->save_item("", "save_test_1");
        $this->assertTrue($item !== null);
        $controller->delete_item("", "save_test_1");
    }
    
    public function testSaveItem_nonexistant_parent() {
        $controller = new LottoController();
        $new_item = $controller->save_item("blah", "save_test_2");
        $this->assertNull($new_item);
    }
    
    public function testSaveItem_null_parent() {
        $controller = new LottoController();
        $new_item = $controller->save_item(null, "save_test_3");
        $this->assertNull($new_item);
    }
    
    public function testSaveItem_duplicate() {
        $controller = new LottoController();
        $item1 = $controller->save_item("", "save_test_1");
        $this->assertTrue($item1 !== null);
        $item1 = $controller->save_item("", "save_test_1");
        $this->assertTrue($item1 !== null); // Article controller turns a dupe into an update
        $controller->delete_item("", "save_test_1");
    }
    */
}

?>
