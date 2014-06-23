<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_ADMIN . "/AbstractController.php");
    include_once(DIR_LIB . "/core/DownloadItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
class DownloadsController extends AbstractController {
    
    public function __construct() {
        
    }
    
    public function perform_action($action, $category) {
        $parent = $this->get_parent_category($category);
        $redirect_url = "";
        $status_msg = "";
        
        if (strcasecmp($action, "update") == 0) {
            $item = $this->update_item($parent, $category,
                Utils::get_user_input("file"), 
                Utils::get_user_input("viewable") == "on" ? "yes" : "no"
            );
            if ($item !== null) {
                $status_msg = "Successfully updated " . $item->getName() . ".";
                $redirect_url = ADMIN_URL . "/EditController.php?category=" . $item->getDir();
            } else {
                $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
            }
        } else if (strcasecmp($action, "save") == 0) {
            $item = $this->save_item($parent, 
                $_FILES['file']['name'], 
                Utils::get_user_input("viewable") == "on" ? "yes" : "no"
            );
            if ($item !== null) {
                $status_msg = "Successfully uploaded " . $item->getName() . ".";
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
        if (func_num_args() != 3) {
            Logger::log_error("Error saving item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $file = rawurldecode($args[1]); 
        $viewable = $args[2];
                
        if (strlen($file) == 0) {
            Logger::log_error("Error saving item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        // Check have we already uploaded a file with the same name as this one
        $target_file = $this->get_valid_path(DIR_DOWNLOADS_FILES . "/{$file}", DIR_DOWNLOADS_FILES);
        if (file_exists($target_file)) {
            Logger::log_error("Error saving item: item already exists", __FILE__, __LINE__);
            return null;
        }
        
        // Upload the file
        $uploaded_file = $_FILES['file']['tmp_name'];
        if(!move_uploaded_file($uploaded_file, $target_file)) {
            Logger::log_error("Error saving item: the upload failed", __FILE__, __LINE__);
            return null;
        }

        if (!file_exists($target_file)) {
            Logger::log_error('Error saving item: the file was not uploaded', __FILE__, __LINE__);
            return null;
        }
        
        $items = $this->get_items($parent);
        $new_item = $this->make_item($parent, $file, $viewable);
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
        $file = $args[2]; 
        $viewable = $args[3];
        
        if (strlen($file) == 0) {
            Logger::log_error("Error updating item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        $original = $this->get_item($category);
        
        if ($original === null) {
            Logger::log_error("Error updating item: item to update does not exist", __FILE__, __LINE__);
            return null;
        }
        
        if (strcasecmp($original->getFile(), $file) != 0) {
            $orig = DIR_DOWNLOADS_FILES . DIRECTORY_SEPARATOR . $original->getFile();
            $new = DIR_DOWNLOADS_FILES . DIRECTORY_SEPARATOR . $file;
            if (!(file_exists($orig) && is_file($orig) && !file_exists($new) && rename($orig, $new))) {
                Logger::log_error("Error updating item: rename failed", __FILE__, __LINE__);
                return null;
            }
        }
        
        $items = $this->get_items($parent);
        foreach ($items as $item) {
            if (strcasecmp($original->getFile(), $item->getFile()) == 0) {
                $item->setFile($file);
                $item->setViewable($viewable == "yes" ? true : false);
                break;
            }
        }
        
        if ($this->save_config_data($parent, $items)) {
            // Return the updated item
            $updated_items = $this->get_items($parent);
            foreach ($updated_items as $item) {
                if (strcasecmp($file, $item->getFile()) == 0) {
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
        if ($this->save_config_data($parent, $items)) {
            // Remove the file associated with this item.
            $file = $this->get_valid_path(DIR_DOWNLOADS_FILES . DIRECTORY_SEPARATOR . $original->getFile(), DIR_DOWNLOADS_FILES);
            if (file_exists($file) && is_file($file)) {
                if (!unlink($file)) {
                    Logger::log_error("Warning: failed to remove file {$file}", __FILE__, __LINE__);
                }
            }
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
        }
        
        return null;
    }
    
    public function get_edit_template() {
        return "fragments/edit_download.twig";
    }
    
    public function get_new_template() {
        return "fragments/create_download.twig";
    }
    
    public function get_parent_category($category) {
        return "downloads/";
    }
    
    public function get_back_category($category, $context) {
        if (strcasecmp(AbstractController::CONTEXT_LIST, $context) == 0) {
            return "";
        }
        
        return $this->get_parent_category($category);
    }
    
    public function contribute_to_comman_tasks($category) {
        $items = array(
            array("name"=>"Create New Download", "url"=>ADMIN_URL . "/CreateController.php?category=downloads/new")
        );
        
        return $items;
    }
    
    public function get_modified_time() {
        $file = $this->get_config_file("downloads");
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
    
    public function is_valid_file($file) {
        $items = $this->get_viewable_items("downloads");
        
        // convert to a map
        $files = array();
        foreach($items as $item) {
            if ($item->isViewable()) {
                $files[$item->getFile()] = $item;
            }
        }
        
        return array_key_exists($file, $files);
    }
    
    protected function get_config_file($category) {
        $dir = $this->get_config_dir("downloads");
        return $dir . DIRECTORY_SEPARATOR . "downloads.xml";
    }
    
    protected function load_config_data($category) {
        if ($category === null || strlen($category) == 0) {
            Logger::log_error("Error loading downloads configuration data: invalid argument list", __FILE__, __LINE__);
            return array();
        }
        
        $all_files = array();
        $data = array();
        $dir = $this->get_valid_path(DIR_DOWNLOADS_FILES, DIR_DOWNLOADS_FILES);
        
        if (file_exists($dir)) {
            $dh  = opendir($dir);
            while (false !== ($filename = readdir($dh))) {
                if ($filename !== '.' && $filename !== '..' && is_file($dir . DIRECTORY_SEPARATOR . $filename)) {
                    $all_files[$filename] = $filename;
                }
            }

            closedir($dh);
            ksort($all_files);

            // Load the gallery configdata - if the configdata file exists
            $config_file = $this->get_config_file($category);
            if (file_exists($config_file)) {
                $sxml = simplexml_load_file($config_file);
                $files = $sxml->files->children();
                foreach ($files as $file) {
                    // Only load the configdata if the file actually exists
                    $name = (string)$file->name;
                    if (array_key_exists($name, $all_files)) {
                        unset($all_files[$name]);
                        $data[] = array(
                            "name"=>$name, // name
                            "viewable"=>(string)$file->viewable // viewable
                        );
                    }
                }
            }
        }
        
        // If no configdata or stragglers not in the configdata
        // deal with them here
        foreach ($all_files as $f) {
            $data[] = array(
                "name"=>$f, // name
                "viewable"=>"no" // viewable
            );
        }
        
        return $data;
    }
    
    protected function save_config_data($parent, array $items) {
        if ($parent === null || strlen($parent) == 0) {
            Logger::log_error("Error saving downloads configuration data: invalid argument list", __FILE__, __LINE__);
            return false;
        }
        
        $dom = new DOMDocument();
        $files_elem = $dom->createElement('files');
        foreach($items as $item) {
            $file = $item->getFile();
            $viewable = $item->getViewable() == true ? "yes" : "no";
            
            $fileElement = $dom->createElement('file');
            
            $nameElement = $dom->createElement('name');
            $nameElement->appendChild($dom->createTextNode($file));
            $fileElement->appendChild($nameElement);
            
            $viewableElement = $dom->createElement('viewable');
            $viewableElement->appendChild($dom->createTextNode($viewable));
            $fileElement->appendChild($viewableElement);
            
            $files_elem->appendChild($fileElement);
        }
        
        $root = $dom->createElement('downloads_config');
        $root->appendChild($files_elem);
        
        $dom->appendChild($root);
        $buffer = $dom->saveXml();
        
        $file = $this->get_config_file($parent);
        if (file_put_contents($file, $buffer)) {
            chmod($file, 0640);
        } else {
            Logger::log_error("Problem saving downloads configuration data", __FILE__, __LINE__);
            return false;
        }
        
        return true;
    }
    
    private function make_item($parent, $file, $viewable) {
        $dir = DIR_DOWNLOADS_FILES . "/";
        
        $item = new DownloadItem("downloads/");
        $item->setFile($file);
        $item->setLastmodified(date("d/m/Y", filemtime($dir . $file)));
        $item->setSize(round(filesize($dir . $file) / 1000, 1) . " KB");
        $encoded_file = rawurlencode($file);
        $item->setLink("<a href='download.php?file={$encoded_file}'>download</a>");
        $item->setViewable($viewable == "yes" ? true : false);
        
        return $item;
    }
}
?>