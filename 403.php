<?php 
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  require_once(DIR_LIB . "/Twig/Autoloader.php");
  
  Twig_Autoloader::register();
  
  $context = array(
      "ERROR_TITLE"=>"Whoa!",
      "ERROR_MSG"=>"Did you get lost or not find what you were looking for?"
  );
  
  Config::load_template(array("templates"), "error.twig", $context);
?>