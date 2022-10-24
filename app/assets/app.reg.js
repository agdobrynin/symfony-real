import {createPicker} from "picmo";

document.addEventListener("DOMContentLoaded", () => {
    const rootElement = document.querySelector("div#emoji-toolbar");
    const emojiInput = document.querySelector("[name='registration_form[emoji]']");

    function toggle(emojiDiv) {
        const {display} = emojiDiv.style;
        emojiDiv.style.display = display === "none" ? "block" : "none";
    }

    if (rootElement !== null && emojiInput !== null) {
        rootElement.style.display = "none";
        emojiInput.addEventListener("click", () => toggle(rootElement));

        const picker = createPicker({
            rootElement,
            showSearch: true,
            showCategoryTabs: true,
            emojiSize: "1.3em",
            showPreview: false,
            showRecents: false,
            showVariants: false,
        });

        picker.addEventListener('emoji:select', event => {
            document.querySelector("[name='registration_form[emoji]']").value = event.emoji;
            toggle(rootElement);
        });
    }
});
