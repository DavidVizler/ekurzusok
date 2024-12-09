function currentDate(){
    let creatingDate = document.getElementById("creatingDate")
    let actualDate = new Date()
    let currentDate = actualDate.toISOString().split('T')[0];
    creatingDate.innerHTML += currentDate
}

document.getElementById("backToPreviousPage").addEventListener("click",()=>{
    window.history.go(-1)
})

window.addEventListener("load", currentDate)