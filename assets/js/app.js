/*
 * Little Treats Bakery - small shared behaviours
 *  - mobile menu button
 *  - toast messages (dismiss + auto hide)
 *  - confirmation dialog for forms marked with data-confirm
 *  - "Update" button on the cart only enabled after a change
 *  - loading state on submit buttons (prevents double submits)
 */
(function () {
    "use strict";

    /* ---------- Mobile menu ---------- */

    var toggle = document.querySelector(".nav-toggle");
    var nav = document.getElementById("main-nav");

    if (toggle && nav) {

        toggle.addEventListener("click", function () {

            var open = nav.classList.toggle("is-open");

            toggle.setAttribute("aria-expanded", open ? "true" : "false");
            toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");

        });

    }


    /* ---------- Toast ---------- */

    var toast = document.querySelector(".toast");

    if (toast) {

        var hideToast = function () {
            var region = toast.parentNode;
            if (region && region.parentNode) {
                region.parentNode.removeChild(region);
            }
        };

        var close = toast.querySelector(".toast-close");

        if (close) {
            close.addEventListener("click", hideToast);
        }

        setTimeout(hideToast, 7000);

    }


    /* ---------- Confirmation dialog ---------- */

    var dialog = null;
    var pendingForm = null;
    var lastFocus = null;

    function buildDialog() {

        var overlay = document.createElement("div");
        overlay.className = "confirm-overlay";
        overlay.hidden = true;

        overlay.innerHTML =
            '<div class="confirm-box" role="alertdialog" aria-modal="true" aria-labelledby="confirm-text">' +
            '<p id="confirm-text"></p>' +
            '<div class="confirm-actions">' +
            '<button type="button" class="confirm-cancel">Cancel</button>' +
            '<button type="button" class="confirm-ok">Yes, continue</button>' +
            '</div></div>';

        document.body.appendChild(overlay);

        overlay.querySelector(".confirm-cancel").addEventListener("click", closeDialog);

        overlay.querySelector(".confirm-ok").addEventListener("click", function () {

            var form = pendingForm;

            closeDialog();

            if (form) {
                form.setAttribute("data-confirmed", "1");
                if (form.requestSubmit) {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }

        });

        overlay.addEventListener("click", function (event) {
            if (event.target === overlay) {
                closeDialog();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && !overlay.hidden) {
                closeDialog();
            }
        });

        return overlay;
    }

    function openDialog(form, message) {

        if (!dialog) {
            dialog = buildDialog();
        }

        pendingForm = form;
        lastFocus = document.activeElement;

        dialog.querySelector("#confirm-text").textContent = message;
        dialog.hidden = false;
        dialog.querySelector(".confirm-ok").focus();

    }

    function closeDialog() {

        if (dialog) {
            dialog.hidden = true;
        }

        pendingForm = null;

        if (lastFocus && lastFocus.focus) {
            lastFocus.focus();
        }

    }

    document.addEventListener("submit", function (event) {

        var form = event.target;

        var message = form.getAttribute && form.getAttribute("data-confirm");

        if (message && form.getAttribute("data-confirmed") !== "1") {
            event.preventDefault();
            openDialog(form, message);
        }

    }, true);


    /* ---------- Cart: Update button only after a change ---------- */

    document.querySelectorAll(".quantity-form").forEach(function (form) {

        var input = form.querySelector('input[name="quantity"]');
        var button = form.querySelector("button");

        if (!input || !button) {
            return;
        }

        var original = input.value;

        button.disabled = true;

        input.addEventListener("input", function () {
            button.disabled = (input.value === original || input.value === "");
        });

    });


    /* ---------- Loading state on submit ---------- */

    document.addEventListener("submit", function (event) {

        var form = event.target;

        if (event.defaultPrevented) {
            return;
        }

        setTimeout(function () {

            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(function (button) {
                button.classList.add("is-loading");
                button.disabled = true;
            });

        }, 0);

    });

    // Coming back with the browser Back button: make buttons usable again
    window.addEventListener("pageshow", function (event) {

        if (event.persisted) {

            document.querySelectorAll("button.is-loading").forEach(function (button) {
                button.classList.remove("is-loading");
                button.disabled = false;
            });

        }

    });

})();
