function currentDate(){
    let creatingDate = document.getElementById("creatingDate")
    let actualDate = new Date()
    let currentDate = actualDate.toISOString().split('T')[0];
    creatingDate.innerHTML += currentDate
}

window.addEventListener("load", currentDate)