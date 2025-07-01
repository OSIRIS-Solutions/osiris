/*
* -----------------------------------------------------------------------------
* OSIRIS JS
* Version: Dev
* Copyright, Julia Koblitz
* Licensed under MIT
* -----------------------------------------------------------------------------
*/

// Namespaced function to check whether a selector is supported by the browser or not
// Original is by Diego Perini, lifted from https://gist.github.com/paulirish/441842
function osirisJS_selectorSupported(selector) {
    var support, link, sheet, doc = document,
        root = doc.documentElement,
        head = root.getElementsByTagName("head")[0],

        impl = doc.implementation || {
            hasFeature: function () {
                return false;
            }
        },

        link = doc.createElement("style");
    link.type = "text/css";

    (head || root).insertBefore(link, (head || root).firstChild);

    sheet = link.sheet || link.styleSheet;

    if (!(sheet && selector)) return false;

    support = impl.hasFeature("CSS2", "") ?
        function (selector) {
            try {
                sheet.insertRule(selector + "{ }", 0);
                sheet.deleteRule(sheet.cssRules.length - 1);
            } catch (e) {
                return false;
            }
            return true;
        } : function (selector) {
            sheet.cssText = selector + " { }";
            return sheet.cssText.length !== 0 && !(/unknown/i).test(sheet.cssText) && sheet.cssText.indexOf(selector) === 0;
        };

    return support(selector);
};


/* Start polyfills */

// Polyfill for Element.matches()
if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
}

// Polyfill for Element.closest()
if (!Element.prototype.closest) {
    Element.prototype.closest = function (s) {
        var el = this;
        do {
            if (el.matches(s)) return el;
            el = el.parentElement || el.parentNode;
        } while (el !== null && el.nodeType === 1);
        return null;
    };
}

// Polyfill for Element.classList (http://purl.eligrey.com/github/classList.js/blob/master/classList.js)
"document" in self && ("classList" in document.createElement("_") && (!document.createElementNS || "classList" in document.createElementNS("http://www.w3.org/2000/svg", "g")) || !function (t) { "use strict"; if ("Element" in t) { var e = "classList", n = "prototype", i = t.Element[n], s = Object, r = String[n].trim || function () { return this.replace(/^\s+|\s+$/g, "") }, o = Array[n].indexOf || function (t) { for (var e = 0, n = this.length; n > e; e++)if (e in this && this[e] === t) return e; return -1 }, c = function (t, e) { this.name = t, this.code = DOMException[t], this.message = e }, a = function (t, e) { if ("" === e) throw new c("SYNTAX_ERR", "The token must not be empty."); if (/\s/.test(e)) throw new c("INVALID_CHARACTER_ERR", "The token must not contain space characters."); return o.call(t, e) }, l = function (t) { for (var e = r.call(t.getAttribute("class") || ""), n = e ? e.split(/\s+/) : [], i = 0, s = n.length; s > i; i++)this.push(n[i]); this._updateClassName = function () { t.setAttribute("class", this.toString()) } }, u = l[n] = [], h = function () { return new l(this) }; if (c[n] = Error[n], u.item = function (t) { return this[t] || null }, u.contains = function (t) { return ~a(this, t + "") }, u.add = function () { var t, e = arguments, n = 0, i = e.length, s = !1; do t = e[n] + "", ~a(this, t) || (this.push(t), s = !0); while (++n < i); s && this._updateClassName() }, u.remove = function () { var t, e, n = arguments, i = 0, s = n.length, r = !1; do for (t = n[i] + "", e = a(this, t); ~e;)this.splice(e, 1), r = !0, e = a(this, t); while (++i < s); r && this._updateClassName() }, u.toggle = function (t, e) { var n = this.contains(t), i = n ? e !== !0 && "remove" : e !== !1 && "add"; return i && this[i](t), e === !0 || e === !1 ? e : !n }, u.replace = function (t, e) { var n = a(t + ""); ~n && (this.splice(n, 1, e), this._updateClassName()) }, u.toString = function () { return this.join(" ") }, s.defineProperty) { var f = { get: h, enumerable: !0, configurable: !0 }; try { s.defineProperty(i, e, f) } catch (p) { void 0 !== p.number && -2146823252 !== p.number || (f.enumerable = !1, s.defineProperty(i, e, f)) } } else s[n].__defineGetter__ && i.__defineGetter__(e, h) } }(self), function () { "use strict"; var t = document.createElement("_"); if (t.classList.add("c1", "c2"), !t.classList.contains("c2")) { var e = function (t) { var e = DOMTokenList.prototype[t]; DOMTokenList.prototype[t] = function (t) { var n, i = arguments.length; for (n = 0; i > n; n++)t = arguments[n], e.call(this, t) } }; e("add"), e("remove") } if (t.classList.toggle("c3", !1), t.classList.contains("c3")) { var n = DOMTokenList.prototype.toggle; DOMTokenList.prototype.toggle = function (t, e) { return 1 in arguments && !this.contains(t) == !e ? e : n.call(this, t) } } "replace" in document.createElement("_").classList || (DOMTokenList.prototype.replace = function (t, e) { var n = this.toString().split(" "), i = n.indexOf(t + ""); ~i && (n = n.slice(i), this.remove.apply(this, n), this.add(e), this.add.apply(this, n.slice(1))) }), t = null }());

// Polyfill for :focus-visible (https://github.com/WICG/focus-visible)
// Only applied if the selector is not supported by the browser natively
if (!osirisJS_selectorSupported(":focus-visible")) {
    !function (e, t) { "object" == typeof exports && "undefined" != typeof module ? t() : "function" == typeof define && define.amd ? define(t) : t() }(0, function () { "use strict"; function e(e) { var t = !0, n = !1, o = null, d = { text: !0, search: !0, url: !0, tel: !0, email: !0, password: !0, number: !0, date: !0, month: !0, week: !0, time: !0, datetime: !0, "datetime-local": !0 }; function i(e) { return !!(e && e !== document && "HTML" !== e.nodeName && "BODY" !== e.nodeName && "classList" in e && "contains" in e.classList) } function s(e) { e.classList.contains("focus-visible") || (e.classList.add("focus-visible"), e.setAttribute("data-focus-visible-added", "")) } function u(e) { t = !1 } function a() { document.addEventListener("mousemove", c), document.addEventListener("mousedown", c), document.addEventListener("mouseup", c), document.addEventListener("pointermove", c), document.addEventListener("pointerdown", c), document.addEventListener("pointerup", c), document.addEventListener("touchmove", c), document.addEventListener("touchstart", c), document.addEventListener("touchend", c) } function c(e) { e.target.nodeName && "html" === e.target.nodeName.toLowerCase() || (t = !1, document.removeEventListener("mousemove", c), document.removeEventListener("mousedown", c), document.removeEventListener("mouseup", c), document.removeEventListener("pointermove", c), document.removeEventListener("pointerdown", c), document.removeEventListener("pointerup", c), document.removeEventListener("touchmove", c), document.removeEventListener("touchstart", c), document.removeEventListener("touchend", c)) } document.addEventListener("keydown", function (n) { n.metaKey || n.altKey || n.ctrlKey || (i(e.activeElement) && s(e.activeElement), t = !0) }, !0), document.addEventListener("mousedown", u, !0), document.addEventListener("pointerdown", u, !0), document.addEventListener("touchstart", u, !0), document.addEventListener("visibilitychange", function (e) { "hidden" === document.visibilityState && (n && (t = !0), a()) }, !0), a(), e.addEventListener("focus", function (e) { var n, o, u; i(e.target) && (t || (n = e.target, o = n.type, "INPUT" === (u = n.tagName) && d[o] && !n.readOnly || "TEXTAREA" === u && !n.readOnly || n.isContentEditable)) && s(e.target) }, !0), e.addEventListener("blur", function (e) { var t; i(e.target) && (e.target.classList.contains("focus-visible") || e.target.hasAttribute("data-focus-visible-added")) && (n = !0, window.clearTimeout(o), o = window.setTimeout(function () { n = !1 }, 100), (t = e.target).hasAttribute("data-focus-visible-added") && (t.classList.remove("focus-visible"), t.removeAttribute("data-focus-visible-added"))) }, !0), e.nodeType === Node.DOCUMENT_FRAGMENT_NODE && e.host ? e.host.setAttribute("data-js-focus-visible", "") : e.nodeType === Node.DOCUMENT_NODE && (document.documentElement.classList.add("js-focus-visible"), document.documentElement.setAttribute("data-js-focus-visible", "")) } if ("undefined" != typeof window && "undefined" != typeof document) { var t; window.applyFocusVisiblePolyfill = e; try { t = new CustomEvent("focus-visible-polyfill-ready") } catch (e) { (t = document.createEvent("CustomEvent")).initCustomEvent("focus-visible-polyfill-ready", !1, !1, {}) } window.dispatchEvent(t) } "undefined" != typeof document && e(document) });
}

