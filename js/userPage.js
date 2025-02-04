async function getUserData() {
    try {
        let response = await fetch('./api/query/user-data')
        if(response.ok){
            let userData = await response.json()
            console.log(userData)
            loadUserDataIn(userData)
        }
    } catch (error) {
        console.log(error)
    }
}

function loadUserDataIn(userData) {
    let emailInput = document.getElementById("emailInput");
    let lastnameInput = document.getElementById('lastname')
    let firstnameInput = document.getElementById('firstname')
    if (!userData) {
        console.error("userData is null or undefined");
        return;
    }

    emailInput.value = userData.email;
    lastnameInput.value = userData.lastname
    firstnameInput.value = userData.firstname
}

async function modifyUserData(){
    let email = document.getElementById('emailInput').value
    let lastname = document.getElementById('lastname').value
    let firstname = document.getElementById('firstname').value
    let password = document.getElementById('password').value
    let newPassword = document.getElementById('new-password').value

    let alertDiv = document.getElementById('alertDiv')
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
            alertDiv.style.display = "flex"
            alertDiv.textContent = valasz.uzenet
        }
        else{
            alertDiv.style.display = "flex"
            alertDiv.style.border = "2px solid #c3e6cb"
            alertDiv.style.color = "#155724"
            alertDiv.style.backgroundColor = "#d4edda"
            alertDiv.textContent = valasz.uzenet
            setTimeout(function(){
                location.reload()
            },1500)
        }
    } catch (error) {
        console.log(error)
    }
}

document.getElementById('modifyUserDataButton').addEventListener('click',modifyUserData)

window.addEventListener('load', getUserData)