async function getUserData() {
    try {
        let response = await fetch('./api/query/user-data')
        if(response.ok){
            let userData = await response.json()
            loadUserDataIn(userData)
        }
    } catch (error) {
        console.log(error)
    }
}

function loadUserDataIn(userData) {
    let emailInput = $("emailInput");
    let lastnameInput = $('lastname')
    let firstnameInput = $('firstname')
    if (!userData) {
        console.error("Nincsenek adatok!");
        return;
    }

    emailInput.value = userData.email;
    lastnameInput.value = userData.lastname
    firstnameInput.value = userData.firstname
}

function resultModal(result){
    let modal = create("div", 'modal')
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "block"
    alertDiv.appendChild(modal)

    let modal_content = create("div", 'modal-content')
    modal.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = result

    let ok_button = create("button")
    modal_content.appendChild(ok_button)
    ok_button.innerHTML = "OK"

    ok_button.addEventListener("click",()=>{
        alertDiv.style.display = "none"
        alertDiv.innerHTML = ""
    })
}

async function modifyUserData(){
    let email = $('emailInput').value
    let lastname = $('lastname').value
    let firstname = $('firstname').value
    let password = $('password').value
    let newPassword = $('new-password').value
    let reqData = {
        "email" : email,
        "lastname" : lastname,
        "firstname" : firstname,
        "password" : password,
        "new_password" : newPassword
    }
    try {
        let response = await fetch('./api/user/modify-data',{
            method : 'POST',
            headers : {
                'Content-Type' : 'application/json'
            }, body : JSON.stringify(reqData)
        })
        let valasz = await response.json()
        if(valasz.sikeres == false){
            resultModal(valasz.uzenet + "!")
        }
        else{
            resultModal(valasz.uzenet + "!")
            setTimeout(function(){
                location.reload()
            },1500)
        }
    } catch (error) {
        console.log(error)
    }
}

async function modifyUserPassword() {
    let password = $('old_password').value
    let newPassword = $('new-password').value
    let checkingNewPassword = $("new-password-again").value
    let reqData;
    if(newPassword == checkingNewPassword){
        reqData = {
            "old_password" : password,
            "new_password" : newPassword
        }
    }else{
        resultModal("A két jelszó nem egyezik")
    }
    
    try {
        let response = await fetch('./api/user/change-password',{
            method : 'POST',
            headers : {
                'Content-Type' : 'application/json'
            }, body : JSON.stringify(reqData)
        })
        let valasz = await response.json()
        console.log(valasz)
        if(valasz.sikeres == false){
            resultModal(valasz.uzenet + "!")
        }
        else{
            resultModal(valasz.uzenet + "!")
            setTimeout(function(){
                location.reload()
            },1500)
        }
    } catch (error) {
        console.log(error)
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const adatmodositasLink = document.querySelector(".navbar a:nth-child(1)");
    const jelszoModositasLink = document.querySelector(".navbar a:nth-child(2)");
    const saveButton = $("modifyUserDataButton");

    function showUserDataFields() {
        document.querySelector(".userDataDiv").style.display = "block";
        document.querySelector(".passwordDiv").style.display = "none";
        adatmodositasLink.classList.add("active");
        jelszoModositasLink.classList.remove("active");
    }

    function showPasswordFields() {
        document.querySelector(".userDataDiv").style.display = "none";
        document.querySelector(".passwordDiv").style.display = "block";
        jelszoModositasLink.classList.add("active");
        adatmodositasLink.classList.remove("active");
    }

    adatmodositasLink.addEventListener("click", showUserDataFields);
    jelszoModositasLink.addEventListener("click", showPasswordFields);

    saveButton.addEventListener("click", function () {
        if (adatmodositasLink.classList.contains("active")) {
            modifyUserData();
        } else if (jelszoModositasLink.classList.contains("active")) {
            modifyUserPassword();
        }
    });

    showUserDataFields();

    const style = create("style");
    style.innerHTML = `
        .navbar a.active {
            font-weight: bold;
            color: #5271ff;
        }
    `;
    document.head.appendChild(style);
});

window.addEventListener('load', getUserData)