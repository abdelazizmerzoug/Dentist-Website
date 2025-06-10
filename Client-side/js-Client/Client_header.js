const menuList = document.getElementById("menuList");
menuList.style.maxHeight = "0px";

function toggleMenu() {
    menuList.style.maxHeight = menuList.style.maxHeight === "0px" ? "381px" : "0px";
}