/* End polyfills */


/* osirisJS JS core */

var osirisJS = {
    // Getting the required elements
    // Re-initialized once the DOM is loaded (to avoid issues with virtual DOM)
    pageWrapper: document.getElementsByClassName("page-wrapper")[0],
    stickyAlerts: document.getElementsByClassName("sticky-alerts")[0],

    darkModeOn: false, // Also re-initialized once the DOM is loaded (see below)

    // Create cookie
    createCookie: function (name, value, days) {
        var expires;
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
        }
        else {
            expires = "";
        }
        document.cookie = name + "=" + value + expires + "; path=/; SameSite=Strict";
    },

    // Read cookie
    readCookie: function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(";");
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === " ") {
                c = c.substring(1, c.length);
            }
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        return null;
    },

    // Erase cookie
    eraseCookie: function (name) {
        osirisJS.createCookie(name, "", -1);
    },

    // Toggle light/dark mode 
    toggleDarkMode: function () {
        if (document.body.classList.contains("dark-mode")) {
            document.body.classList.remove("dark-mode");
            osirisJS.darkModeOn = false;
            osirisJS.createCookie("osirisJS_preferredMode", "light-mode", 365);
        } else {
            document.body.classList.add("dark-mode");
            osirisJS.darkModeOn = true;
            osirisJS.createCookie("osirisJS_preferredMode", "dark-mode", 365);
        }
    },

    // Get preferred mode
    getPreferredMode: function () {
        if (osirisJS.readCookie("osirisJS_preferredMode")) {
            return osirisJS.readCookie("osirisJS_preferredMode");
        } else {
            return "not-set";
        }
    },

    // Toggles sidebar
    toggleSidebar: function (btn = null) {
        if (osirisJS.pageWrapper) {
            if (btn) btn.classList.add('active');
            if (osirisJS.pageWrapper.getAttribute("data-sidebar-hidden")) {
                osirisJS.pageWrapper.removeAttribute("data-sidebar-hidden");
                osirisJS.pageWrapper.removeAttribute("data-sidebar-first");

            } else {
                if (btn) btn.classList.remove('active');
                osirisJS.pageWrapper.setAttribute("data-sidebar-hidden", "hidden");
            }
        }
    },

    // Deactivate all the dropdown toggles when another one is active
    deactivateAllDropdownToggles: function () {
        var activeDropdownToggles = document.querySelectorAll("[data-toggle='dropdown'].active");
        for (var i = 0; i < activeDropdownToggles.length; i++) {
            activeDropdownToggles[i].classList.remove("active");
            activeDropdownToggles[i].closest(".dropdown").classList.remove("show");
        }
    },

    // Toggle modal (using Javascript)
    toggleModal: function (modalId) {
        var modal = document.getElementById(modalId);

        if (modal) {
            modal.classList.toggle("show");
        }
    },

    // Toggle loader
    toggleLoader: function () {
        var loader = document.querySelector('.loader');

        if (loader) {
            loader.classList.toggle("show");
        }
    },

    /* Code block for handling sticky alerts */

    // Make an ID for an element
    makeId: function (length) {
        var result = "";
        var characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    },

    // Toast an alert (show, fade, dispose)
    toastAlert: function (alertId, timeShown) {
        var alertElement = document.getElementById(alertId);

        // Setting the default timeShown
        if (timeShown === undefined) {
            timeShown = 5000;
        }

        // Alert is only toasted if it does not have the .show class
        if (!alertElement.classList.contains("show")) {
            // Add .block class if it does not exist
            if (!alertElement.classList.contains("block")) {
                alertElement.classList.add("block");
            }

            // Show the alert
            // The 0.25 seconds delay is for the animation
            setTimeout(function () {
                alertElement.classList.add("show");
            }, 250);

            // Wait some time (timeShown + 250) and fade out the alert
            var timeToFade = timeShown + 250;
            setTimeout(function () {
                alertElement.classList.add("fade");
            }, timeToFade);

            // Wait some more time (timeToFade + 500) and dispose the alert (by removing the .block class)
            // Again, the extra delay is for the animation
            // Remove the .show and .fade classes (so the alert can be toasted again)
            var timeToDestroy = timeToFade + 500;
            setTimeout(function () {
                alertElement.classList.remove("block");
                alertElement.classList.remove("show");
                alertElement.classList.remove("fade");
            }, timeToDestroy);
        }
    },

    // Create a sticky alert, display it, and then remove it
    initStickyAlert: function (param) {
        // Setting the variables from the param
        var content = ("content" in param) ? param.content : "";
        var title = ("title" in param) ? param.title : "";
        var alertType = ("alertType" in param) ? param.alertType : "";
        var fillType = ("filled" in param) ? param.filled : false;
        var hasDismissButton = ("hasDismissButton" in param) ? param.hasDismissButton : true;
        var timeShown = ("timeShown" in param) ? param.timeShown : 5000;

        // Create the alert element
        var alertElement = document.createElement("div");

        // Set ID to the alert element
        alertElement.setAttribute("id", osirisJS.makeId(6));

        // Add the title
        if (title) {
            content = "<h4 class='title'>" + title + "</h4>" + content;
        }

        // Add the classes to the alert element
        alertElement.classList.add("alert");
        if (alertType) {
            alertElement.classList.add(alertType);
        }
        if (fillType) {
            alertElement.classList.add('filled');
        }

        // Add the close button to the content (if required)
        if (hasDismissButton) {
            content = "<button class='close' data-dismiss='alert' type='button' aria-label='Close'><span aria-hidden='true'>&times;</span></button>" + content;
        }

        // Add the content to the alert element
        alertElement.innerHTML = content;

        // Append the alert element to the sticky alerts
        if (osirisJS.stickyAlerts === undefined) {
            osirisJS.stickyAlerts = document.getElementsByClassName("sticky-alerts")[0]
        }
        osirisJS.stickyAlerts.insertBefore(alertElement, osirisJS.stickyAlerts.childNodes[0]);

        // Toast the alert
        osirisJS.toastAlert(alertElement.getAttribute("id"), timeShown);
    },

    /* End code block for handling sticky alerts */

    // Click handler that can be overridden by users if needed
    clickHandler: function (event) { },

    // Keydown handler that can be overridden by users if needed
    keydownHandler: function (event) { },

    // Function for binding the input value
    bindInputValue: function (inputElement) {
        if (inputElement.getAttribute("data-target")) {
            var targetElementIDs = inputElement.getAttribute("data-target").replace(/\s+/g, "").split(",");
            var targetElement;

            for (var i = 0; i < targetElementIDs.length; i++) {
                targetElement = document.getElementById(targetElementIDs[i]);
                if (targetElement) {
                    if (targetElement instanceof HTMLInputElement) {
                        targetElement.value = inputElement.value;
                    } else {
                        targetElement.innerText = inputElement.value;
                    }
                }
            }
        }
    },

    // For attaching the bind input value function (meant to be called when an event listener is attached)
    callBindInputValueForAttachment: function (event) {
        osirisJS.bindInputValue(event.target);
    },
}


