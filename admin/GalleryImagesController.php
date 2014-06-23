<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_ADMIN . "/GalleryController.php");
    include_once(DIR_ADMIN . "/AbstractController.php");
    include_once(DIR_LIB . "/core/GalleryImageItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
class GalleryImagesController extends AbstractController {
    
    public function __construct() {
        
    }
    
    public function perform_action($action, $category) {
        $parent = $this->get_parent_category($category);
        $redirect_url = "";
        $status_msg = "";
        
        if (strcasecmp($action, "update") == 0) {
            $item = $this->update_item($parent, $category,
                Utils::get_user_input("name"), 
                Utils::get_user_input("description"), 
                Utils::get_user_input("title"), 
                Utils::get_user_input("image"), 
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
                Utils::get_user_input("description"), 
                Utils::get_user_input("title"), 
                Utils::get_user_input("image"), 
                Utils::get_user_input("viewable") == "on" ? "yes" : "no"
            );
            if ($item !== null) {
                $status_msg = "Successfully saved " . $item->getName() . ".";
                $redirect_url = ADMIN_URL . "/EditController.php?category=" . $item->getDir();
            } else {
                $redirect_url = ADMIN_URL . "/index.php?category=" . $category;
            }
        } else if (strcasecmp($action, "delete") == 0) {
            //$item = $this->delete_item($parent, $category);
            //if ($item !== null) {
            //    $status_msg = "Successfully deleted " . $item->getName() . ".";
            //    $redirect_url = ADMIN_URL . "/index.php?category=" . $item->getParent();
            //} else {
            //    $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
            //}
            $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
            Logger::log_error("Delete not allowed on gallery items.");
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
            $item = $this->make_item($category, $cd['name'], $cd['description'], $cd['title'], $cd['image'], $cd['viewable']);
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
        if (func_num_args() != 6) {
            Logger::log_error("Error saving item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $name = $args[1];
        $description = $args[2];
        $title = $args[3];
        $image = $args[4];
        $viewable = $args[5];
                
        if (strlen($name) == 0) {
            Logger::log_error("Error saving item: invalid values", __FILE__, __LINE__);
            return null;
        }
        
        $new_item = $this->make_item($parent, $name, $description, $title, $image, $viewable);
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
        if (func_num_args() != 7) {
            Logger::log_error("Error updating item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $name = $args[2];
        $description = $args[3];
        $title = $args[4];
        $image = $args[5];
        $viewable = $args[6];
        
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
                $item->setDescription($description);
                $item->setTitle($title);
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
        // no-op...
        return null;
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
        return "fragments/edit_gallery_item.twig";
    }
    
    public function get_new_template() {
        return "fragments/create_gallery_item.twig";
    }
   
    protected function get_config_file($category) {
        $dir = $this->get_config_dir($category);
        return $dir . DIRECTORY_SEPARATOR . "config.xml";
    }
    
    public function get_back_category($category, $context) {
        if (strcasecmp(AbstractController::CONTEXT_LIST, $context) == 0) {
            return GALLERY_ALIAS . "/";
        }
        
        return $this->get_parent_category($category);
    }
    
    public function contribute_to_comman_tasks($category) {
        $items = array(
            
        );
        
        return $items;
    }
    
    protected function load_config_data($category) {
        if ($category === null || strlen($category) == 0) {
            Logger::log_error("Error loading gallery image configuration data: invalid argument list", __FILE__, __LINE__);
            return array();
        }
        
        $gallery = $this->get_gallery($category);
        if (!$this->is_valid_gallery($gallery, false)) {
            Logger::log_error("Error loading gallery image configuration data: invalid gallery: {$gallery}", __FILE__, __LINE__);
            return array();
        }
        
        // load the image files
        $images = array();
        $dir = $this->get_valid_path(DIR_GALLERY . "/{$gallery}", DIR_GALLERY);
        if (file_exists($dir)) {
            $dh  = opendir($dir);
            while (false !== ($filename = readdir($dh))) {
                if (is_file($dir . DIRECTORY_SEPARATOR . $filename) != 0 && preg_match("/^.*\.(jpg|jpeg|png|gif)$/i", $filename)) {
                    $images[$filename] = $filename;
                }
            }
            closedir($dh);
            ksort($images, SORT_NUMERIC);
        }
        
        // Load the gallery metadata - if the metadata file exists
        $data = array();
        $file = $this->get_config_file($category);
        if (file_exists($file)) {
            $sxml = simplexml_load_file($file);
            $metadata = $sxml->images->children();
            foreach ($metadata as $image) {
                // Only load the metadata if the file actually exists
                $name = (string)$image->name;
                if (array_key_exists($name, $images)) {
                    unset($images[$name]);
                    $data[] = array(
                        "image"=>$name, // image
                        "name"=>$name, // name
                        "title"=>(string)$image->title, 
                        "description"=>(string)$image->description,
                        "viewable"=>(string)$image->viewable
                    );
                }
            }
        }
        
        // If no metadata or stragglers not in the metadata
        // deal with them here
        foreach ($images as $image) {
            $data[] = array(
                "image"=>$image, // image
                "name"=>$image, // name
                "title"=>"", // title
                "description"=>"", // description
                "viewable"=>"no" // viewable
            );
        }
        
        return $data;
    }
    
    protected function save_config_data($parent, array $items) {
        if ($parent === null || strlen($parent) == 0) {
            Logger::log_error("Error saving gallery image configuration data: invalid argument list", __FILE__, __LINE__);
            return false;
        }
        
        $gallery = $this->get_gallery($parent);
        if (!$this->is_valid_gallery($gallery, false)) {
            Logger::log_error("Error saving gallery image configuration data: invalid gallery: {$gallery}", __FILE__, __LINE__);
            return false;
        }
        
        $dom = new DOMDocument();
        $images = $dom->createElement('images');
        $images->setAttribute("gallery", $gallery);
        
        foreach($items as $item) {
            // RENAMING CODE....need to look into renaming in general
            //$images_dir = DIR_GALLERY . "/{$gallery}/";
            //
            //$old_name = basename($info[0]);
            //$old_filename = $images_dir . $old_name;
            //if (file_exists($old_filename)) {
            //    $new_name = $info[1];
            //    $new_filename = $images_dir . $new_name;
            //    if (strcasecmp($old_filename, $new_filename) != 0) {
            //        if (rename($old_filename, $new_filename)) {
            //            $thumbs_dir = DIR_GALLERY . "/{$gallery}/thumbs/";
            //            $old_thumbname = $thumbs_dir . $old_name;
            //            if (file_exists($old_thumbname)) {
            //                $new_thumbname = $thumbs_dir . $new_name;
            //                rename($old_thumbname, $new_thumbname);
            //            }
            //        }
            //    }
            //}
            
            $image = $dom->createElement('image');
            $name = $dom->createElement('name');
            $name->appendChild($dom->createTextNode($item->getName()));
            $title = $dom->createElement('title');
            $title->appendChild($dom->createTextNode($item->getTitle()));
            $description = $dom->createElement('description');
            $description->appendChild($dom->createTextNode($item->getDescription()));
            $viewable = $dom->createElement('viewable');
            $viewable->appendChild($dom->createTextNode($item->getViewable() == true ? "yes" : "no"));
            $image->appendChild($name);
            $image->appendChild($title);
            $image->appendChild($description);
            $image->appendChild($viewable);
            $images->appendChild($image);
        }
        
        $root = $dom->createElement('gallery');
        $root->appendChild($images);
        $dom->appendChild($root);
        $buffer = $dom->saveXml();
        
        $file = $this->get_config_file($parent);
        
        if (file_put_contents($file, $buffer)) {
            chmod($file, 0644);
        } else {
            Logger::log_error("Problem saving gallery image configuration data", __FILE__, __LINE__);
            return false;
        }
        
        return true;
    }
    
    private function make_item($parent, $name, $description, $title, $image, $viewable) {
        $item = new GalleryImageItem($parent);
        $item->setName($name);
        $item->setDescription($description);
        $item->setTitle($title);
        $gallery = $this->get_gallery($parent);
        $item->setImage(GALLERIES_URL . "/{$gallery}/$image");
        $item->setImageWithCacheBust($item->getImage() . "?cacheBust=" . filemtime(DIR_BASE . $item->getImage()));
        $item->setThumbNail(GALLERIES_URL . "/{$gallery}/thumbs/" . $image);
        $item->setViewable($viewable == "yes" ? true : false);
        return $item;
    }
    
    private function get_gallery($category) {
        $tokens = preg_split("/\//", $category);
        $gallery = null;
        if (count($tokens) > 1) {
            if (strcasecmp("gallerys", $tokens[0]) == 0) {
                $gallery = $tokens[1];
            } else {
                return $tokens[0];
            }
        }
        
        return $gallery;
    }
    
    private function is_valid_gallery($gallery, $check_viewable) {
        $galleryController = new GalleryController();
        return $galleryController->is_valid_gallery($gallery, $check_viewable);
    }

}
?>