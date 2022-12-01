import bootbox from "bootbox";

window.bootbox = bootbox;

document.addEventListener("DOMContentLoaded", () => {
    // confirm delete comment, post
    document.addEventListener("click", (evt) => {
        const targetClass = evt.target?.classList || undefined;

        if (targetClass.contains("anchor-confirm")) {
            evt.preventDefault();

            const href = evt.target.href;
            const {message, locale, title} = evt.target.dataset;

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
