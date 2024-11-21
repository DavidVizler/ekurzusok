let menu = document.getElementById("hamburger-menu")

function clickHandler(){
    menu.classList.toggle("active")
}
menu.addEventListener("click", clickHandler)