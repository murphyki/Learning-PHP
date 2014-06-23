<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/DownloadsService.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/Logger.php");
    include_once(DIR_LIB . "/core/ControllerService.php");
    
    Logger::init();
    
    $output = "";
    
    $action = Utils::get_user_input("action");
    if (strcasecmp($action, "load_file_list") == 0) {
        $output = DownloadsService::format_files_for_download_page();
    } else if (strcasecmp($action, "check_for_updates") == 0) {
        $token = Utils::get_user_input("token");
        if (strlen($token) == 0) {
            Logger::log_error("Unable to check for updates.", __FILE__, __LINE__);
        } else {
            $output = DownloadsService::is_modified($token);
        }
    } else {
        Logger::log_error("Unknown action requested...", __FILE__, __LINE__);
    }
    
    ControllerService::render_response($output);
?>
