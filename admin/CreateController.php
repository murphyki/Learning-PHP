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
    SecurityService::validate();
    
    $category_param = Utils::get_user_input("category");
    $controller = AdminConfig::get_controller($category_param);
    
    $context = array(
        "CATEGORY"=>$category_param,
        "BACK_URL"=>ADMIN_URL . "/index.php?category=" . $controller->get_back_category($category_param, AbstractController::CONTEXT_CREATE),
        "CREATE_TEMPLATE"=>$controller->get_new_template()
    );
    
    AdminConfig::load_template(array("templates"), "create.twig", $context);
?>
