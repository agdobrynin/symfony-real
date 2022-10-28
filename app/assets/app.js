import "jquery/dist/jquery.slim";
import "@popperjs/core/dist/umd/popper.min";
import * as bootstrap from "bootstrap/dist/js/bootstrap.bundle.min";

window.bootstrap = bootstrap;

document.addEventListener("DOMContentLoaded", () => {
    // Popover init
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

    const notificationContainer = document.querySelector("#notification");

    if (notificationContainer) {
        const text = notificationContainer.querySelector("#notification-text");
        let loader = notificationContainer.querySelector("#notification-loader");

        const check = () => {
            $.get("/micro-post/notification/unread-all", (data) => {
                if (!loader.classList.contains("visually-hidden")) {
                    loader.classList.add("visually-hidden");
                }

                const notificationCount = data.all;
                text.textContent = notificationCount;

                if (notificationCount === 0) {
                    text.classList.add("visually-hidden");
                    notificationContainer.classList.add("grayscale");
                } else {
                    text.classList.remove("visually-hidden");
                    notificationContainer.classList.remove("grayscale");
                }

                setTimeout(check, 5000);
            });
        };

        check();
    }
});