/* Things done once the DOM is loaded */

function osirisJSOnDOMContentLoaded() {
    // Re-initializing the required elements (to avoid issues with virtual DOM)
    if (!osirisJS.pageWrapper) {
        osirisJS.pageWrapper = document.getElementsByClassName("page-wrapper")[0];
    }
    if (!osirisJS.stickyAlerts) {
        osirisJS.stickyAlerts = document.getElementsByClassName("sticky-alerts")[0];
    }

    // Hiding sidebar on first load on small screens 
    // add data-sidebar-first as indicator to hide transitions
    if (document.documentElement.clientWidth <= 992) {
        if (osirisJS.pageWrapper) {
            osirisJS.pageWrapper.setAttribute("data-sidebar-hidden", "hidden");
            osirisJS.pageWrapper.setAttribute("data-sidebar-first", "first");
        }
    }
    console.log(document.documentElement.clientWidth);

    // Adding the click event listener
    document.addEventListener(
        "click",
        function (event) {
            var eventCopy = event;
            var target = event.target;

            // Handle clicks on dropdown toggles
            if (target.matches("[data-toggle='dropdown']") || target.matches("[data-toggle='dropdown'] *")) {
                if (target.matches("[data-toggle='dropdown'] *")) {
                    target = target.closest("[data-toggle='dropdown']");
                }
                if (target.classList.contains("active")) {
                    target.classList.remove("active");
                    target.closest(".dropdown").classList.remove("show");
                } else {
                    osirisJS.deactivateAllDropdownToggles();
                    target.classList.add("active");
                    target.closest(".dropdown").classList.add("show");
                }
            } else {
                if (!target.matches(".dropdown-menu *")) {
                    osirisJS.deactivateAllDropdownToggles();
                }
            }

            // Handle clicks on alert dismiss buttons
            if (target.matches(".alert [data-dismiss='alert']") || target.matches(".alert [data-dismiss='alert'] *")) {
                if (target.matches(".alert [data-dismiss='alert'] *")) {
                    target = target.closest(".alert [data-dismiss='alert']");
                }
                target.parentNode.classList.add("dispose");
            }

            // Handle clicks on modal toggles
            if (target.matches("[data-toggle='modal']") || target.matches("[data-toggle='modal'] *")) {
                if (target.matches("[data-toggle='modal'] *")) {
                    target = target.closest("[data-toggle='modal']");
                }
                var targetModal = document.getElementById(target.getAttribute("data-target"));
                if (targetModal) {
                    if (targetModal.classList.contains("modal")) {
                        osirisJS.toggleModal(target.getAttribute("data-target"));
                    }
                }
            }

            // Handle clicks on modal dismiss buttons
            if (target.matches(".modal [data-dismiss='modal']") || target.matches(".modal [data-dismiss='modal'] *")) {
                if (target.matches(".modal [data-dismiss='modal'] *")) {
                    target = target.closest(".modal [data-dismiss='modal']");
                }
                target.closest(".modal").classList.remove("show");
            }

            // Handle clicks on modal overlays
            if (target.matches(".modal-dialog")) {
                var parentModal = target.closest(".modal");

                if (!parentModal.getAttribute("data-overlay-dismissal-disabled")) {
                    if (parentModal.classList.contains("show")) {
                        parentModal.classList.remove("show");
                    } else {
                        window.location.hash = "#close-modal";
                    }
                }
            }

            // Handle clicks on password show/hide toggles
            if (target.matches("[data-toggle='password']") || target.matches("[data-toggle='password'] *")) {
                if (target.matches("[data-toggle='password'] *")) {
                    target = target.closest("[data-toggle='password']");
                }
                var targetInput = document.getElementById(target.getAttribute("data-target"));
                if (targetInput) {
                    if (targetInput.getAttribute("type") == "password") {
                        targetInput.type = "text";
                        target.classList.add("target-input-type-text");
                    } else {
                        targetInput.type = "password";
                        target.classList.remove("target-input-type-text");
                    }
                }
            }

            // Handle clicks on number step up buttons
            if (target.matches("[data-trigger='number-step-up']") || target.matches("[data-trigger='number-step-up'] *")) {
                if (target.matches("[data-trigger='number-step-up'] *")) {
                    target = target.closest("[data-trigger='number-step-up']");
                }
                var targetInput = document.getElementById(target.getAttribute("data-target"));
                if (targetInput) {
                    if (!document.documentMode) {
                        // Not IE, because document.documentMode is undefined
                        // That property is only available in IE
                        targetInput.stepUp();
                    }
                    else {
                        // In IE, range inputs have the stepUp() and stepDown() functions
                        // Therefore, the following hack implements those functions for number inputs
                        var cloneInput = targetInput.cloneNode(false);
                        cloneInput.setAttribute("type", "range");
                        try {
                            cloneInput.stepUp();
                        }
                        catch (e) { }
                        targetInput.value = cloneInput.value;
                    }
                }
            }

            // Handle clicks on number step down buttons
            if (target.matches("[data-trigger='number-step-down']") || target.matches("[data-trigger='number-step-down'] *")) {
                if (target.matches("[data-trigger='number-step-down'] *")) {
                    target = target.closest("[data-trigger='number-step-down']");
                }
                var targetInput = document.getElementById(target.getAttribute("data-target"));
                if (targetInput) {
                    if (!document.documentMode) {
                        // Not IE, because document.documentMode is undefined
                        // That property is only available in IE
                        targetInput.stepDown();
                    }
                    else {
                        // In IE, range inputs have the stepUp() and stepDown() functions
                        // Therefore, the following hack implements those functions for number inputs
                        var cloneInput = targetInput.cloneNode(false);
                        cloneInput.setAttribute("type", "range");
                        try {
                            cloneInput.stepDown();
                        }
                        catch (e) { }
                        targetInput.value = cloneInput.value;
                    }
                }
            }

            // Call the click handler method to handle any logic set by the user in their projects to handle clicks
            osirisJS.clickHandler(eventCopy);
        },
        false
    );

    // Adding the key down event listener (for shortcuts and accessibility)
    document.addEventListener(
        "keydown",
        function (event) {
            var eventCopy = event;

            // Shortcuts are triggered only if no input, textarea, or select has focus,
            // If the control key or command key is not pressed down,
            // And if the enabling data attribute is present on the DOM's body
            if (!(document.querySelector("input:focus") || document.querySelector("textarea:focus") || document.querySelector("select:focus"))) {
                event = event || window.event;

                if (!(event.ctrlKey || event.metaKey)) {
                    // Toggle sidebar when [shift] + [S] keys are pressed
                    if (document.body.getAttribute("data-sidebar-shortcut-enabled")) {
                        if (event.shiftKey && event.which == 83) {
                            // Variable to store whether a modal is open or not
                            var modalOpen = false;

                            // Hash exists, so we check if it belongs to a modal
                            if (window.location.hash) {
                                var hash = window.location.hash.substring(1);
                                var elem = document.getElementById(hash);
                                if (elem) {
                                    if (elem.classList.contains("modal")) {
                                        modalOpen = true;
                                    }
                                }
                            }
                            // Check for a modal with the .show class
                            if (document.querySelector(".modal.show")) {
                                modalOpen = true;
                            }

                            // This shortcut works only if no modal is open
                            if (!modalOpen) {
                                osirisJS.toggleSidebar();
                                event.preventDefault();
                            }
                        }
                    }

                    // Toggle dark mode when [shift] + [D] keys are pressed
                    if (document.body.getAttribute("data-dm-shortcut-enabled")) {
                        if (event.shiftKey && event.which == 68) {
                            osirisJS.toggleDarkMode();
                            event.preventDefault();
                        }
                    }
                }
            }

            // Handling other keydown events
            if (event.which === 27) {
                // Close dropdown menu (if one is open) when [esc] key is pressed
                if (document.querySelector("[data-toggle='dropdown'].active")) {
                    var elem = document.querySelector("[data-toggle='dropdown'].active");
                    elem.classList.remove("active");
                    elem.closest(".dropdown").classList.remove("show");
                    event.preventDefault();
                }
                // Close modal (if one is open, and if no dropdown menu is open) when [esc] key is pressed
                // Conditional on dropdowns so that dropdowns on modals can be closed with the keyboard without closing the modal
                else {
                    // Hash exists, so we check if it belongs to a modal
                    if (window.location.hash) {
                        var hash = window.location.hash.substring(1);
                        var elem = document.getElementById(hash);
                        if (elem) {
                            if (elem.classList.contains("modal")) {
                                if (!elem.getAttribute("data-esc-dismissal-disabled")) {
                                    window.location.hash = "#";
                                    event.preventDefault();
                                }
                            }
                        }
                    }
                    // Check for a modal with the .show class
                    if (document.querySelector(".modal.show")) {
                        var elem = document.querySelector(".modal.show");
                        if (!elem.getAttribute("data-esc-dismissal-disabled")) {
                            elem.classList.remove("show");
                            event.preventDefault();
                        }
                    }
                }
            }

            // Call the keydown handler method to handle any logic set by the user in their projects to handle keydown events
            osirisJS.keydownHandler(eventCopy);
        }
    );

    // Handling custom file inputs
    var osirisJSCustomFileInputs = document.querySelectorAll(".custom-file input");
    for (var i = 0; i < osirisJSCustomFileInputs.length; i++) {
        var customFile = osirisJSCustomFileInputs[i];
        // Create file name container element, add the class name, and set default value
        // Append it to the custom file element
        var fileNamesContainer = document.createElement("div");
        fileNamesContainer.classList.add("file-names");
        var dataDefaultValue = customFile.getAttribute("data-default-value");
        if (dataDefaultValue) {
            fileNamesContainer.innerHTML = dataDefaultValue;
        } else {
            fileNamesContainer.innerHTML = "No file chosen";
        }
        customFile.parentNode.appendChild(fileNamesContainer);

        // Add the event listener that will update the contents of the file name container element on change
        customFile.addEventListener(
            "change",
            function (event) {
                fileNamesContainer = event.target.parentNode.querySelector(".file-names");
                if (event.target.files.length === 1) {
                    fileNamesContainer.innerHTML = event.target.files[0].name;
                } else if (event.target.files.length > 1) {
                    fileNamesContainer.innerHTML = event.target.files.length + " files";
                } else {
                    fileNamesContainer.innerHTML = "No file chosen";
                }
            }
        );
    }

    // Setting the initial value on load
    // Adding the event listeners for binding the value
    // Only for elements with the attribute
    // The double event listener is for cross-browser compatibility
    // Mainly, IE does not register the input event, so change must be used
    var osirisJSElemsBindValue = document.querySelectorAll("[data-bind-value]");
    for (var i = 0; i < osirisJSElemsBindValue.length; i++) {
        osirisJS.bindInputValue(osirisJSElemsBindValue[i]);
        osirisJSElemsBindValue[i].addEventListener(
            "input", osirisJS.callBindInputValueForAttachment, false
        );
        osirisJSElemsBindValue[i].addEventListener(
            "change", osirisJS.callBindInputValueForAttachment, false
        );
    }

    // Adding the .with-transitions class to the page-wrapper so that transitions are enabled
    // This way, the weird bug on Chrome is avoided, where the transitions run on load
    if (osirisJS.pageWrapper) {
        osirisJS.pageWrapper.classList.add("with-transitions");
    }
}


