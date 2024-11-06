let menu=document.getElementById("menu")
// Function to open the popup
function openPopUp() {
   document.getElementById("popup").style.display = "flex";
}

// Function to close the popup
function closePopup() {
   document.getElementById("popup").style.display = "none";
}

function clickHandler(){
   menu.classList.toggle("active");
}
menu.addEventListener("click",clickHandler);

