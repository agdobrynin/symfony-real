import bootbox from "bootbox";

window.bootbox = bootbox;

document.addEventListener("DOMContentLoaded", () => {
    // confirm delete comment
    document.addEventListener("click", (evt) => {
        if (evt.target?.classList.contains("comment-delete")) {
            evt.preventDefault();

            const {message, href, locale, title} = evt.target.dataset;

            bootbox.confirm({
                closeButton: false,
                message,
                locale,
                title,
                callback: function (result) {
                    if (result) {
                        window.location = href;
                    }
                }
            })
        }
    });
});
