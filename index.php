<?php 
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  require_once(DIR_LIB . "/core/ArticleItem.php");
  require_once(DIR_LIB . "/core/Logger.php");
  require_once(DIR_LIB . "/Twig/Autoloader.php");
  
  Logger::init();
  
  Twig_Autoloader::register();
  
  $item = new ArticleItem("home", "Home");
  $item->load_from_xml();
    
  $context = array(
      'PAGE_TITLE'=>'Home',
      'PAGE_DESCRIPTION'=>APP_NAME . ", add app description here...",
      'PAGE_KEYWORDS'=>"add app keywords here...",
      'MAIN_CONTENT'=>$item->getContent(),
      'SLIDER_WIDTH'=>SLIDER_WIDTH,
      'SLIDER_HEIGHT'=>SLIDER_HEIGHT
  );
  
  Config::load_template(array("templates"), "home.twig", $context);
?>