function inViewport(el) {
    var elH = el.offsetHeight,
        H = window.innerHeight,
        r = el.getBoundingClientRect(),
        t = r.top,
        b = r.bottom;
    return Math.max(0, t > 0 ? Math.min(elH, H - t) : Math.min(b, H));
}

function adjustPageNav() {
    var pagenav = document.querySelector('.on-this-page-nav')
    if (pagenav === null || pagenav.length === 0) {
        function adjustPageNav() { return; }
        return;
    }

    // set height of pagenav
    // var wrapper = document //.querySelector('.content-wrapper')
    var footer = document.querySelector('.page-footer')
    var navbarTopHeight = document.querySelector('.navbar-top').offsetHeight
    var navbarBottomHeight = document.querySelector('.navbar-bottom').offsetHeight
    var navbarHeight = navbarTopHeight - Math.min(navbarTopHeight, window.scrollY) + navbarBottomHeight
    // console.log(navbarHeight);
    // var offset = wrapper.scrollTop + wrapper.offsetHeight
    // var offset = window.scrollY
    // var footerVisible = offset - footer.offsetTop
    // footerVisible = Math.max(footerVisible, 0)
    // console.log(offset, footer.offsetTop);

    var footerVisible = inViewport(footer);

    var m = 10 + footerVisible + navbarHeight;
    var style = ""

    var titlebar = document.querySelector('.content-wrapper>.title-bar')
    //
    if (titlebar !== null && titlebar.length !== 0) {
        m += titlebar.offsetHeight;
        style = "top: calc(var(--navbar-height) + " + titlebar.offsetHeight + "px - 1rem); "
    }

    style += "max-height: calc(100vh - " + m + "px);"
    pagenav.style = style
    // set current position

}

// Call the function when the DOM is loaded
document.addEventListener("DOMContentLoaded", osirisJSOnDOMContentLoaded);
window.addEventListener("load", adjustPageNav);
document.addEventListener('scroll', adjustPageNav)


function objectifyForm(el) {
    var formArray = $(el).serializeArray();
    //serialize data function
    var returnArray = {};
    for (var i = 0; i < formArray.length; i++) {
        returnArray[formArray[i]['name']] = formArray[i]['value'];
    }
    return returnArray;
}


