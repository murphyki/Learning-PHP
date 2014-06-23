<?php 
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  include_once(DIR_LIB . "/core/LinksService.php");
  require_once(DIR_LIB . "/core/Logger.php");
  require_once(DIR_LIB . "/Twig/Autoloader.php");
  
  Logger::init();
  
  Twig_Autoloader::register();
  
  $context = array(
      'PAGE_TITLE'=>'Links',
      'LINKS_TOKEN'=>LinksService::get_modified_time(),
      "linkitems"=>LinksService::get_items()
  );
  
  Config::load_template(array("templates"), "links.twig", $context);
?>