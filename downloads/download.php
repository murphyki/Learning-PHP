<?php
    include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
    include_once(DIR_LIB . "/core/Utils.php");
    include_once(DIR_LIB . "/core/DownloadsService.php");
    include_once(DIR_LIB . "/core/ControllerService.php");
    
    $file = Utils::get_user_input("file");
    if (strlen($file) > 0) {
        if (DownloadsService::is_valid_file($file)) {
            $filename = $file;
            $file = DIR_DOWNLOADS_FILES . "/" . $file;
            if (file_exists($file) && is_readable($file) && preg_match('/\.pdf$/', $file)) {
                header("Content-type: application/pdf");
                header("Content-Disposition: attachment; filename=\"{$filename}\"");
                readfile($file);
                exit();
            }
        }
    }
    
    header("HTTP/1.0 404 Not Found");
    ControllerService::redirect("/404.php");
?>
