import bootbox from "bootbox";

window.bootbox = bootbox;

document.addEventListener("DOMContentLoaded", () => {
    // confirm delete comment, post
    document.addEventListener("click", (evt) => {
        const targetClass = evt.target?.classList || undefined;

        if (targetClass.contains("comment-delete") || targetClass.contains("post-delete")) {
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
