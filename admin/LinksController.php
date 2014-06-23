<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_ADMIN . "/AbstractController.php");
    include_once(DIR_LIB . "/core/LinkItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
class LinksController extends AbstractController {
    
    public function __construct() {
        
    }
    
    public function perform_action($action, $category) {
        $parent = $this->get_parent_category($category);
        $redirect_url = "";
        $status_msg = "";
    
        if (strcasecmp($action, "update") == 0) {
            $item = $this->update_item($parent, $category,
                Utils::get_user_input("caption"),
                Utils::get_user_input("href"), 
                Utils::get_user_input("title"),
                Utils::get_user_input("target")
            );
            if ($item !== null) {
                $status_msg = "Successfully updated " . $item->getName() . ".";
                $redirect_url = ADMIN_URL . "/EditController.php?category=" . $item->getDir();
            } else {
                $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
            }
        } else if (strcasecmp($action, "save") == 0) {
            $item = $this->save_item($parent, 
                Utils::get_user_input("caption"),
                Utils::get_user_input("href"), 
                Utils::get_user_input("title"),
                Utils::get_user_input("target")
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
            $item = $this->make_item($category, $cd['caption'], $cd['href'], $cd['title'], $cd['target']);
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
        if (func_num_args() != 5) {
            Logger::log_error("Error saving item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $caption = $args[1];
        $href = rawurldecode($args[2]); 
        $title = $args[3];
        $target = $args[4];
                
        if (strlen($caption) == 0 || strlen($href) == 0) {
            Logger::log_error("Error saving item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        $new_item = $this->make_item($parent, $caption, $href, $title, $target);
        
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

            return $updated_items[0];
        } else {
            Logger::log_error("Error saving item: save failed", __FILE__, __LINE__);
            return null;
        }
        
        return null;
    }
    
    public function update_item($parent, $category) {
        if (func_num_args() != 6) {
            Logger::log_error("Error updating item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $caption = $args[2];
        $href = rawurldecode($args[3]); 
        $title = $args[4];
        $target = $args[5];
        
        if (strlen($caption) == 0 || strlen($href) == 0) {
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
                $item->setCaption($caption);
                $item->setHref($href);
                $item->setTitle($title);
                $item->setTarget($target);
                break;
            }
        }
        
        if ($this->save_config_data($parent, $items)) {
            // Rename the directory owned by the updated item.
            $updated_items = $this->get_items($parent);
            foreach ($updated_items as $item) {
                if (strcasecmp($caption, $item->getCaption()) == 0) {
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
        return "fragments/edit_link.twig";
    }
    
    public function get_new_template() {
        return "fragments/create_link.twig";
    }
    
    public function get_parent_category($category) {
        return "links/";
    }
    
    public function get_back_category($category, $context) {
        if (strcasecmp(AbstractController::CONTEXT_LIST, $context) == 0) {
            return "";
        }
        
        return $this->get_parent_category($category);
    }
    
    public function contribute_to_comman_tasks($category) {
        $items = array(
            array("name"=>"Create New Link", "url"=>ADMIN_URL . "/CreateController.php?category=links/new")
        );
        
        return $items;
    }
    
    public function get_modified_time() {
        $file = $this->get_config_file("links");
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
        $dir = $this->get_config_dir("links");
        return $dir . DIRECTORY_SEPARATOR . "links.xml";
    }
    
    protected function load_config_data($category) {
        if ($category === null || strlen($category) == 0) {
            Logger::log_error("Error loading link configuration data: invalid argument list", __FILE__, __LINE__);
            return array();
        }
        
        $links = array();
        $file = $this->get_config_file($category);
        if (file_exists($file)) {
            $sxml = simplexml_load_file($file);
            foreach ($sxml->links->children() as $link) {
                $links[] = array(
                    "caption"=>(string)$link->caption,
                    "href"=>(string)$link->href,
                    "title"=>(string)$link->title,
                    "target"=>(string)$link->target);
            }
        }
        
        return $links;
    }
    
    protected function save_config_data($parent, array $items) {
        if ($parent === null || strlen($parent) == 0) {
            Logger::log_error("Error saving links configuration data: invalid argument list", __FILE__, __LINE__);
            return false;
        }
        
        $dom = new DOMDocument();
        $links = $dom->createElement('links');
        foreach($items as $item) {
            $caption = $dom->createElement('caption');
            $caption->appendChild($dom->createTextNode($item->getCaption()));
            $href = $dom->createElement('href');
            $href->appendChild($dom->createTextNode($item->getHref()));
            $title = $dom->createElement('title');
            $title->appendChild($dom->createTextNode($item->getTitle()));
            $target = $dom->createElement('target');
            $target->appendChild($dom->createTextNode($item->getTarget()));
            $link = $dom->createElement('link');
            $link->appendChild($href);
            $link->appendChild($caption);
            $link->appendChild($title);
            $link->appendChild($target);
            $links->appendChild($link);
        }
        
        $root = $dom->createElement('links_config');
        $root->appendChild($links);
        
        $dom->appendChild($root);
        $buffer = $dom->saveXml();
    
        $file = $this->get_config_file($parent);
        if (file_put_contents($file, $buffer)) {
            chmod($file, 0640);
        } else {
            Logger::log_error("Problem saving link configuration data", __FILE__, __LINE__);
            return false;
        }
        
        return true;
    }
    
    private function make_item($parent, $caption, $href, $title, $target) {
        $item = new LinkItem("links/");
        $item->setCaption($caption);
        $item->setHref($href);
        $item->setTitle($title);
        $item->setTarget($target);
        return $item;
    }
}
?>