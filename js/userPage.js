async function getUserData() {
    try {
        let [response, userData] = API.getUserData();
        if(response.ok){
            loadUserDataIn(userData)
        }
        else if (response.status == 401) {
            window.location.href = './login.html';
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

function confirmationModal(){
    let modal = create("div",'modal')
    let alertDiv = $('alertDiv')
    alertDiv.style.display = "block"
    alertDiv.appendChild(modal)

    let modal_content = create("div", 'modal-content')
    modal.appendChild(modal_content)

    let message = create("p")
    modal_content.appendChild(message)
    message.innerHTML = "Biztosan törölni akarja a fiókját?"

    let yes_button = create("button")
    modal_content.appendChild(yes_button)
    yes_button.innerHTML = "Igen"
    yes_button.addEventListener("click", DeleteUserAccount)

    let no_button = create("button")
    modal_content.appendChild(no_button)
    no_button.innerHTML = "Nem"

    no_button.addEventListener("click",()=>{
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

async function DeleteUserAccount() {
    let password = $("password-to-delete").value
    try {
        let request = await fetch('./api/user/delete',{
            method : 'POST',
            headers : {
                'Content-Type' : 'application/json'
            }, body : JSON.stringify({"password" : password})
        })
        let response = await request.json()
        if(response.sikeres){
            location.href = "./index.html"
        }else{
            resultModal(response.uzenet)
        }
    } catch (error) {
        console.log(error)
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const adatmodositasLink = document.querySelector(".navbar a:nth-child(1)");
    const jelszoModositasLink = document.querySelector(".navbar a:nth-child(2)");
    const deleteAccountLink = document.querySelector(".navbar a:nth-child(3)")
    const saveButton = $("modifyUserDataButton");
    const deleteAccountButton = $("deleteUserAccountButton")
    function showDeleteUserAccountFields() {
        document.querySelector(".userDataDiv").style.display = "none";
        document.querySelector(".passwordDiv").style.display = "none";
        document.querySelector(".deleteAccountDiv").style.display = "block";
        
        jelszoModositasLink.classList.remove("active");
        adatmodositasLink.classList.remove("active");
        deleteAccountLink.classList.add("active");
    
        saveButton.style.display = "none";
        deleteAccountButton.style.display = "block";
    }
    
    function showUserDataFields() {
        document.querySelector(".userDataDiv").style.display = "block";
        document.querySelector(".passwordDiv").style.display = "none";
        document.querySelector(".deleteAccountDiv").style.display = "none";
        
        adatmodositasLink.classList.add("active");
        jelszoModositasLink.classList.remove("active");
        deleteAccountLink.classList.remove("active");
    
        saveButton.style.display = "block";
        deleteAccountButton.style.display = "none";
    }
    
    function showPasswordFields() {
        document.querySelector(".userDataDiv").style.display = "none";
        document.querySelector(".passwordDiv").style.display = "block";
        document.querySelector(".deleteAccountDiv").style.display = "none";
        
        jelszoModositasLink.classList.add("active");
        adatmodositasLink.classList.remove("active");
        deleteAccountLink.classList.remove("active");
    
        saveButton.style.display = "block";
        deleteAccountButton.style.display = "none";
    }
    

    adatmodositasLink.addEventListener("click", showUserDataFields);
    jelszoModositasLink.addEventListener("click", showPasswordFields);
    deleteAccountLink.addEventListener("click", showDeleteUserAccountFields)

    saveButton.addEventListener("click", function () {
        if (adatmodositasLink.classList.contains("active")) {
            modifyUserData();
        } else if (jelszoModositasLink.classList.contains("active")) {
            modifyUserPassword();
        }
    });

    deleteAccountButton.addEventListener("click", confirmationModal)

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