function _create(data = {}) {
    $('.loader').addClass('show')
    $.ajax({
        type: "POST",
        data: {
            values: data
        },
        dataType: "html",
        url: ROOTPATH + '/create',
        success: function (response) {
            $('.loader').removeClass('show')

            toastSuccess(response)
            $('#result').html(response)
        },
        error: function (response) {
            $('.loader').removeClass('show')
            toastError(response.responseText)
        }
    })
}

function _update(id, data = {}) {
    $('.loader').addClass('show')
    $.ajax({
        type: "POST",
        data: {
            values: data
        },
        dataType: "html",
        url: ROOTPATH + '/update/' + id,
        success: function (response) {
            $('.loader').removeClass('show')

            toastSuccess("Updated " + response.updated + " datasets.")
            // $('#result').html(response)
        },
        error: function (response) {
            $('.loader').removeClass('show')
            toastError(response.responseText)
        }
    })
}

function _updateUser(id, data = {}) {
    $('.loader').addClass('show')
    $.ajax({
        type: "POST",
        data: {
            values: data
        },
        dataType: "html",
        url: ROOTPATH + '/update-user/' + id,
        success: function (response) {
            $('.loader').removeClass('show')
            console.log(response);
            toastSuccess("Updated " + response.updated + " datasets.")
            // $('#result').html(response)
        },
        error: function (response) {
            $('.loader').removeClass('show')
            toastError(response.responseText)
        }
    })
}

function _approve(id, approval) {
    $('.loader').addClass('show')
    $.ajax({
        type: "POST",
        data: {
            approval: approval
        },
        dataType: "html",
        url: ROOTPATH + '/crud/activities/approve/' + id,
        success: function (response) {
            $('.loader').removeClass('show')
            let n_notifications = parseInt($('span.notification').text()) - 1
            console.log(n_notifications);
            if (n_notifications >= 0) {
                $('span.notification').text(n_notifications)
            }
            
            var loc = location.pathname.split('/')
            if (loc[loc.length - 1] == "issues") {
                $('#tr-' + id).remove()
                return;
            };

            if (approval == 1) {
                $('#approve-' + id).remove()
                toastSuccess('Approved')
            }
            if (approval == 2) {
                location.reload()
            }
            if (approval == 3) {
                $('#tr-' + id).remove()
                toastSuccess('Removed activity')
            }
            
            // toastSuccess("Updated " + response.updated + " datasets.")
            // $('#result').html(response)
        },
        error: function (response) {
            $('.loader').removeClass('show')
            toastError(response.responseText)
        }
    })
}

function _delete(id) {
    $('.loader').addClass('show')
    $.ajax({
        type: "POST",
        dataType: "json",
        url: ROOTPATH + '/delete/' + id,
        success: function (response) {
            $('.loader').removeClass('show')

            console.log(response);
            toastSuccess("Deleted " + response.deleted + " datasets.")
            // $('#'+id).remove();
            $('#' + id).fadeOut();
        },
        error: function (response) {
            $('.loader').removeClass('show')
            toastError(response.responseText)
        }
    })
}


function prependRow(trcontent) {
    var table = $('#activity-table tbody')
    var tr = $('<tr>').css('display', 'none')
    tr.html(trcontent)
    table.prepend(tr)
    tr.fadeIn()
}


function initActivities(selector, data = {}) {
    if ($(selector).length === 0) {
        console.warn('No activities table found with selector:', selector);
        return;
    }
    return $(selector).DataTable({
        "ajax": {
            "url": ROOTPATH + '/api/all-activities',
            "data": data,
            dataSrc: 'data'
        },
        deferRender: true,
        buttons: [
            {
                extend: 'copyHtml5',
                exportOptions: {
                    columns: [3]
                },
                className: 'btn small',
                text: `<i class="ph ph-clipboard"></i> ${lang('Copy', 'Kopieren')}`,
            },
            {
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [5, 3, 4, 6, 7, 8, 9],
                    format: {
                        header: function (data, columnIdx) {
                            // eigene Header-Texte definieren
                            const customHeaders = {
                                0: lang('Icon', 'Icon'),
                                1: lang('Activity', 'Aktivität'),
                                2: lang('Links', 'Links'),
                                3: lang('Formatted text', 'Formatierter Text'),
                                4: lang('Start date', 'Startdatum'),
                                5: lang('Type', 'Typ'),
                                6: lang('Subtype', 'Untertyp'),
                                7: lang('Title', 'Titel'),
                                8: lang('Authors', 'Autoren'),
                                9: lang('Year', 'Jahr')
                            };
                            return customHeaders[columnIdx] || data;
                        }
                    }
                },
                className: 'btn small',
                title: 'OSIRIS_activities',
                text: `<i class="ph ph-file-xls"></i> ${lang('Excel', 'Excel')}`,
            }
        ],
        dom: 'fBrtip',
        pageLength: 5,
        columnDefs: [
            {
                targets: 0,
                data: 'icon',
                // className: 'w-50'
            },
            {
                targets: 1,
                data: 'activity'
            },
            {
                targets: 2,
                data: 'links',
                className: 'unbreakable'
            },
            {
                targets: 3,
                data: 'search-text',
                searchable: true,
                visible: false,
                header: 'Test'
            },
            {
                targets: 4,
                data: 'start',
                searchable: true,
                visible: false,
            },
            {
                targets: 5,
                data: 'raw_type',
                searchable: true,
                visible: false,
            },
            {
                targets: 6,
                data: 'raw_subtype',
                searchable: true,
                visible: false,
            },
            {
                targets: 7,
                data: 'title',
                searchable: true,
                visible: false,
            },
            {
                targets: 8,
                data: 'authors',
                searchable: true,
                visible: false,
            },
            {
                targets: 9,
                data: 'year',
                searchable: true,
                visible: false,
            },
        ],
        "order": [
            [4, 'desc'],
            // [0, 'asc']
        ]
    });
}



function initProjects(selector, data = {}) {
    data['formatted'] = true;
    $(selector).DataTable({
        ajax: {
            url: ROOTPATH + '/api/projects',
            data: data
        },
        type: 'GET',
        dom: 'frtipP',
        columns: [{
            className: 'dt-control',
            orderable: false,
            data: null,
            defaultContent: ''
        },
        {
            data: 'name',
            render: function (data, type, row) {
                return `<a href="${ROOTPATH}/projects/view/${row.id}">${data}</a>`
            }
        },
        {
            data: 'type',
            render: function (data) {
                if (data == 'Eigenfinanziert') {
                    return `<span class="badge text-signal">
                    <i class="ph ph-piggy-bank"></i>&nbsp;${lang('Self-funded', 'Eigenfinanziert')}
                    </span>`
                }
                if (data == 'Stipendium') {
                    return `<span class="badge text-success no-wrap">
                    <i class="ph ph-tip-jar"></i>&nbsp;${lang('Stipendiate', 'Stipendium')}
                    </span>`
                }
                if (data == 'Drittmittel') {
                    return `<span class="badge text-danger">
                    <i class="ph ph-hand-coins"></i>&nbsp;${lang('Third-party funded', 'Drittmittel')}
                    </span>`
                }
                if (data == 'Teilprojekt') {
                    return `<span class="badge text-danger">
                    <i class="ph ph-hand-coins"></i>&nbsp;${lang('Subproject', 'Teilprojekt')}
                    </span>`
                }
                else {
                    return data;
                }
            }
        },
        {
            data: 'funder', render: function (data, type, row) {
                if (!data && row.scholarship) return row.scholarship;
                return data;
            }
        },
        {
            data: 'date_range', render: function (data, type, row) {
                return `<span class="hidden">${row.start}</span>
                    ${data}`

            }
        },
            // {
            //     data: 'role', render: function (data) {
            //         if (data == 'coordinator') {
            //             return `<span class="badge text-signal">
            //             <i class="ph ph-crown-simple"></i>
            //             ${lang('Coordinator', 'Koordinator')}
            //             </span>`
            //         }
            //         if (data == 'associated') {
            //             return `<span class="badge text-success">
            //             <i class="ph ph-address-book"></i>
            //             ${lang('Associated', 'Beteiligt')}
            //             </span>`
            //         }
            //         return `<span class="badge text-muted">
            //             <i class="ph ph-handshake"></i>
            //             ${lang('Partner')}
            //             </span>`
            //     }
            // },
        ],
        order: [
            [4, 'desc']
        ]
    });
}



