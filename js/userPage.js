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

document.addEventListener("DOMContentLoaded", function () {
    const adatmodositasLink = document.querySelector(".navbar a:nth-child(1)");
    const jelszoModositasLink = document.querySelector(".navbar a:nth-child(2)");
    const passwordField = document.querySelector("#password");
    const newPasswordField = document.querySelector("#new-password");
    const emailField = document.querySelector("#emailInput");
    const lastNameField = document.querySelector("#lastname");
    const firstNameField = document.querySelector("#firstname");

    function showUserDataFields() {
        emailField.parentElement.parentElement.style.display = "block";
        lastNameField.parentElement.parentElement.style.display = "block";
        firstNameField.parentElement.parentElement.style.display = "block";
        passwordField.parentElement.parentElement.style.display = "block";
        newPasswordField.parentElement.parentElement.style.display = "none";
        adatmodositasLink.classList.add("active");
        jelszoModositasLink.classList.remove("active");
    }

    function showPasswordFields() {
        emailField.parentElement.parentElement.style.display = "none";
        lastNameField.parentElement.parentElement.style.display = "none";
        firstNameField.parentElement.parentElement.style.display = "none";
        passwordField.parentElement.parentElement.style.display = "block";
        newPasswordField.parentElement.parentElement.style.display = "block";
        jelszoModositasLink.classList.add("active");
        adatmodositasLink.classList.remove("active");
    }

    adatmodositasLink.addEventListener("click", function () {
        showUserDataFields();
    });

    jelszoModositasLink.addEventListener("click", function () {
        showPasswordFields();
    });


    showUserDataFields();

    const style = document.createElement("style");
    style.innerHTML = `
        .navbar a.active {
            font-weight: bold;
            color: #5271ff;
        }
    `;
    document.head.appendChild(style);
});


document.getElementById('modifyUserDataButton').addEventListener('click',modifyUserData)

window.addEventListener('load', getUserData)