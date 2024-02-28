<?php

    session_start();

//    require_once '/var/www/html/api/isLogged.php';    

    //make list of files .json in direcory
    $files_list = array("");
    foreach (str_split(trim(`ls json/*.json`)) as $c) {
        if (strcmp($c, " ") > 0) {
            $files_list[count($files_list) - 1] = $files_list[count($files_list) - 1].$c;
        } 
        else {
            array_push($files_list, "");
        }
    } 

    $files_list_formatted = array();
    for ($x = 0; $x < count($files_list); $x++) {
        array_push($files_list_formatted, str_replace("_", " ", str_replace(".json", "", str_replace("json/", "", $files_list[$x]))));
    }
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8"/>
        <link rel="stylesheet" href="style.css" type="text/css"/>
        <link rel="stylesheet" href="fontello/upload/css/fontello.css" type="text/css"/>
        <script src="script.js" defer></script>
        <link href="/Pictures/icon_alfa.ico" rel="shortcut icon"/>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
        <?php 
            if (isset($_SESSION["upload_status"])) {
                echo "<script defer> alert(\"" . $_SESSION["upload_status"] . "\")</script>";
                unset($_SESSION["upload_status"]);
            }
        ?>
    </head>
    <body>
        <h1>NDUS</h1>
        <div id="my-icon-upload">
            <form action="fileHandler.php" method="post" enctype="multipart/form-data">
                <input type="file" id="fileToUpload" name="fileToUpload" style="display: none;">
                <input type="submit" id="mySubmit" style="display: none;">
            </form>
            <i class="icon-upload"></i>
            <span class="tooltiptext">Załaduj swoje słówka</span>
        </div>
        <br><br><br>
        <h3>Wybierz, które słówka chcesz powtarzać:</h3>
        <br><br>
	<div class="container">
        <div class="boxes">
            <?php
                for ($x = 0; $x < count($files_list); $x++) {
                    echo "<div class=\"box\" onclick=\"redirectToNDUS('" . $files_list[$x] . "')\">" . $files_list_formatted[$x] . "</div>\n";
                }
            ?>
	</div>
	</div>
    </body>
</html>
