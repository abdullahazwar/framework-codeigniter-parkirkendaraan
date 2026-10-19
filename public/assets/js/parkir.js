"use strict";

document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll("form[data-submit-lock]").forEach((form) => {
    form.addEventListener("submit", (event) => {
        if (event.defaultPrevented || form.dataset.submitting === "true") {
            event.preventDefault();
            return;
        }
        form.dataset.submitting = "true";
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.dataset.originalText = button.textContent;
            button.disabled = true;
            button.textContent = "Menyimpan...";
        }
    });
});

window.addEventListener("pageshow", () => {
    document.querySelectorAll('form[data-submitting="true"]').forEach((form) => {
        delete form.dataset.submitting;
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = false;
            button.textContent = button.dataset.originalText;
        }
    });
});

document.querySelectorAll("[data-print]").forEach((button) => {
    button.addEventListener("click", () => window.print());
});

const checkout = document.querySelector("form[data-checkout]");
if (checkout) {
    const input = checkout.querySelector('[name="uang_diterima"]');
    const output = document.getElementById("kembalian");
    const formatter = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        maximumFractionDigits: 0,
    });
    const update = () => {
        const paid = Number(input.value);
        const total = Number(checkout.dataset.total);
        output.textContent = formatter.format(Math.max(0, paid - total));
    };
    input.addEventListener("input", update);
    update();
}
