/**
 * Module for post likes.
 */

import ky from 'ky';
import {toggleLoading} from "./function_utils";

document.addEventListener("DOMContentLoaded", () => {
    document.addEventListener("click", (event) => {
        const likeActionTitleLike = "like";
        const likeActionTitleUnlike = "unlike";
        const likeActionTitlesSupport = [likeActionTitleLike, likeActionTitleUnlike];
        /** @type {HTMLButtonElement} elButtonClicked */
        const elButtonClicked = event.target;
        /** @type {string|undefined} postUuid */
        const postUuid = elButtonClicked.dataset.uuid;
        /** @type {string|undefined} locale */
        const locale = elButtonClicked.dataset.locale || "en";
        /** @type {string|undefined} postUuid */
        const likeActionTitle = elButtonClicked.dataset.action;

        if (postUuid && likeActionTitle && likeActionTitlesSupport.includes(likeActionTitle)
            && elButtonClicked instanceof HTMLButtonElement) {
            const isLikeAction = likeActionTitle === likeActionTitleLike;

            const route = `/micro-post/${locale}/${isLikeAction ? 'like' : 'unlike'}` + `/${postUuid}`;

            toggleLoading(elButtonClicked, true);

            ky.get(route).json()
                .then((data) => {
                    /** @type {HTMLElement|null} counterEl */
                    const counterEl = elButtonClicked.querySelector("sup");
                    /** @type {HTMLElement|null} titleLikeInButton */

                    if (counterEl) {
                        counterEl.innerText = data.count;
                    }

                    const {
                        labelLike = "like", cssClassLike,
                        labelUnlike = "unlike", cssClassUnlike
                    } = elButtonClicked.dataset;

                    const likeUnlikeReg = new RegExp(`(${labelLike}|${labelUnlike})`, "i");

                    elButtonClicked.childNodes.forEach((el) => {
                        if (el.nodeType === 3) {
                            if (likeUnlikeReg.test(el.textContent.trim())) {
                                el.textContent = isLikeAction ? ` ${labelUnlike} ` : ` ${labelLike} `;
                            }
                        }
                    });

                    if (isLikeAction) {
                        elButtonClicked.dataset.action = likeActionTitleUnlike;
                        elButtonClicked.classList.replace(cssClassLike, cssClassUnlike);
                    } else {
                        elButtonClicked.dataset.action = likeActionTitleLike;
                        elButtonClicked.classList.replace(cssClassUnlike, cssClassLike);
                    }

                    toggleLoading(elButtonClicked, false);
                })
                .catch((error) => {
                    toggleLoading(elButtonClicked, false);

                    if (error.response?.status === 401) {
                        error.response.json().then((res) => {
                            if (res.redirect) {
                                window.location = res.redirect;
                            } else {
                                throw Error('Wrong response. Not found key "redirect"');
                            }
                        });
                    }
                });
        }
    });
});
