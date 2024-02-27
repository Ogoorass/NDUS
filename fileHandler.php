<?php

    session_start();

    $target_dir = '/var/www/html/NDUS/json/';
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

    $file_content = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);

    

    if (strlen($file_content) > pow(1024, 2)) {
        //if too big
        $_SESSION["upload_status"] = "Za duży plik";
    }
    elseif (json_decode($file_content) == null || strlen($file_content) == 0) {
        //if not json
        $_SESSION["upload_status"] = "To nie .json :( ";
    }
    elseif (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)){
        //if succeded
        $_SESSION["upload_status"] = "Pomyślnie załadowano " . basename($_FILES["fileToUpload"]["name"]);
    }
    else {
        //if error somewhere
        $_SESSION["upload_status"] = "Wystąpił błąd podczas wysyłania pliku " . basename($_FILES["fileToUpload"]["name"]);
    }

    header("Location: index.php");

?>
