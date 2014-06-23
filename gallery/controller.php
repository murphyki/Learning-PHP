<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/GalleryService.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/ControllerService.php");
    
    Logger::init();
    
    $output = "";
    
    $action = Utils::get_user_input("action");
    if (strcasecmp($action, "load_gallery") == 0) {
        $gallery = Utils::get_user_input("gallery");
        if (strlen($gallery) == 0) {
            Logger::log_error("Missing request parameters.", __FILE__, __LINE__);
        } else {
            $output = GalleryService::format_for_gallery_page($gallery);
        }
    } else if (strcasecmp($action, "load_gallery_page_selector") == 0) {
        $gallery = Utils::get_user_input("gallery");
        $output = GalleryService::format_galleries_for_selector($gallery);
    } else if (strcasecmp($action, "check_for_updates") == 0) {
        $token = Utils::get_user_input("token");
        if (strlen($token) == 0) {
            Logger::log_error("Unable to check for updates.", __FILE__, __LINE__);
        } else {
            $output = GalleryService::is_modified($token);
        }
    } else {
        Logger::log_error("Unknown action requested...", __FILE__, __LINE__);
    }
    
    ControllerService::render_response($output);
?>
