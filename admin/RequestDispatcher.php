<?php
    session_start();
    
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/SecurityService.php");
    include_once(DIR_LIB . "/core/ControllerService.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
    Logger::init();
    SecurityService::validate();
    
    $action_param = Utils::get_user_input("action");
    $category_param = Utils::get_user_input("category");
    $controller = AdminConfig::get_controller($category_param);
    
    $redirect_url = $controller->perform_action($action_param, $category_param);
    
    ControllerService::redirect($redirect_url, false);
?>
