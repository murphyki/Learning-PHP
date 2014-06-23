<?php
    
    $pattern = "/lotto\/\d{4}\/\d{4}_\d{2}_\d{2}$/i";
    $category = "loTTo/2013/2013_05_31";
    var_dump($category);
    echo("<br/>");
    if (preg_match($pattern, $category)) {
        echo("a match");
    } else {
        echo("no match");
    }
?>
