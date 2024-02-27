<?php
  require_once '/var/www/html/api/isLogged.php';
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="utf-8"/>
        <link href="/Pictures/icon_alfa.ico" rel="shortcut icon"/>
        <link rel="stylesheet" href="style.css" type="text/css"/>
        <link rel="stylesheet" href="fontello/css/fontello.css" type="text/css"/>
        <script src="NDUS.js" defer></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    </head>

    <body onload="enter()">
        <header>
            <div class="logo">
                <a href="/"><img src="/Pictures/slw-green.svg" class="header_icon"/></a>
                <h1 id="name"></h1>
                <div style="clear: both;"></div>
            </div>
        </header>

        <main>    
            <div id="info"></div>
            <div id="input_div">
                <input id="input" type="text" autocomplite="off" autofocus>
                <div id="user_answer"></div>
            </div>
            <div id="nazistowskie_kropki">
                <button class="nazistowskie_kropki_button" onclick="nazistowskieKropki('Ä')">Ä</button>
                <button class="nazistowskie_kropki_button" onclick="nazistowskieKropki('ä')">ä</button>
                <button class="nazistowskie_kropki_button" onclick="nazistowskieKropki('Ö')">Ö</button>
                <button class="nazistowskie_kropki_button" onclick="nazistowskieKropki('ö')">ö</button>
                <button class="nazistowskie_kropki_button" onclick="nazistowskieKropki('Ü')">Ü</button>
                <button class="nazistowskie_kropki_button" onclick="nazistowskieKropki('ü')">ü</button>
                <button class="nazistowskie_kropki_button" onclick="nazistowskieKropki('ß')">ß</button>
                <div style="clear: both;"></div>
            </div>
            <div style="clear: both;"></div>
            <button type="submit" id="start" onclick="enter()">START</button>
        </main>
    </body>
</html>