function impactfactors(containerID, canvasID, data = {}) {
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/impact-factor-hist",
        data: data,
        dataType: "json",
        success: function (response) {
            console.log(response);
            var container = document.getElementById(containerID)
            if (response.count == 0) {
                container.classList.add('hidden')
                return;
            }
            var ctx = document.getElementById(canvasID)
            var data = response.data;

            var labels = data.labels;
            var colors = [
                '#006EB795',
            ]
            var i = 0

            console.log(labels);
            var data = {
                type: 'bar',
                options: {
                    plugins: {
                        legend: {
                            display: false,
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    if (!items.length) {
                                        return '';
                                    }
                                    const item = items[0];
                                    const x = item.parsed.x;
                                    const min = x;
                                    const max = x + 1;
                                    return `IF: ${min} - ${max}`;
                                }
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'linear',
                            ticks: {
                                stepSize: 1
                            },
                            stacked: true,
                            title: {
                                display: true,
                                text: lang('Impact factor', 'Impact factor')
                            },
                        },
                        y: {
                            title: {
                                display: true,
                                text: lang('Number of publications', 'Anzahl Publikationen')
                            },
                            ticks: {
                                callback: function (value, index, ticks) {
                                    // only show full numbers
                                    if (Number.isInteger(value)) {
                                        return value
                                    }
                                    return "";
                                }
                            }
                        }
                    },
                },
                data: {
                    labels: data.x,
                    datasets: [{
                        data: data.y,
                        backgroundColor: colors[i++],
                        borderWidth: 1,
                        borderColor: '#464646',
                        borderRadius: 4
                    },],
                }
            }


            console.log(data);
            var myChart = new Chart(ctx, data);
        },
        error: function (response) {
            console.log(response);
        }
    });
}




function authorrole(containerID, canvasID, data = {}) {
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/author-role",
        data: data,
        dataType: "json",
        success: function (response) {
            console.log(response);
            var container = document.getElementById(containerID)
            if (response.count == 0) {
                container.classList.add('hidden')
                return;
            }
            var data = response.data;
            var ctx = document.getElementById(canvasID)
            var myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: '# of Scientists',
                        data: data.y,
                        backgroundColor: data.colors,
                        borderColor: '#464646', //'',
                        borderWidth: 1,
                    }]
                },
                plugins: [ChartDataLabels],
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            display: true,
                        },
                        title: {
                            display: false,
                            text: 'Scientists approvation'
                        },
                        datalabels: {
                            color: 'black',
                            // anchor: 'end',
                            // align: 'end',
                            // offset: 10,
                            font: {
                                size: 20
                            }
                        }
                    },
                }
            });
        },
        error: function (response) {
            console.log(response);
        }
    });
}

function activitiesChart(containerID, canvasID, data = {}) {
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/activity-chart",
        data: data,
        dataType: "json",
        success: function (response) {
            console.log(response);
            var container = document.getElementById(containerID)
            if (response.count == 0) {
                container.classList.add('hidden')
                return;
            }

            var dataset = response.data;

            var ctx = document.getElementById(canvasID)

            var data = {
                type: 'bar',
                options: {
                    plugins: {
                        title: {
                            display: false,
                            text: 'All activities'
                        },
                        legend: {
                            display: true,
                        }
                    },
                    responsive: true,
                    scales: {
                        x: {
                            stacked: false,
                            title: {
                                display: true,
                                text: lang('Years', 'Jahre')
                            }
                        },
                        y: {
                            stacked: false,
                            ticks: {
                                callback: function (value, index, ticks) {
                                    // only show full numbers
                                    if (Number.isInteger(value)) {
                                        return value
                                    }
                                    return "";
                                }
                            },
                            title: {
                                display: true,
                                text: lang('Number of activities', 'Anzahl der Aktivitäten')
                            }
                        }
                    },
                    maintainAspectRatio: false,
                    onClick: (e) => {
                        const canvasPosition = Chart.helpers.getRelativePosition(e, activityChart);
                        // Substitute the appropriate scale IDs
                        const dataX = activityChart.scales.x.getValueForPixel(canvasPosition.x);
                        const dataY = activityChart.scales.y.getValueForPixel(canvasPosition.y);
                        window.location = ROOTPATH + "/my-year/" + CURRENT_USER + "?year=" + dataset.labels[dataX]
                    }
                },
                data: {
                    labels: dataset.labels,
                    datasets: dataset.data,
                    // grouped:true
                },
            }


            console.log(data);
            var activityChart = new Chart(ctx, data);
        },
        error: function (response) {
            console.log(response);
        }
    });
}

