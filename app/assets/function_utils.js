/**
 * @param {HTMLButtonElement} el Clicked button
 * @param {boolean} isLoading Show loading element
 */
export function toggleLoading(el, isLoading) {
    if (!el instanceof HTMLButtonElement) {
        throw Error("First parameter el must be as HTMLButtonElement");
    }

    el.disabled = isLoading;
    /** @type {HTMLSpanElement|null} spinner */
    const spinner = el.querySelector(".spinner-into-btn");

    if (spinner) {
        spinner.style.display = isLoading ? "inline-block" : "none";
    }
}
