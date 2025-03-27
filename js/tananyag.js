function currentDate(){
    let creatingDate = $("creatingDate")
    let actualDate = new Date()
    let currentDate = actualDate.toISOString().split('T')[0];
    creatingDate.innerHTML += currentDate
}

$("backToPreviousPage").addEventListener("click",()=>{
    window.history.go(-1)
})

let adatok;
window.addEventListener('load',async()=>{
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    let reqData = {
        "content_id" : parseInt(tartalomId)
    }
    try {
        let request = await fetch("api/query/content-data",{
            method : "POST",
            headers : {
                "Content-Type" : "application/json"
            },body : JSON.stringify(reqData)
        })
        if(request.ok){
            adatok = await request.json()
            showContentData(adatok)
        }
    } catch (error) {
        console.log(error)
    }
})


function showContentData(adatok){
    let title = $("title")
    let createdDate = $("creatingDate")
    let createUser = $("createrUser")
    let description = $("description")
    title.innerHTML = adatok.title
    createdDate.innerHTML = adatok.published
    createUser.innerHTML = adatok.lastname + " " + adatok.firstname
    description.innerHTML = adatok.description
    if(adatok.owned == 0){
        document.getElementById("modifyBtn").classList.add("disabled")
    }
    if(adatok.archived == 1){
        document.getElementById("modifyBtn").disabled = true
        document.getElementById("modifyBtn").style.opacity = 0.6
        document.getElementById("modifyBtn").style.cursor = "not-allowed"
        document.getElementById("deleteBtn").style.opacity = 0.6
        document.getElementById("deleteBtn").style.cursor = "not-allowed"
        document.getElementById("deleteBtn").disabled = true
    }
}
window.addEventListener("load", currentDate)

function showModal(){
    $("edit-modal").style.display = "flex";
    let title = $("ContentTitle").value = adatok.title
    let description = $("description-input").value = adatok.description
}

function confirmationModal(){
    let alertDiv = $('confirmationModalDiv')
    alertDiv.style.display = "flex"

    let modal_content = create("div", 'modal-content')
    alertDiv.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = "Biztosan törölni akarja a tartalmat?"

    let yes_button = create("button")
    modal_content.appendChild(yes_button)
    yes_button.innerHTML = "Igen"
    yes_button.addEventListener("click", DeleteContent)

    let no_button = create("button")
    modal_content.appendChild(no_button)
    no_button.innerHTML = "Nem"

    no_button.addEventListener("click",()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
}

async function ModifyData() {
    let title = $("ContentTitle").value
    let description = $("description-input").value
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    let reqData = {
        "content_id" : parseInt(tartalomId),
        "title" : title,
        "desc" : description,
        "task" : false,
        "maxpoint" : null,
        "deadline" : null
    }
    try {
        let request = await fetch("api/content/modify-data",{
            method : "POST",
            headers : {"Content-Type" : "application/json"},
            body : JSON.stringify(reqData)
        })
        let response = await request.json()
        if(response.sikeres == true){
            setTimeout(function(){
                location.reload()
            }, 1500)
        }
        else{
            showAlert(response.uzenet)
        }
    } catch (error) {
        console.log(error)
    }
}

async function DeleteContent() {
    let urlParams = getUrlParams();
    let tartalomId = urlParams.get('id');
    try {
        let request = await fetch('api/content/delete',{
            method : 'POST',
            headers : {'Content-Type' : 'application/json'},
            body : JSON.stringify({"content_id" : parseInt(tartalomId)})
        })
        let response = await request.json()
        if(response.sikeres == false){
            showAlert(response.uzenet)
        }
        else{
            window.location.href = document.referrer;
        }
    } catch (error) {
        console.log(error)
    }
}

function showAlert(uzenet){
    let alertDiv = $("alertDiv")
    alertDiv.style.display = "flex"
    alertDiv.innerHTML = uzenet
}

$("save-btn").addEventListener("click",ModifyData)

document.getElementById("modifyBtn").addEventListener("click", showModal);
document.getElementById("deleteBtn").addEventListener("click", confirmationModal)

document.querySelector(".close").addEventListener("click", function () {
    document.getElementById("edit-modal").style.display = "none";
});