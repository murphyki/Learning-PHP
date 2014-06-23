<?php 
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  include_once(DIR_LIB . "/core/GalleryService.php");
  require_once(DIR_LIB . "/core/Logger.php");
  require_once(DIR_LIB . "/Twig/Autoloader.php");
  
  Logger::init();
  
  Twig_Autoloader::register();
  
  $context = array(
      'PAGE_TITLE'=>'Gallery',
      'GALLERY_TOKEN'=>GalleryService::get_modified_time(),
      'GALLERIES'=>GalleryService::format_galleries_for_selector(null)
  );
  
  Config::load_template(array("templates"), "gallery.twig", $context);
?>