function currentDateLoad(){
    let creatingDate = document.getElementById("creatingDate")
    let actualDate = new Date()
    let currentDate = actualDate.toISOString().split("T")[0]
    let time = actualDate.getHours() + ":" + actualDate.getMinutes()
    creatingDate.innerHTML += currentDate + ", " + time
}

window.addEventListener("load", currentDateLoad)

document.getElementById("uploadFileButton").addEventListener("click",()=>{
    document.getElementById("fileInput").click()
})