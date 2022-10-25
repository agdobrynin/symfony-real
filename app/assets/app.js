import "jquery/dist/jquery.slim";
import "bootstrap/dist/js/bootstrap.min";

document.addEventListener("DOMContentLoaded", () => {
    const notificationContainer = document.querySelector("#notification-main");

    if (notificationContainer) {
        const text = notificationContainer.querySelector("#notification-text");
        let loader = notificationContainer.querySelector("#notification-loader");

        const check = () => {
            $.get("/micro-post/notification/unread-all", (data) => {
                if (loader) {
                    loader.remove();
                }
                text.textContent = data.all;
                setTimeout(check, 5000);
            });
        };

        check();
    }
});
