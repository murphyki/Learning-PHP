<?php
    session_start();
    
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_LIB . "/core/SecurityService.php");
    include_once(DIR_LIB . "/core/Utils.php");
    require_once(DIR_LIB . "/Twig/Autoloader.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
    Logger::init();
    Twig_Autoloader::register();
    AdminConfig::authenticate();
    
    $category_param = Utils::get_user_input("category");
    $controller = AdminConfig::get_controller($category_param);
    
    $items = $controller->get_items($category_param);
    
    $context = array(
        "ITEM_LIST_TEMPLATE"=>$controller->get_list_template(),
        "CATEGORY"=>$category_param,
        "BACK"=>$controller->get_back_category($category_param, AbstractController::CONTEXT_LIST),
        "common_tasks"=>$controller->contribute_to_comman_tasks($category_param),
        "items"=>$items
    );
    
    AdminConfig::load_template(array("templates", "templates/fragments"), "list.twig", $context);
?>
