document.addEventListener("DOMContentLoaded", () => {
    const dropdown = document.querySelector(".dropdown");
    const menu = document.querySelector(".dropdown-menu");

    dropdown.addEventListener("click", (e) => {
        e.stopPropagation(); // หยุดการกระจายอีเวนต์
        menu.style.display = menu.style.display === "block" ? "none" : "block";
    });

    // ซ่อนเมนูเมื่อคลิกที่อื่น
    document.addEventListener("click", () => {
        menu.style.display = "none";
    });
});