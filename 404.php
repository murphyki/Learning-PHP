<?php 
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  require_once(DIR_LIB . "/Twig/Autoloader.php");
  
  Twig_Autoloader::register();
  
  $context = array(
      "ERROR_TITLE"=>"Oh Dear!",
      "ERROR_MSG"=>"We cannot seem to find what you are looking for. It may have 
          moved to a different location or even been removed. "
  );
  
  Config::load_template(array("templates"), "error.twig", $context);
?>