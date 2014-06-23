<?php
    session_start();
    
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_LIB . "/core/SecurityService.php");
    include_once(DIR_LIB . "/core/CategoryItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    require_once(DIR_LIB . "/Twig/Autoloader.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
    Logger::init();
    Twig_Autoloader::register();
    SecurityService::validate();
    
    $category_param = Utils::get_user_input("category");
    $controller = AdminConfig::get_controller($category_param);
    
    $admin_opts = AdminConfig::get_admin_config_options($category_param);
    
    $item = $controller->get_item($category_param);
    
    $context = array(
        "CATEGORY"=>$category_param,
        "BACK_URL"=>ADMIN_URL . "/index.php?category=" . $controller->get_back_category($category_param, AbstractController::CONTEXT_EDIT),
        "EDIT_TEMPLATE"=>$controller->get_edit_template(),
        "SHOW_SUBCATEGORY_CHECKBOX"=>$admin_opts["show_subcategory_checkbox"],
        "item"=>$item
    );
    
    AdminConfig::load_template(array("templates"), "edit.twig", $context);
?>
