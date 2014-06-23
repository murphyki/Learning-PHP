<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_ADMIN . "/AbstractController.php");
    include_once(DIR_LIB . "/core/GalleryItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
class GalleryController extends AbstractController {
    
    public function __construct() {
        
    }
    
    public function perform_action($action, $category) {
        $parent = $this->get_parent_category($category);
        $redirect_url = "";
        $status_msg = "";
        
        if (strcasecmp($action, "update") == 0) {
            $item = $this->update_item($parent, $category,
                Utils::get_user_input("name"), 
                Utils::get_user_input("viewable") == "on" ? "yes" : "no"
            );
            if ($item !== null) {
                $status_msg = "Successfully updated " . $item->getName() . ".";
                $redirect_url = ADMIN_URL . "/EditController.php?category=" . $item->getDir();
            } else {
                $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
            }
        } else if (strcasecmp($action, "save") == 0) {
            // When saving a category we are already in the parent level...
            // so use category instead of parent
            $item = $this->save_item($category, 
                Utils::get_user_input("name"),
                $_FILES['gallery_file']['tmp_name'], 
                Utils::get_user_input("viewable") == "on" ? "yes" : "no"
            );
            if ($item !== null) {
                $status_msg = "Successfully saved " . $item->getName() . ".";
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
        $config_data = $this->load_config_data($category);
        $items = array();
        foreach ($config_data as $cd) {
            $item = $this->make_item($category, $cd['name'], $cd['viewable']);
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
        
        $args = func_get_args();
        $name = $args[1];
        $file = $args[2];
        $viewable = $args[3];
                
        if (strlen($name) == 0) {
            Logger::log_error("Error saving item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        $new_item = $this->make_item($parent, $name, $viewable);
        $items = $this->get_items($parent);
        array_unshift($items, $new_item);
        
        $this->setCreateDir(true);
        $ok = $this->save_config_data($parent, $items);
        $this->setCreateDir(false);
        
        if ($ok) {
            if (is_uploaded_file($file)) {
                if(!Utils::ends_with($_FILES['gallery_file']['name'], ".zip")) {
                    Logger::log_error('The file you tried to upload is invalid.', __FILE__, __LINE__);
                    return false;
                }
                
                $dir = Utils::make_dir($this->get_valid_path(DIR_GALLERY . "/{$name}", DIR_GALLERY));
                
                $zip = new ZipArchive();
                if ($zip->open($file) === TRUE) {
                    $zip->extractTo($dir);
                    $zip->close();
                } else {
                    Logger::log_error("There was an error unzipping the file", __FILE__, __LINE__);
                    return null;
                }
            }
            
            Utils::make_dir($this->get_valid_path(DIR_CONTENT . "/gallerys/{$name}"));
        
            $updated_items = $this->get_items($parent);
            if ($updated_items === null || count($updated_items) <= 0) {
                Logger::log_error("Error saving item: items were not saved", __FILE__, __LINE__);
                return null;
            }

            return $updated_items[0];
        } else {
            Logger::log_error("Error saving item: save failed", __FILE__, __LINE__);
            return null;
        }
        
        return null;
    }
    
    public function update_item($parent, $category) {
        if (func_num_args() != 4) {
            Logger::log_error("Error updating item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $name = $args[2];
        $viewable = $args[3];
        
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
                $item->setViewable($viewable == "yes" ? true : false);
                break;
            }
        }
        
        if ($this->save_config_data($parent, $items)) {
            // Rename the directory owned by the updated item.
            $updated_items = $this->get_items($parent);
            foreach ($updated_items as $item) {
                if (strcasecmp($original->getName(), $item->getName()) == 0) {
                    return $item;
                }
            }
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
        if (!$this->save_config_data($parent, $items)) {
            Logger::log_error("Error deleting item: save failed", __FILE__, __LINE__);
            return null;
        }
        
        // Delete the gallery and its contents
        self::do_clean($this->get_valid_path(DIR_GALLERY . DIRECTORY_SEPARATOR . $original->getName(), DIR_GALLERY));
        self::do_clean($this->get_valid_path(DIR_CONTENT . DIRECTORY_SEPARATOR . "gallerys" . DIRECTORY_SEPARATOR . $original->getName()));
        
        return $original;
    }
    
    public function find_item($parent, $category) {
        $items = $this->get_items($parent);
        foreach ($items as $item) {
            if (strcasecmp($item->getDir(), $category) == 0) {
                return $item;
            } 
        }
        
        return null;
    }
    
    public function get_edit_template() {
        return "fragments/edit_gallery.twig";
    }
    
    public function get_new_template() {
        return "fragments/create_gallery.twig";
    }
   
    public function get_parent_category($category) {
        return GALLERY_ALIAS . "/";
    }
    
    public function get_back_category($category, $context) {
        if (strcasecmp(AbstractController::CONTEXT_LIST, $context) == 0) {
            return "";
        }
        
        return $this->get_parent_category($category);
    }
    
    public function contribute_to_comman_tasks($category) {
        $alias = GALLERY_ALIAS;
        $items = array(
            array("name"=>"Create New Gallery", "url"=>ADMIN_URL . "/CreateController.php?category={$alias}/new")
        );
        
        return $items;
    }
    
    public function get_modified_time() {
        $file = $this->get_config_file(GALLERY_ALIAS);
        if (file_exists($file)) {
            return filemtime($file);
        } else {
            return 0;
        }
    }
    
    public function is_modified($token) {
        $modified_time = $this->get_modified_time();
        
        if ($modified_time > $token) {
            return("true");
        } else {
            return("false");
        }
    }
    
    public function is_valid_gallery($gallery, $check_viewable = true) {
        if ($gallery === null)
            return false;
        
        if (strlen($gallery) == 0)
            return false;
        
        if (strcasecmp($gallery, "null") == 0)
            return false;
        
        if (strcasecmp($gallery, "images") == 0)
            return false;
        
        $items = $this->get_items(GALLERY_ALIAS . "/");
        $galleryItem = null;
        foreach($items as $item) {
            if (strcasecmp($item->getName(), $gallery) == 0) {
                $galleryItem = $item;
                break;
            }
        }
        
        if ($galleryItem === null) {
            return false;
        }
            
        if ($check_viewable) {
            return $galleryItem->getViewable();
        }
        
        return true;
    }
    
    public function get_gallery_from_url($url) {
        $tokens = preg_split("/\//", $url);
        if (count($tokens) >= 2) {
            $gallery = $tokens[count($tokens) - 2];
        
            if ($this->is_valid_gallery($gallery)) {
                return $gallery;
            }
        }
        
        return null;
    }
   
    protected function get_config_file($category) {
        $dir = $this->get_config_dir(GALLERY_ALIAS . "/");
        return $dir . DIRECTORY_SEPARATOR . "gallery.xml";
    }
    
    protected function load_config_data($category) {
        if ($category === null || strlen($category) == 0) {
            Logger::log_error("Error loading gallery configuration data: invalid argument list", __FILE__, __LINE__);
            return array();
        }
        
        $dirs = array();
        $data = array();
        $dir = $this->get_valid_path(DIR_GALLERY, DIR_GALLERY);
        if (file_exists($dir)) {
            $dh  = opendir($dir);
            while (false !== ($filename = readdir($dh))) {
                if ($filename !== '.' && $filename !== '..' && is_dir($dir . DIRECTORY_SEPARATOR . $filename)) {
                    $dirs[$filename] = $filename;
                }
            }
            
            closedir($dh);
            ksort($dirs);
        }
        
        // Load the gallery configdata - if the configdata file exists
        $file = $this->get_config_file($category);
        if (file_exists($file)) {
            $sxml = simplexml_load_file($file);
            $galleries = $sxml->galleries->children();
            foreach ($galleries as $gallery) {
                // Only load the configdata if the directory actually exists
                $name = (string)$gallery->name;
                if (array_key_exists($name, $dirs)) {
                    unset($dirs[$name]);
                    $data[] = array(
                        "name"=>$name, // name
                        "viewable"=>(string)$gallery->viewable // viewable
                    );
                }
            }
        }
        
        // If no configdata or stragglers not in the configdata
        // deal with them here
        foreach ($dirs as $d) {
            $data[] = array(
                "name"=>$d, // name
                "viewable"=>"no" // viewable
            );
        }
        
        return $data;
    }
    
    protected function save_config_data($parent, array $items) {
        if ($parent === null || strlen($parent) == 0) {
            Logger::log_error("Error saving gallery configuration data: invalid argument list", __FILE__, __LINE__);
            return false;
        }
        
        $dom = new DOMDocument();
        
        $gals = $dom->createElement('galleries');
        foreach($items as $item) {
            $name = $item->getName();
            $viewable = $item->getViewable() == true ? "yes" : "no";
            
            $g = $dom->createElement('gallery');
            
            $name_element = $dom->createElement('name');
            $name_element->appendChild($dom->createTextNode($name));
            $g->appendChild($name_element);
            
            $viewable_element = $dom->createElement('viewable');
            $viewable_element->appendChild($dom->createTextNode($viewable));
            $g->appendChild($viewable_element);
            
            $gals->appendChild($g);
        }
        
        $root = $dom->createElement('gallery_config');
        $root->appendChild($gals);
        
        $dom->appendChild($root);
        $buffer = $dom->saveXml();
        
        $file = $this->get_config_file($parent);
        if (file_put_contents($file, $buffer)) {
            chmod($file, 0640);
        } else {
            Logger::log_error("Problem saving gallery configuration data", __FILE__, __LINE__);
            return false;
        }
        
        return true;
    }
    
    private function make_item($parent, $name, $viewable) {
        $item = new GalleryItem(GALLERY_ALIAS . "/");
        $item->setName($name);
        $item->setViewable($viewable == "yes" ? true : false);
        
        $url = ADMIN_URL . "/index.php?category=gallerys/{$name}";
        $item->setAdminUrl($url);
        
        return $item;
    }

}
?>