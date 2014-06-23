<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_ADMIN . "/AbstractController.php");
    include_once(DIR_LIB . "/core/CategoryItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
class CategoryController extends AbstractController {
    
    public function __construct() {
    }
    
    public function perform_action($action, $category) {
        $parent = $this->get_parent_category($category);
        $redirect_url = "";
        $status_msg = "";
        
        if (strcasecmp($action, "update") == 0) {
            $item = $this->update_item($parent, $category,
                Utils::get_user_input("name"), 
                Utils::get_user_input("viewable") == "on" ? "yes" : "no",
                Utils::get_user_input("hassubcategory") == "on" ? "yes" : "no"
            );
            if ($item !== null) {
                $status_msg = "Successfully updated " . $item->getName() . ".";
                $redirect_url = ADMIN_URL . "/EditController.php?category=" . $item->getDir();
            } else {
                $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
            }
        } else if (strcasecmp($action, "save") == 0) {
            $item = $this->save_item($parent, 
                Utils::get_user_input("name"), 
                Utils::get_user_input("viewable") == "on" ? "yes" : "no",
                Utils::get_user_input("hassubcategory") == "on" ? "yes" : "no"
            );
            if ($item !== null) {
                $status_msg = "Successfully created " . $item->getName() . ".";
                $redirect_url = ADMIN_URL . "/EditController.php?category=" . $item->getDir();
            } else {
                $redirect_url = ADMIN_URL . "/index.php?category=" . $category;
            }
        } else if (strcasecmp($action, "delete") == 0) {
            $item = $this->delete_item($parent, $category);
            if ($item !== null) {
                $status_msg = "Successfully deleted " . $item->getName() . ".";
                $redirect_url = ADMIN_URL . "/index.php?category=" . $item->getParent();
            } else {
                $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
            }
        } else if (strcasecmp($action, "save_order") == 0) {
            $names = Utils::get_user_input_array("names");
            if ($this->save_order($category, $names)) {
                $status_msg = "Successfully saved ordered items.";
            }
            $redirect_url = ADMIN_URL . "/index.php?category=" . $category;
        } else {
            Logger::log_error("Unknown action requested", __FILE__, __LINE__);
            $redirect_url = ADMIN_URL . "/index.php?category=$parent";
        }
        
        ControllerService::set_status_context($status_msg, strlen($status_msg) > 0 ? "success" : "");
        
        return $redirect_url;
    }
    
    public function get_item($category) {
        return $this->find_item($this->get_parent_category($category), $category);
    }
    
    public function get_items($category) {
        $raw_items = $this->load_config_data($category);
        $items = array();
        foreach ($raw_items as $raw_item) {
            $item = $this->make_item($category, $raw_item["name"], $raw_item["viewable"], $raw_item["subcontent"]);
            $items[] = $item;
        }
        
        return $items;
    }
    
    public function get_viewable_items($category) {
        $items = $this->get_items($category);
        $viewable_items = array();
        
        foreach ($items as $item) {
            if ($item->getViewable()) {
                $viewable_items[] = $item;
            }
        }
       
        return $viewable_items;
    }
    
    public function save_item($parent) {
        if (func_num_args() != 4) {
            Logger::log_error("Error saving item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $parent_item = $this->get_item($parent);
        
        if (strlen($parent) > 0 && $parent_item === null) {
            Logger::log_error("Error saving item: failed to find item parent", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $name = $args[1]; 
        $viewable = $args[2]; 
        $subContent = $args[3];
        
        if (strlen($name) == 0) {
            Logger::log_error("Error saving item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        $new_item = $this->make_item($parent, $name, $viewable, $subContent);
        
        $item = $this->get_item($new_item->getDir());
        if ($item !== null) {
            Logger::log_error("Error saving item: item already exists", __FILE__, __LINE__);
            return null;
        }
        
        $items = $this->get_items($parent);
        array_unshift($items, $new_item);
        
        $this->setCreateDir(true);
        $ok = $this->save_config_data($parent, $items);
        $this->setCreateDir(false);
        if ($ok) {
            $updated_items = $this->get_items($parent);
            if ($updated_items === null || count($updated_items) <= 0) {
                Logger::log_error("Error saving item: items were not saved", __FILE__, __LINE__);
                return null;
            }
            
            $new_item = $updated_items[0];
            
            if ($new_item !== null && $new_item->getHasSubContent()) {
                $this->generate_toc($parent);
            }
            
            $this->update_touch_file($parent);
            
            return $new_item;
        } else {
            Logger::log_error("Error saving item: save failed", __FILE__, __LINE__);
            return null;
        }
        
        return null;
    }
    
    public function update_item($parent, $category) {
        if (func_num_args() != 5) {
            Logger::log_error("Error updating item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $name = $args[2]; 
        $viewable = $args[3];
        $subContent = $args[4];
        
        if (strlen($name) == 0) {
            Logger::log_error("Error updating item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        $original = $this->get_item($category);
        if ($original === null) {
            Logger::log_error("Error updating item: item to update does not exist", __FILE__, __LINE__);
            return null;
        }
        
        $items = $this->get_items($parent);
        foreach ($items as $item) {
            if (strcasecmp($original->getName(), $item->getName()) == 0) {
                $item->setName($name);
                $item->setViewable($viewable == "yes" ? true : false);
                $item->setHasSubContent($subContent == "yes" ? true : false);
                break;
            }
        }
        
        if ($this->save_config_data($parent, $items)) {
            // Rename the directory owned by the updated item.
            $updated_items = $this->get_items($parent);
            if ($updated_items === null || count($updated_items) <= 0) {
                Logger::log_error("Error updating item: items were not saved", __FILE__, __LINE__);
                return null;
            }
            
            $updated_item = null;
            foreach ($updated_items as $item) {
                if (strcasecmp($name, $item->getName()) == 0) {
                    $dir = DIR_CONTENT . DIRECTORY_SEPARATOR;
                    if ($this->do_rename($dir . $original->getDir(), $dir . $item->getDir())) {
                        $dir = DIR_BASE . DIRECTORY_SEPARATOR;
                        $this->do_rename($dir . $original->getDir(), $dir . $item->getDir());
                    }
                    
                    $updated_item = $item;
                }
            }
            
            if ($updated_item !== null && $updated_item->getHasSubContent()) {
                $this->generate_toc($category);
            }
            
            $this->update_touch_file($category);
            
            return $updated_item;
        } else {
            Logger::log_error("Error updating item: save failed", __FILE__, __LINE__);
            return null;
        }
        
        return null;
    }
    
    public function delete_item($parent, $category) {
        $original = $this->get_item($category);
        if ($original === null) {
            Logger::log_error("Error deleting item: item to delete does not exist", __FILE__, __LINE__);
            return null;
        }
        
        $items = $this->get_items($parent);
        for ($i = 0; $i < count($items); $i++) {
            $item = $items[$i];
            if (strcasecmp($original->getName(), $item->getName()) == 0) {
                array_splice($items, $i, 1);
                break;
            }
        }
        
        // Save the new list of items
        if ($this->save_config_data($parent, $items)) {
            // Remove any files/folders belong to the item that was deleted.
            // This looks odd, passing category as the parent but
            // it makes sense as it is this and everything below category
            // we want to delete.
            $this->do_delete_item($category, $category);
        } else {
            Logger::log_error("Error deleting item: save failed", __FILE__, __LINE__);
            return null;
        }
        
        return $original;
    }
    
    public function find_item($parent, $category) {
        $items = $this->get_items($parent);
        foreach ($items as $item) {
            if (strcasecmp($item->getDir(), $category) == 0) {
                return $item;
            } 
            
            if ($item->getHasSubContent()) {
                $it = $this->find_item($item->getDir(), $category);
                if ($it !== null) {
                    return $it;
                }
            }
        }
        
        return null;
    }
    
    public function get_edit_template() {
        return "fragments/edit_category.twig";
    }
    
    public function get_new_template() {
        return "fragments/create_category.twig";
    }
    
    private function do_delete_item($parent, $category) {
        $items = $this->get_items($parent);
        foreach ($items as $item) {
            if ($item->getHasSubContent()) {
                $this->do_delete_item($item->getDir(), $category);
            } else {
                self::do_clean($this->get_valid_path(DIR_BASE . DIRECTORY_SEPARATOR . $item->getDir(), DIR_BASE)); 
                self::do_clean($this->get_valid_path(DIR_CONTENT . DIRECTORY_SEPARATOR . $item->getDir()));
            }
        }
        
        self::do_clean($this->get_valid_path(DIR_BASE . DIRECTORY_SEPARATOR . $category, DIR_BASE));
        self::do_clean($this->get_valid_path(DIR_CONTENT . DIRECTORY_SEPARATOR . $category));
    }
    
    protected function load_config_data($category) {
        $items = array();
        $file = $this->get_config_file($category);
        if ($file !== null && file_exists($file)) {
            $sxml = simplexml_load_file($file);
            foreach ($sxml->categories->children() as $item) {
                $items[] = array(
                    "name"=>(string)$item->name, 
                    "viewable"=>(string)$item->viewable,
                    "subcontent"=>(string)$item->subcontent);
            }
        } else {
            // scan the directory and see if there are some folders there...
            $root = $this->get_config_dir($category);
            if ($file !== null && file_exists($root)) {
                $dh  = opendir($root);
                while (false !== ($dir = readdir($dh))) {
                    if ($dir !== '.' && $dir !== '..' && is_dir($root . DIRECTORY_SEPARATOR . $dir)) {
                        $items[] = array(
                            "name"=>$dir, 
                            "viewable"=>"no",
                            "subcontent"=>"no");
                    }
                }

                closedir($dh);
            }
        }
        
        return $items;
    }
    
    protected function save_config_data($parent, array $items) {
        $dom = new DOMDocument();
        $categorieElements = $dom->createElement('categories');
        foreach($items as $item) {
            $name = $dom->createElement('name');
            $name->appendChild($dom->createTextNode($item->getName()));
            $viewable = $dom->createElement('viewable');
            $viewable->appendChild($dom->createTextNode($item->getViewable() ? "yes" : "no"));
            $subContent = $dom->createElement('subcontent');
            $subContent->appendChild($dom->createTextNode($item->getHasSubContent() ? "yes" : "no"));
            $categoryElement = $dom->createElement('category');
            $categoryElement->appendChild($name);
            $categoryElement->appendChild($viewable);
            $categoryElement->appendChild($subContent);
            $categorieElements->appendChild($categoryElement);
        }
        
        $root = $dom->createElement('site_config');
        $root->appendChild($categorieElements);
        
        $dom->appendChild($root);
        $buffer = $dom->saveXml();
        
        $file = $this->get_config_file($parent);
        if ($file !== null && file_put_contents($file, $buffer)) {
            chmod($file, 0640);
        } else {
            Logger::log_error("Problem saving configuration data", __FILE__, __LINE__);
            return false;
        }
        
        return true;
    }
    
    private function make_item($parent, $name, $viewable, $subContent) {
        $item = new CategoryItem($parent);
        $item->setName($name);
        if (strlen($parent) == 0 || strcasecmp($parent, "root") == 0) {
            $item->setDir(Utils::sanitize_name(strtolower($name)));
        } else {
            $item->setDir($parent . "/". Utils::sanitize_name(strtolower($name)));
        }

        $item->setViewable($viewable == "yes" ? true : false);
        $item->setHasSubContent($subContent == "yes" ? true : false);

        $url = ADMIN_URL;

        if ($item->getHasSubContent()) {
            $url .= "/index.php?category=" . $item->getDir();
        } else {
            $opts = AdminConfig::get_admin_config_options($item->getDir(), $item->getName());
            $url .= $opts["admin_url"];
        }

        $item->setAdminUrl($url);
        
        return $item;
    }
}
?>