function projectTimeline(selector, data = {}) {
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/project-timeline",
        data: data,
        dataType: "json",
        success: function (response) {
            console.log(response);
            var events = []

            const CURRENT_YEAR = new Date().getFullYear();
            var startyear = CURRENT_YEAR
            var endyear = CURRENT_YEAR

            response.data.forEach(element => {
                var s = element.start
                var start = new Date(s.year, s.month, s.day)
                if (start.getFullYear() < startyear)
                    startyear = start.getFullYear()

                var e = element.end
                var end = new Date(e.year, e.month, e.day)
                if (end.getFullYear() > endyear)
                    endyear = end.getFullYear()

                events.push({
                    startdate: start,
                    enddate: end,
                    title: element.name,
                    role: element.persons.role,
                    funder: element.funder ?? element.scholarship
                })
            });

            var radius = 3,
                distance = radius * 2 + 2

            var margin = {
                top: 8,
                right: 25,
                bottom: 30,
                left: 25
            },
                width = 600,
                // height = (distance * types.length) + margin.top + margin.bottom;
                height = (distance * response.count) - distance + margin.top + margin.bottom;


            var svg = d3.select(selector).append('svg')
                .attr("viewBox", `0 0 ${width} ${height}`)

            width = width - margin.left - margin.right
            height = height - margin.top - margin.bottom;

            var timescale = d3.scaleTime()
                .domain([new Date(startyear, 0, 1), new Date(endyear, 12, 1)])
                .range([0, width]);

            const typeInfo = {
                'PI': { color: '#f78104', label: lang('Pi', 'PI') },
                'applicant': { color: '#B61F29', label: lang('Applicant', 'Antragsteller:in') },
                'worker': { color: '#008083', label: lang('Worker', 'Projektmitarbeiter:in') },
                'associate': { color: '#AAAAAA', label: lang('Associate', 'Beteiligte Person') },
                'coordinator': { color: '#AAAAAA', label: lang('Scientific Coordinator', 'Wiss. Koordinator:in') },
                'scholar': { color: '#008083', label: lang('Scholar', 'Stipenidat') },
                'supervisor': { color: '#f78104', label: lang('Supervisor', 'Betreuende Person') },
            }

            var axisBottom = d3.axisBottom(timescale)
                .ticks(12)
            // .tickPadding(5).tickSize(20);
            svg.append('g').attr('class', 'axes')
                .attr('transform', `translate(${margin.left}, ${height + margin.top + radius * 2})`)
                .call(axisBottom);

            var quarter = svg.append('g')
                .attr('transform', `translate(${margin.left - 6}, ${height + margin.top + radius * 2})`)
            // .selectAll("g")

            quarter.append('rect')
                .style("fill", 'rgb(236, 175, 0)')
                // .attr('height', height+margin.top+radius*4)
                .attr('height', 8)
                .attr('width', function (d, i) {
                    var date = new Date(CURRENT_YEAR, 1, 1)
                    var x1 = timescale(date)
                    var date = new Date(CURRENT_YEAR, 12, 31)
                    var x2 = timescale(date)
                    return x2 - x1
                })
                .style('opacity', .2)
                .attr('x', (d) => {
                    var date = new Date(CURRENT_YEAR, 1, 1)
                    return timescale(date)
                })
                // .attr('y', radius*-2)
                .attr('y', 0)

            quarter.append('text')
                .attr('x', (d) => {
                    var date = new Date(CURRENT_YEAR, 1, 1)
                    var x1 = timescale(date)
                    var date = new Date(CURRENT_YEAR, 12, 31)
                    var x2 = timescale(date)
                    return x1 + (x2 - x1) / 2
                })
                .attr('y', 6)
                .attr('text-anchor', 'middle')
                .style('fill', 'rgb(165, 122, 0)')
                .style('font-size', "5px")
                .html(lang('Current year', 'Aktuelles Jahr'))


            d3.selectAll("g>.tick>text")
                .each(function (d, i) {
                    d3.select(this).style("font-size", "8px");
                });

            var Tooltip = d3.select(selector)
                .append("div")
                .style("opacity", 0)
                .attr("class", "tooltip")
                .style("background-color", "white")
                .style("border", "solid")
                .style("border-width", "2px")
                .style("border-radius", "5px")
                .style("padding", "5px")


            function mouseover(d, i) {

                d3.select(this)
                    .select('circle,rect')
                    .transition()
                    .duration(300)
                    .style('opacity', 1)

                //Define and show the tooltip over the mouse location
                $(this).popover({
                    placement: 'auto top',
                    container: selector,
                    mouseOffset: 10,
                    followMouse: true,
                    trigger: 'hover',
                    html: true,
                    content: function () {
                        var role = '';
                        console.log(d.role);
                        if (typeInfo[d.role] !== undefined) {
                            role = `<span style="color:${typeInfo[d.role].color}">${typeInfo[d.role].label}</span>`
                        }
                        return `<b>${d.title ?? 'No title available'}</b><br><span class="text-muted">${d.funder ?? '-'}</span><br>${role}`
                    }
                });
                $(this).popover('show');
            } //mouseoverChord

            //Bring all chords back to default opacity
            function mouseout(event, d) {
                d3.select(this).select('circle,rect')
                    .transition()
                    .duration(300)
                    .style('opacity', .5)
                //Hide the tooltip
                $('.popover').each(function () {
                    $(this).remove();
                });
            }

            var eventGroup = svg.append('g')
                .attr('transform', `translate(${margin.left}, ${margin.top})`)
                .selectAll("g")
                .data(events)
                .enter().append("g")
                .attr('transform', function (d, i) {
                    var date = d.startdate
                    var x = timescale(date)
                    var y = i * distance
                    return `translate(${x}, ${y})`
                })

            eventGroup.on("mouseover", mouseover)
                .on("mouseout", mouseout)

            var lines = eventGroup.append('rect')
                .style("fill", function (d, i) {
                    if (typeInfo[d.role] === undefined) {
                        return '#cccccc'
                    }
                    return typeInfo[d.role].color
                })
                .attr('height', radius * 2)
                .attr('width', function (d, i) {
                    var date = d.startdate
                    var x1 = timescale(date)
                    var date = d.enddate
                    var x2 = timescale(date)
                    return x2 - x1
                })
                .style('opacity', .6)
                .attr('rx', 3)
                .attr('y', -radius)

            var labels = eventGroup.append('text')
                .attr('x', (d) => {
                    var date = d.startdate
                    var x1 = timescale(date)
                    var date = d.enddate
                    var x2 = timescale(date)
                    return (x2 - x1) / 2
                })
                .attr('y', 2)
                .attr('text-anchor', 'middle')
                .style('fill', 'white')
                .style('font-weight', '600')
                .style('font-size', "5px")
                .text((d) => d.title)
        },
        error: function (response) {
            console.log(response);
        }
    });
}

function coauthorNetwork(selector, data = {}) {
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/author-network",
        data: data,
        dataType: "json",
        success: function (response) {
            console.log(response);
            var matrix = response.data.matrix;
            var DEPTS = response.data.labels;

            var data = Object.values(DEPTS);
            var labels = data.map(item => item['name']);

            var colors = []
            var links = []
            var depts_in_use = {};

            data.forEach(function (d, i) {
                colors.push(d.dept.color ?? '#cccccc');
                var link = null
                if (i !== 0) link = ROOTPATH + "/profile/" + d.user
                links.push(link)

                if (d.dept.id && depts_in_use[d.dept.id] === undefined)
                    depts_in_use[d.dept.id] = d.dept;
            })

            Chords(selector, matrix, labels, colors, data, links, false, DEPTS[CURRENT_USER]['index']);


            var legend = d3.select('#legend')
                .append('div').attr('class', 'content')

            legend.append('div')
                .style('font-weight', 'bold')
                .attr('class', 'mb-5')
                .text(lang("Departments", "Abteilungen"))

            for (const dept in depts_in_use) {
                if (Object.hasOwnProperty.call(depts_in_use, dept)) {
                    const d = depts_in_use[dept];
                    var row = legend.append('div')
                        .attr('class', 'd-flex mb-5')
                        .style('color', d.color)
                    row.append('div')
                        .style('background-color', d.color)
                        .style("width", "2rem")
                        .style("height", "2rem")
                        .style("border-radius", ".5rem")
                        .style("display", "inline-block")
                        .style("margin-right", "1rem")
                    row.append('span').text(d.name)
                }
            }

        },
        error: function (response) {
            console.log(response);
        }
    });
}


function conceptTooltip() {
    $('.concept').each(function () {
        var el = $(this)
        var data = {
            score: el.attr('data-score'),
            name: el.attr('data-name'),
            count: el.attr('data-count'),
            wikidata: el.attr('data-wikidata'),
        }
        el.popover({
            placement: 'auto bottom',
            container: '#concepts',
            mouseOffset: 10,
            trigger: 'click',
            html: true,
            content: function () {
                var label = lang('Activities', 'Aktivitäten')
                if (data.count == 1) label = lang('Activity', 'Aktivität');
                return `<b>${data.name}</b><br>
                    Score: ${data.score} %</br>
                    In ${data.count} ${label}<br>
                    <hr>
                    <a href="${ROOTPATH}/concepts/${data.name}" target="_blank" rel="noopener noreferrer"><i class="ph ph-arrow-right"></i> Concept page</a><br>
                    <a href="${data.wikidata}" target="_blank" rel="noopener noreferrer"><i class="ph ph-arrow-up-right"></i> Wikidata</a>
                    `;
            }
        });
    }
    );
}

