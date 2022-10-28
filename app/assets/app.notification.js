import {toggleLoading} from "./function_utils";
import ky from "ky";

document.addEventListener("DOMContentLoaded", () => {

    document.addEventListener("click", (event) => {
        const targetEl = event.target;
        const sendData = [];
        const notificationId = targetEl?.dataset?.notitifationId;

        if (notificationId) {
            toggleLoading(targetEl, true);
            sendData.push(notificationId);

            ky.post("/micro-post/notification/set-seen", {
                headers: {
                    'content-type': 'application/json'
                },
                json: sendData,
            }).json()
                .then(() => {
                    toggleLoading(targetEl, false);
                    targetEl.closest("div.col-notification-item")?.remove();
                })
                .catch((error) => {
                    toggleLoading(targetEl, false);
                    throw Error(error);
                });
        }
    });
});
