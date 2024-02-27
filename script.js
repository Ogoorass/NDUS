function redirectToNDUS(fileName) {
    window.location.href = "NDUS.php?" + fileName;
}

document.getElementById("my-icon-upload").addEventListener("click", () => {
    document.getElementById("fileToUpload").click();
})

document.getElementById("fileToUpload").onchange = () => {
    document.getElementById("mySubmit").click();
}