function wordcloud(selector, data = {}) {

    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/wordcloud",
        data: data,
        dataType: "json",
        success: function (response) {
            var dat = response.data //
            var max = 120;
            var highest = Object.values(dat)[0]
            var factor = max / highest

            myWords = Object.keys(dat).map(function (key) {
                return {
                    text: key,
                    size: (dat[key] * factor) + 10
                };
            });
            // console.log(myWords);
            // myWords = myWords.slice(0, 300)

            // set the dimensions and margins of the graph
            var margin = {
                top: 10,
                right: 10,
                bottom: 10,
                left: 10
            },
                width = 800 - margin.left - margin.right,
                height = 450 - margin.top - margin.bottom,
                colors = [
                    '#f78104',
                    '#faab36',
                    '#e95709',
                    '#008083',
                    '#249ea0',
                    '#005f60',
                    // '#63a308',
                    // '#ECAF00',
                ];
            // Constructs a new cloud layout instance. It run an algorithm to find the position of words that suits your requirements
            // Wordcloud features that are different from one word to the other must be here
            var layout = d3.layout.cloud()
                .size([800, 500])
                .words(myWords)
                .padding(0)
                .rotate(function () {
                    return (~~(Math.random() * 3) * 45) - 45;
                })
                .font("Impact")
                .fontSize(function (d) {
                    return d.size;
                })
                .on("end", draw);

            layout.start();

            function draw(words) {
                d3.select(selector).append("svg")
                    .attr("width", '100%')
                    .attr("height", '100%')
                    .attr('viewBox', "0 0 800 500")
                    .append("g")
                    .attr("transform", "translate(" + layout.size()[0] / 2 + "," + layout.size()[1] / 2 + ")")
                    .selectAll("text")
                    .data(words)
                    .enter().append("text")
                    .style("font-size", function (d) {
                        return d.size + "px";
                    })
                    .style("font-family", "Impact")
                    .attr("text-anchor", "middle")
                    .attr("fill", (d) => colors[Math.floor(Math.random() * colors.length)])
                    .attr("transform", function (d) {
                        return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
                    })
                    .text(function (d) {
                        return d.text;
                    });
            }

        },
        error: function (response) {
            console.log(response);
        }
    });
}


function userTable(selector, data = {}) {
    data['table'] = true
    console.log(data)
    return $(selector).DataTable({
        "ajax": {
            "url": ROOTPATH + '/api/users',
            "data": data,
            dataSrc: 'data'
        },
        dom: 'frtipP',
        deferRender: true,
        responsive: true,
        language: {
            url: lang(null, ROOTPATH + '/js/datatables/de-DE.json')
        },
        columnDefs: [
            {
                targets: 0,
                data: 'img',
                searchable: false,
                sortable: false,
                visible: true
            },
            {
                targets: 1,
                data: 'html',
                className: 'flex-grow-1'
            },
            {
                targets: 2,
                data: 'dept',
                searchable: true,
                sortable: false,
                visible: false
            },
            {
                targets: 3,
                data: 'active',
                searchable: true,
                sortable: false,
                visible: false
            },
            {
                target: 4,
                data: 'names',
                searchable: true,
                visible: false
            },
            {
                target: 5,
                data: 'topics',
                searchable: true,
                visible: false
            }

        ],
        "order": [
            [1, 'asc'],
        ],

        paging: true,
        autoWidth: true,
        pageLength: 18,
    });
}

function iconTest(icon) {
    $('#test-icon').attr('class', 'ph ph-' + icon);
}

function spark($selector, $filter) {
    // TODO: unfinished
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/activities",
        data: {
            filter: $filter,
            aggregate: 'year'
        },
        dataType: "json",
        success: function (response) {
            console.log(response);
            var data = response.data;

            // sort data by key "year" desc
            data.sort((a, b) => b.activity - a.activity);
            // transform data
            var labels = data.map(item => item.activity);
            // labels are years. fill the gaps
            labels = Array.from({ length: labels[0] - labels[labels.length - 1] + 1 }, (_, i) => labels[labels.length - 1] + i);

            var y = data.map(item => item.count);

            console.log(labels);
            console.log(y);

            var ctx = document.getElementById($selector)
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Dataset',
                        data: y,
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        title: {
                            display: false,
                            text: 'Activities'
                        }
                    },
                    scales: {
                        // x: {
                        //     display: false,
                        // },
                        y: {
                            display: false,
                            beginAtZero: true
                        }
                    },
                }
            });
        },
        error: function (response) {
            console.log(response);
        }
    });
}


function timelineChart(filter = {}, props = {}) {
    if (typeof timeline !== 'function') {
        console.error('Timeline function is not defined. Please ensure the timeline.js script is included.');
        return;
    }

    let selector = props.timelineSelector || '#timeline';
    let eventSelector = props.eventSelector || '#event-selector';
    let yearSelector = props.yearSelector || '#activity-year';

    // check if selector exists
    if (!$(selector).length || !$(yearSelector).length) {
        console.error('Timeline selector or year selector not found.');
        return;
    }
    if (eventSelector && !$(eventSelector).length) {
        eventSelector = null; // if eventSelector is not found, set it to null
    }

    // current year and quarter
    // let date = new Date();
    let year = $(yearSelector).val();
    let currentYear = new Date().getFullYear();
    // check if year is a valid 4 digit number
    if (!/^\d{4}$/.test(year)) {
        toastError('Invalid year format. Please enter a valid 4-digit year.');
        return;
    }
    if (year > currentYear) {
        year = currentYear;
        $(yearSelector).val(year);
    }

    $(selector).empty()
    if (eventSelector) {
        $(eventSelector).empty()
    }

    filter['start_date'] = {
        '$gte': `${year}-01-01`,
        '$lte': `${year}-12-31`
    };
    // let quarter = Math.ceil((date.getMonth() + 1) / 3);
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/timeline",
        data: {
            filter: filter
        },
        dataType: "json",
        success: function (response) {
            console.log(response);
            let typeInfo = response.data.info;
            let events = response.data.events;
            if (events.length === 0) {
                $(selector).html('<div class="content text-muted text-center">' + lang('No activities found for this year.', 'Keine Aktivitäten für dieses Jahr gefunden.') + '</div>');
                return;
            }
            if (eventSelector) {
                let types = response.data.types;
                types.forEach(t => {
                    let type = typeInfo[t];
                    if (!type) return;
                    let item = $('<small class="badge type-badge active ' + type.id + '" onclick="toggleTimelineActivity(\'' + type.id + '\')">' + lang(type.name, type.name_de ?? null) + '</small>');
                    item.css('background-color', type.color);
                    $(eventSelector).append(item);
                });
            }
            timeline(year, 0, typeInfo, events);
        },
        error: function (response) {
            console.log(response);
        }
    });
}

function toggleTimelineActivity(type) {
    // check if type is active
    let active = ($('.badge.' + type).hasClass('active'));

    $('.badge.' + type).toggleClass('active');
    $('.event-circle.' + type).toggle();

    if (activitiesTable) {
        // activitiesTable.columns(5).search(type, true, false, true).draw();
        if (active) {
            activeCategories.add(type);
        } else {
            activeCategories.delete(type);
        }

        activitiesTable.draw(); // Tabelle neu zeichnen
    }
}