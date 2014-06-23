<?php 
  include_once(realpath($_SERVER["DOCUMENT_ROOT"]) . "/Config.php");
  require_once(DIR_LIB . "/Twig/Autoloader.php");
  
  Twig_Autoloader::register();
  
  $context = array(
  );
  
  Config::load_template(array("../templates"), "search.twig", $context);
?>