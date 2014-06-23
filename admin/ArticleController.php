<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_ADMIN . "/AbstractController.php");
    include_once(DIR_ADMIN . "/CategoryController.php");
    include_once(DIR_LIB . "/core/ArticleItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
class ArticleController extends AbstractController {
    private $categoryController = null;
    
    public function __construct() {
        $this->categoryController = new CategoryController();
    }
    
    public function perform_action($action, $category) {
        $parent = $this->get_parent_category($category);
        $status_msg = "";
        $redirect_url = "";
    
        if (strcasecmp($action, "publish") == 0) {
            $title = Utils::get_user_input("article_title");
            
            if (strcasecmp(substr(strtolower($category), -3), "new") == 0) { // category ends with 'new'
                $article = $this->save_item($parent, $title);// saving for the first time...
                if ($article !== null) {
                    $category = $article->getCategory();
                    $status_msg = "Successfully saved and published {$title}";
                    $redirect_url = ADMIN_URL . "/article/editor.php?category={$category}&article_title={$title}";
                } else {
                    $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
                }
                    
            } else {
                // Make double sure we dont create a category/article called 'new'.
                if (strcasecmp(substr(strtolower($category), -3), "new") != 0) {
                    $article = $this->update_item($parent, $category, $title);
                    if ($article !== null) {
                        $status_msg = "Successfully updated and published {$title}";
                        $redirect_url = ADMIN_URL . "/article/editor.php?category={$category}&article_title={$title}";
                    } else {
                        $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
                    }
                }
            }
        } else {
            Logger::log_error("Unknown action requested", __FILE__, __LINE__);
            $redirect_url = ADMIN_URL . "/index.php?category=" . $parent;
        }
         
        ControllerService::set_status_context($status_msg, strlen($status_msg) > 0 ? "success" : "");
        
        return $redirect_url;
    }
    
    public function get_item($category) {
        return $this->find_item($this->categoryController->get_parent_category($category), $category);
    }
    
    public function get_items($category) {
        $raw_items = $this->categoryController->get_items($category);
        $items = array();
        for($i = 0; $i < count($raw_items); $i++) {
            $item = new ArticleItem($raw_items[$i]->getDir(), $raw_items[$i]->getName());
            $item->load_from_xml();
            $items[] = $item;
        }
        
        return $items;
    }
    
    public function get_viewable_items($category) {
        $raw_items = $this->categoryController->get_items($category);
        $viewable_items = array();
        for($i = 0; $i < count($raw_items); $i++) {
            if ($raw_items[$i]->getViewable()) {
                $item = new ArticleItem($raw_items[$i]->getDir(), $raw_items[$i]->getName());
                $item->load_from_xml();
                $viewable_items[] = $item;
            }
        }
        
        return $viewable_items;
    }
    
    public function save_item($parent) {
        if (func_num_args() != 2) {
            Logger::log_error("Error saving item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $title = $args[1];
        
        $item = $this->categoryController->find_item($parent, $title);
        
        if ($item === null) {
            // We need to create a category for this article...
            $item = $this->categoryController->save_item($parent, $title, "yes", "no");
        }
        
        if ($item !== null) {
            return $this->update_item($parent, $item->getDir(), $title);
        } else {
            Logger::log_error("Error saving item: category creation failed", __FILE__, __LINE__);
            return null;
        }
    }
    
    public function update_item($parent, $category) {
        if (func_num_args() != 3) {
            Logger::log_error("Error updating item: invalid argument list", __FILE__, __LINE__);
            return null;
        }
        
        // Make double sure we dont create a category/article called 'new'.
        if (strcasecmp(substr(strtolower($category), -3), "new") == 0) {
            Logger::log_error("Error updating item: invalid category", __FILE__, __LINE__);
            return null;
        }
        
        $args = func_get_args();
        $title = $args[1];
        
        $category_item = $this->categoryController->find_item($parent, $category);
        
        if ($category_item === null) {
            Logger::log_error("Error updating item: could not find category item", __FILE__, __LINE__);
            return null;
        }
        
        $article = new ArticleItem($category, $title);
        $article->load_from_request();

        // Save it out to the content area as xml
        if ($article->save()) {
            if ($category_item->getViewable()) {
                $this->update_touch_file($category);
                
                if ($article->publish()) {
                    $fb = new FacebookService();
                    $fb->publish_item($article);
                } else {
                    Logger::log_error("Error updating item: publish failed", __FILE__, __LINE__);
                    return null;
                }
            }

            return $article;
        } else {
            Logger::log_error("Error updating item: save failed", __FILE__, __LINE__);
            return null;
        }
        
        return null;
    }
    
    public function delete_item($parent, $category) {
        return $this->categoryController->delete_item($parent, $category);
    }
    
    public function find_item($parent, $category) {
        $raw_item = $this->categoryController->find_item($parent, $category);
        if ($raw_item !== null) {
            $item = new ArticleItem($raw_item->getDir(), $raw_item->getName());
            $item->load_from_xml();
            return $item;
        }
           
        return null;
    }
    
    public function get_edit_template() {
        return "templates/edit.twig";
    }
    
    public function get_new_template() {
        return $this->get_edit_template(); // There the same...
    }
    
    public function get_preferred_editor_height($category) {
        if ($this->is_noticeboard_article($category)) {
            return 300;
        }
        
        return 600;
    }
    
    public function is_noticeboard_article($category) {
        $start = substr(strtolower($category), 0, strlen("noticeboard"));
        return strcasecmp($start, "noticeboard") == 0;
    }
    
    protected function save_config_data($parent, array $items) {
        // no op...
    }
    
    protected function load_config_data($category) {
        // no op...
    }
}
?>