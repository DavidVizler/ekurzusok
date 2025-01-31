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
    baseInputStatus()
}

function baseInputStatus(){
    let inputs = document.querySelectorAll('input')
    for (const input of inputs) {
        input.disabled = true
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // Keresd meg az összes szerkesztési ikont
    const editIcons = document.querySelectorAll(".edit-icon");

    editIcons.forEach(icon => {
        icon.addEventListener("click", function () {
            // Az ikonhoz tartozó input mező keresése
            let inputField = this.parentElement.querySelector("input");

            if (inputField) {
                if (inputField.disabled) {
                    inputField.disabled = false;
                    inputField.focus();
                } else {
                    inputField.disabled = true; // Letiltás
                }
            }
        });
    });
});



window.addEventListener('load', getUserData)
// window.addEventListener('load', getIconIds)