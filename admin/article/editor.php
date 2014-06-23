<?php
    session_start();
    
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_ADMIN . "/ArticleController.php");
    include_once(DIR_LIB . "/core/ArticleItem.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/SecurityService.php");
    require_once(DIR_LIB . "/Twig/Autoloader.php");
    include_once(DIR_LIB . "/core/Logger.php");
    
    Logger::init();
    
    Twig_Autoloader::register();
    
    SecurityService::validate();
    
    $category = Utils::get_user_input("category");
    $controller = new ArticleController();
    $article = $controller->get_item($category);
            
    $context = array(
        "CATEGORY"=>$category,
        "BACK"=>$controller->get_back_category($category, AbstractController::CONTEXT_LIST),
        'IS_NOTICEBOARD_ITEM'=>$controller->is_noticeboard_article($category),
        'EDITOR_HEIGHT'=>$controller->get_preferred_editor_height($category),
        'article'=>$article
    );
    
    AdminConfig::load_template(array("templates", "../templates"), "editor.twig", $context);
?>
