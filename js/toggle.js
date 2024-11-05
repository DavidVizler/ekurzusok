let menu=document.getElementById("menu")
function clickHandler(){
   menu.classList.toggle("active");
}
menu.addEventListener("click",clickHandler);