<?php 
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  include_once(DIR_LIB . "/core/DownloadsService.php");
  require_once(DIR_LIB . "/core/Logger.php");
  require_once(DIR_LIB . "/Twig/Autoloader.php");
  
  Logger::init();
  
  Twig_Autoloader::register();
  
  $context = array(
      'PAGE_TITLE'=>'Downloads',
      'DOWNLOAD_TOKEN'=>DownloadsService::get_modified_time(),
      "downloaditems"=>DownloadsService::get_viewable_items()
  );
  
  Config::load_template(array("templates"), "downloads.twig", $context);
?>