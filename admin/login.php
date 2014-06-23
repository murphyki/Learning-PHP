<?php
    session_start();
    
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_ADMIN . "/AdminConfig.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/SecurityService.php");
    require_once(DIR_LIB . "/Twig/Autoloader.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/authentication/FacebookAuthenticationService.php");
    
    Logger::init();
    Twig_Autoloader::register();
    
    $provider = Utils::get_user_input("provider");
    if (strlen($provider) == 0) {
        if (strlen(Utils::get_user_input("code")) > 0) {
            $provider = "facebook";
        }
    } 
    
    // If this is a post then we want to do the login
    if (strlen($provider) > 0) {
        SecurityService::login($provider);
        exit();
    } else {
        $service = new FacebookAuthenticationService();
        $context = array(
            "GOOGLE_URL"=>"/admin/login.php?provider=google",
            "FACEBOK_URL"=>$service->get_login_url()
        );
    
        AdminConfig::load_template(array("templates"), "login.twig", $context);
    }

?>
