<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/ItemController.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
abstract class AbstractController implements ItemController {
    protected $createDir;
    
    const CONTEXT_LIST = "list";
    const CONTEXT_CREATE = "create";
    const CONTEXT_EDIT = "edit";
    const CONTEXT_DELETE = "delete";

    public function __construct() {
        $this->createDir = false;
    }
    
    protected abstract function save_config_data($parent, array $items);
    protected abstract function load_config_data($category);

    public function get_list_template() {
        return "fragments/default_item_list.twig";
    }
    
    public function get_parent_category($category) {
        $tokens = preg_split("/\//", $category);
        $parent = "";
        if (count($tokens) > 1) {
            $parent = implode("/", array_slice($tokens, 0, count($tokens) - 1));
        }
        
        return $parent;
    }
    
    public function get_back_category($category, $context) {
        return $this->get_parent_category($category);
    }
    
    public function contribute_to_comman_tasks($category) {
        $items = array(
            array("name"=>"Create New Noticeboard Item", "url"=>ADMIN_URL . "/article/editor.php?category=noticeboard/new"),
            array("name"=>"Edit Current Noticeboard Items", "url"=>ADMIN_URL . "/index.php?category=noticeboard"),
            array(""=>"", ""=>""), // divider
            array("name"=>"Create New Download", "url"=>ADMIN_URL . "/CreateController.php?category=downloads/new"),
            array("name"=>"Edit Current Downloads", "url"=>ADMIN_URL . "/index.php?category=downloads/"),
            array(""=>"", ""=>""), // divider
            array("name"=>"Create New Link", "url"=>ADMIN_URL . "/CreateController.php?category=links/new"),
            array("name"=>"Edit Current Links", "url"=>ADMIN_URL . "/index.php?category=links/"),
            array(""=>"", ""=>""), // divider
            array("name"=>"Create New Category", "url"=>ADMIN_URL . "/CreateController.php?category={$category}/new"),
        );
        
        return $items;
    }
    
    protected function setCreateDir($createDir) {
        $this->createDir = $createDir;
    }
    
    protected function get_config_dir($category) {
        if ($category === null || strcasecmp($category, "null") == 0) {
            return null;
        }
        
        $dir = "";
        if (strlen($category) == 0 || 
            strcasecmp($category, "root") == 0) {
            $dir = DIR_CONTENT;
        } else {
            $dir = DIR_CONTENT . DIRECTORY_SEPARATOR . $category;
        }
        
        $valid_dir = $this->get_valid_path($dir);
        
        if ($this->createDir) {
            $valid_dir = Utils::make_dir($valid_dir, 0750);
        }
        
        return $valid_dir;
    }
    
    protected function get_config_file($category) {
        $dir = $this->get_config_dir($category);
        if ($dir !== null) {
            return $dir . "/config.xml";
        }
        
        return null;
    }
    
    protected function do_rename($orig, $new) {
        if (file_exists($orig) && !file_exists($new)) {
            if (is_dir($orig)) {
                if (rename($orig, $new)) {
                    return true;
                } else {
                    Logger::log_error("Failed to rename {$orig} to {$new}", __FILE__, __LINE__);
                }
            }
        }

        return false;
    }
    
    protected static function do_clean($dir) {
        if (file_exists($dir) && is_dir($dir)) {
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;

                $file = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_file($file) && file_exists($file)) {
                    if (!unlink($file)) {
                        Logger::log_error("Failed to remove file: {$file}", __FILE__, __LINE__);
                    }
                } else {
                    do_clean($file);
                }
            }

            if(!rmdir($dir)) {
                Logger::log_error("Failed to remove directory: {$dir}", __FILE__, __LINE__);
            }
        } 
    }
    
    protected function get_valid_path($path, $root_dir = DIR_CONTENT) {
        if ($path === null || strlen($path) == 0) {
            return $path;
        }
        
        $root = realpath($root_dir);
        if ($root === false) {
            Logger::log_error("bad request", __FILE__, __LINE__);
            die();
        }
        
        $dir = realpath($path);
        if ($dir === false) {
            return $path; // path does not exist yet
        }
        
        $test = substr($dir, 0, strlen($root));
        
        if (Utils::starts_with($test, $root)) {
            return $dir;
        }
        
        Logger::log_error("bad request", __FILE__, __LINE__);
        die();
    }
    
    protected function get_sorted_items($category, array $keys) {
        $items = $this->get_items($category);
        $sorted_items = array();
        
        foreach ($keys as $key) {
            foreach ($items as $item) {
                if (strcasecmp($item->getName(), $key) == 0) {
                    $sorted_items[] = $item;
                    break;
                }
            }
        }
        
        return $sorted_items;
        
    }
    
    protected function save_order($category, $names) {
        if (func_num_args() != 2) {
            Logger::log_error("Error saving order: invalid argument list", __FILE__, __LINE__);
            return false;
        }
        
        $items = $this->get_items($category);
        
        if (count($items) != count($names)) {
            Logger::log_error("Error saving order: item/name mismatch", __FILE__, __LINE__);
            return false;
        }
        
        $sorted_items = $this->get_sorted_items($category, $names);
        
        if (count($sorted_items) != count($items)) {
            Logger::log_error("Error saving order: something went wrong during the sort", __FILE__, __LINE__);
            return false;
        }
        
        return $this->save_config_data($category, $sorted_items);
    }
    
    protected function generate_toc($category) {
        $opts = AdminConfig::get_admin_config_options($category);
        if ($opts["generate_toc"] === true) {
            $dir = $this->get_valid_path(DIR_BASE . DIRECTORY_SEPARATOR . $category, DIR_BASE);
            if (!file_exists($dir)) {
                Utils::make_dir($dir);
            }

            $context = array(
                "CATEGORY"=>$category
            );

            $toc_template = $opts["toc_template"];
            if ($toc_template !== null && strlen($toc_template) > 0) {
                $template = Config::parse_template(array(), $toc_template, $context);
                file_put_contents($dir . "/index.php", $template);
            }
        }
    }
    
    protected function update_touch_file($category) {
        $opts = AdminConfig::get_admin_config_options($category);
        if ($opts["update_touch_file"] === true) {
            if (array_key_exists("touch_file", $opts)) {
                touch($opts["touch_file"]);
            }
        }
    }
}
?>