<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_ADMIN . "/AbstractController.php");
    include_once(DIR_LIB . "/core/SliderItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
class SliderController extends AbstractController {
    
    public function __construct() {
        
    }
    
    public function perform_action($action, $category) {
        $parent = $this->get_parent_category($category);
        $redirect_url = "";
        $status_msg = "";
    
        if (strcasecmp($action, "update") == 0) {
            $item = $this->update_item($parent, $category,
                Utils::get_user_input("source"),
                Utils::get_user_input("description"), 
                Utils::get_user_input("link")
            );
            if ($item !== null) {
                $status_msg = "Successfully updated " . $item->getName() . ".";
                $redirect_url = ADMIN_URL . "/EditController.php?category=" . $item->getDir();
            } else {
                $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
            }
        } else if (strcasecmp($action, "save") == 0) {
            $item = $this->save_item($parent, 
                Utils::get_user_input("source"),
                Utils::get_user_input("description"), 
                Utils::get_user_input("link")
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
            $item = $this->make_item($category, $cd['source'], $cd['description'], $cd['link']);
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
        $source = $args[1];
        $description = $args[2]; 
        $link = $args[3];
                
        if (strlen($source) == 0) {
            Logger::log_error("Error saving item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        if (!file_exists(DIR_BASE . $source)) {
            Logger::log_error("Error saving item: the image does not exist", __FILE__, __LINE__);
            return null;
        }
        
        $new_item = $this->make_item($parent, $source, $description, $link);
        
        // NOTE: In other controllers we check for duplicates but slider is an 
        // exception to this as we may ant to have the same image included
        // more than once. Need to ensure that delete works by only removing
        // a single instance, and it is the right one...
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

            return $updated_items[0];
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
        $source = $args[2];
        $description = $args[3]; 
        $link = $args[4];
        
        if (strlen($source) == 0) {
            Logger::log_error("Error updating item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        $original = $this->get_item($category);
        
        if ($original === null) {
            Logger::log_error("Error updating item: item to update does not exist", __FILE__, __LINE__);
            return null;
        }
        
        $image = $original->getImage();
        
        $items = $this->get_items($parent);
        foreach ($items as $item) {
            if (strcasecmp($original->getName(), $item->getName()) == 0) {
                $item->setDescription($description);
                $item->setLink($link);
                break;
            }
        }
        
        if ($this->save_config_data($parent, $items)) {
            // Rename the directory owned by the updated item.
            $updated_items = $this->get_items($parent);
            foreach ($updated_items as $item) {
                if (strcasecmp($image, $item->getImage()) == 0) {
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
        return "fragments/edit_slider_item.twig";
    }
    
    public function get_new_template() {
        return "fragments/create_slider_item.twig";
    }
    
    public function get_list_template() {
        return "fragments/slider_item_list.twig";
    }
   
    public function get_parent_category($category) {
        return "slider/";
    }
    
    public function get_back_category($category, $context) {
        if (strcasecmp(AbstractController::CONTEXT_LIST, $context) == 0) {
            return "";
        }
        
        return $this->get_parent_category($category);
    }
    
    public function contribute_to_comman_tasks($category) {
        $items = array(
            array("name"=>"Create New Slider Item", "url"=>ADMIN_URL . "/CreateController.php?category=slider/new")
        );
        
        return $items;
    }
    
    public function get_modified_time() {
        $file = $this->get_config_file("slider");
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
   
    protected function get_config_file($category) {
        $dir = $this->get_config_dir("slider");
        return $dir . DIRECTORY_SEPARATOR . "slider.xml";
    }
    
    protected function load_config_data($category) {
        if ($category === null || strlen($category) == 0) {
            Logger::log_error("Error loading slider configuration data: invalid argument list", __FILE__, __LINE__);
            return array();
        }
        
        $data = array();
        $file = $this->get_config_file($category);
        if (file_exists($file)) {
            $sxml = simplexml_load_file($file);
            foreach ($sxml->images->children() as $image) {
                $data[] = array(
                    "source"=>(string)$image->source, 
                    "description"=>(string)$image->description, 
                    "link"=>(string)$image->link);
            }
        }
        
        return $data;
    }
    
    protected function save_config_data($parent, array $items) {
        if ($parent === null || strlen($parent) == 0) {
            Logger::log_error("Error saving slider configuration data: invalid argument list", __FILE__, __LINE__);
            return false;
        }
        
        $dom = new DOMDocument();
        $imgs = $dom->createElement('images');
        foreach($items as $item) {
            $source = $dom->createElement('source');
            $source->appendChild($dom->createTextNode($item->getSource()));
            $desc = $dom->createElement('description');
            $desc->appendChild($dom->createTextNode($item->getDescription()));
            $link = $dom->createElement('link');
            $link->appendChild($dom->createTextNode($item->getLink()));
            $image = $dom->createElement('image');
            $image->appendChild($source);
            $image->appendChild($desc);
            $image->appendChild($link);
            $imgs->appendChild($image);
        }
        
        $root = $dom->createElement('slider_config');
        $root->appendChild($imgs);
        
        $dom->appendChild($root);
        $buffer = $dom->saveXml();
    
        $file = $this->get_config_file($parent);
        if (file_put_contents($file, $buffer)) {
            chmod($file, 0640);
        } else {
            Logger::log_error("Problem saving slider configuration data", __FILE__, __LINE__);
            return false;
        }
        
        return true;
    }
    
    private function make_item($parent, $source, $description, $link) {
        $item = new SliderItem("slider/");
        $item->setImage(basename($source));
        $item->setSource($source);
        $item->setSourceWithCacheBust($source);
        $item->setDescription($description);
        $item->setLink($link);
        
        $image_file = DIR_BASE . $source;
        $cacheBust = null;
        if (file_exists($image_file)) {
            $cacheBust = filemtime($image_file);
            $item->setSourceWithCacheBust($item->getSource() . "?cacheBust=" . filemtime($image_file));
            
            $info = getimagesize($image_file);
            if ($info !== null) {
                $maxWidth  = SLIDER_WIDTH;
                $maxHeight = SLIDER_HEIGHT;
                $width     = $info[0];
                $height    = $info[1];

                // Check if the current width is larger than the max
                if($width > $maxWidth){
                    $ratio  = $maxWidth / $width;       // get ratio for scaling image
                    $width  = floor($maxWidth);         // Set new width
                    $height = floor($height * $ratio);  // Scale height based on ratio
                }

                // Check if current height is larger than max
                if($height > $maxHeight){
                    $ratio  = $maxHeight / $height;     // get ratio for scaling image
                    $height = floor($maxHeight);        // Set new height
                    $width  = floor($width * $ratio);   // Reset width to match scaled image
                }
            }
            
            $item->setWidth($width);
            $item->setHeight($height);
        }

        return $item;
    }

}
?>