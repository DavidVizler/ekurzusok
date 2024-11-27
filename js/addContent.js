let contentType = document.getElementById("typeContent")
contentType.addEventListener("click", ()=>{
    let selectElement = document.querySelector('select');
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let type = selectedOption.value;
    if(type == "feladat"){
        document.getElementById("feladatDiv").style.display = "flex"
        document.getElementById("feladatDiv").style.flexDirection = "column"
        document.getElementById("tananyagDiv").style.display = "none"
    }
    if(type == "tananyag"){
        document.getElementById("tananyagDiv").style.display = "flex"
        document.getElementById("tananyagDiv").style.flexDirection = "column"
        document.getElementById("feladatDiv").style.display = "none"
    }
})
