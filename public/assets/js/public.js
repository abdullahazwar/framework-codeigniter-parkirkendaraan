"use strict";

const navigation = document.getElementById("public-navigation");
const toggle = document.querySelector(".nav-toggle");

if (navigation && toggle) {
    const mobile = window.matchMedia("(max-width: 991px)");
    const setOpen = (open, returnFocus = false) => {
        toggle.setAttribute("aria-expanded", String(open));
        toggle.setAttribute("aria-label", open ? "Tutup navigasi" : "Buka navigasi");
        navigation.hidden = mobile.matches && !open;
        if (returnFocus) toggle.focus();
    };
    const syncNavigation = () => {
        toggle.hidden = !mobile.matches;
        setOpen(false);
    };

    toggle.addEventListener("click", () => {
        setOpen(toggle.getAttribute("aria-expanded") !== "true");
    });
    navigation.addEventListener("click", (event) => {
        if (event.target.closest("a") && mobile.matches) setOpen(false, true);
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && toggle.getAttribute("aria-expanded") === "true") {
            setOpen(false, true);
        }
    });
    document.addEventListener("click", (event) => {
        if (!event.target.closest(".public-header") && mobile.matches) setOpen(false);
    });
    document.addEventListener("focusin", (event) => {
        if (!event.target.closest(".public-header") && mobile.matches) setOpen(false);
    });
    mobile.addEventListener("change", syncNavigation);
    syncNavigation();
}
