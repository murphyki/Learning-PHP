<?php 
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  require_once(DIR_LIB . "/Twig/Autoloader.php");
  
  Twig_Autoloader::register();
  
  $context = array(
      "ERROR_TITLE"=>"Ah Crumbs!",
      "ERROR_MSG"=>"An error occurred on the server while processing your request."
  );
  
  Config::load_template(array("templates"), "error.twig", $context);
?>