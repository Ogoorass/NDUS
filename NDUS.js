let fileName = window.location.search.substring(1);
let fileNameFormatted = "";
for (let x of fileName) {
    if (x == '.') break;
    if (x == '_') fileNameFormatted += " ";
    else fileNameFormatted += x;
}
fileNameFormatted = fileNameFormatted.substring(5);
document.getElementById('name').innerHTML = fileNameFormatted;
let requestJSONfile = new XMLHttpRequest();
requestJSONfile.open("GET", fileName, false);
requestJSONfile.send(null);
let json_data = JSON.parse(requestJSONfile.responseText);

const standard_info = "Plik zawiera: " + json_data.length + " słówek. <span id=\"click\" onclick=\"showMoreInfo()\"><i class=\"icon-info-circled\"></i></span>";

document.addEventListener("keypress", event => {
    if (event.key === "Enter") {
        event.preventDefault();
        document.getElementById("start").click();
    }

});

let random_data = [];
let state = 0;
let popped_data = [];
let good_answears;

function showMoreInfo() {
    let extended_info = "<table><tr><th>Lp.</th><th>Odpowiedź</th><th>Pytanie</th></tr>";

    for (let x = 0; x < json_data.length; x++) {
        extended_info += "<tr><td>" + (x + 1) + "</td><td>" + json_data[x]["n"] + "</td><td>" + json_data[x]["p"] + "</td></tr>";
    }

    extended_info += "</table><div id=\"hide\"><span onclick=\"hideMoreInfo()\">Schowaj</span></div><div class=\"cbt\"></div>";
    document.getElementById('info').innerHTML = extended_info;
    document.getElementById('start').style.visibility = "hidden";
}

function hideMoreInfo() {
    document.getElementById('info').innerHTML = standard_info;
    document.getElementById('start').style.visibility = "visible";
}

function enter() {
    let info = document.getElementById('info');
    let start = document.getElementById('start');
    let input_div = document.getElementById('input_div');
    let input = document.getElementById('input');
    let nazistowskie_kropki = document.getElementById('nazistowskie_kropki');
    let user_answer = document.getElementById('user_answer');

    switch (state) {
        case 0:
            info.innerHTML = standard_info;
            start.innerHTML = "START";
            state++;
        break;
        case 1:
            //end
            randomiseData();
            state++;
            good_answears = 0;
            start.innerHTML = "<i class=\"icon-ok\"></i>";
            enter();
        break;
        case 2:
            //typing
            user_answer.style.display = "none";
            if (random_data == 0) {
                input.style.display = "none";
                info.innerHTML = "Koniec! Odpowiedziałeś dobrze na " + good_answears + " z " + json_data.length + ".";
                start.innerHTML = "<i class=\"icon-ccw\"></i>";
                state = 1;
            }
            popped_data = random_data.pop();
            info.innerHTML = json_data.length - random_data.length + ". " + popped_data["p"];
            input.value = "";
            input.style.display = "block";
            //convenience for small screen device - don't display nazistowskie kropki
            if(window.innerWidth > 500){
                nazistowskie_kropki.style.display = "block";
            }
            start.innerHTML = "<i class=\"icon-ok\"></i>";
            document.getElementById('input').focus(); // have to be here bec
            state++;
        break;
        case 3:
            //validation
            input.style.display = "none";
            nazistowskie_kropki.style.display = "none";
            if (input.value == popped_data["n"]){
                info.innerHTML = "Dobrze!";
                user_answer.innerHTML = "<i>" + input.value + "</i>";
                good_answears++;
            }
            else {
                info.innerHTML = "<span style=\"color: red;\">Źle! Poprawna odpowiedź to:<br><b>" + popped_data["n"] + "</b></span>";
                user_answer.innerHTML = "<span style=\"color: red;\"><i>" + input.value + "</i></span>"
            }

            if (random_data == 0) start.innerHTML = "<i class=\"icon-to-end\"></i>";
            else start.innerHTML = "<i class=\"icon-right-fat\"></i>";

            user_answer.style.display = "block";
            state = 2;
        break;
    }
}

function randomNumber(len) {
    return Math.floor(Math.random() * len);
}


function randomiseData() {

    random_data = [];

    //copy json_data
    let data_to_randomise = json_data.slice();

    while (json_data.length > random_data.length) {
        random_data.push(data_to_randomise.splice(randomNumber(data_to_randomise.length), 1)[0]);
    }

}

function nazistowskieKropki(nazi_char) {
    let input = document.getElementById('input');
    input.value += nazi_char;
    input.focus();
}