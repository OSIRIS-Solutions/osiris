function initListWidget($root, initialValues) {
    const nameAttr = $root.data("name") || "items[]";
    const $input = $root.find(".list-widget-input");

    function normalize(s) {
        return $.trim(String(s || "")).replace(/\s+/g, " ");
    }

    function keyOf(s) {
        return normalize(s).toLowerCase();
    }

    function exists(value) {
        const key = keyOf(value);
        let found = false;
        $root.find('.list-widget-hidden').each(function () {
            if (keyOf($(this).val()) === key) {
                found = true;
                return false;
            }
        });
        return found;
    }

    function addItem(raw) {
        const value = normalize(raw);
        if (!value) return;
        if (exists(value)) return;

        const id = "lw_" + Date.now() + "_" + Math.floor(Math.random() * 1e6);

        const $li = $("<span>", {
            class: "list-widget-item",
            "data-id": id
        });
        const $text = $("<span>", {
            class: "list-widget-text",
            text: value
        });
        const $btn = $("<button>", {
            type: "button",
            class: "list-widget-remove",
            text: "×",
            "aria-label": "Eintrag entfernen: " + value
        });

        $li.append($text, $btn);

        const $h = $("<input>", {
            type: "hidden",
            name: nameAttr,
            value: value,
            class: "list-widget-hidden",
            "data-id": id
        });
        $li.append($h);
        $input.before($li);
    }

    function commitSingleFromInput() {
        addItem($input.val());
        $input.val("");
    }

    function parsePasted(text) {
        // split by newline OR comma/semicolon; keep it simple + robust
        return String(text || "")
            .split(/[\n\r,;]+/g)
            .map((t) => normalize(t))
            .filter(Boolean);
    }

    // Enter adds item; Backspace deletes last if empty
    $input.on("keydown.listWidget", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            commitSingleFromInput();
            return;
        }

        if (e.key === "Backspace") {
            if (normalize($input.val()) === "") {
                const $last = $root.find(".list-widget-item").last();
                if ($last.length) {
                    $last.remove();
                    e.preventDefault();
                }
            }
        }
    });

    // blur adds item
    $input.on("blur.listWidget", function () {
        commitSingleFromInput();
    });

    // Paste: add multiple items
    $input.on("paste.listWidget", function (e) {
        const clipboard = (e.originalEvent && e.originalEvent.clipboardData) ? e.originalEvent.clipboardData : null;
        const pasted = clipboard ? clipboard.getData("text") : "";
        const parts = parsePasted(pasted);

        // Only hijack paste if it looks like multiple entries
        if (parts.length > 1) {
            e.preventDefault();
            parts.forEach(addItem);
            $input.val("");
        }
    });

    // Remove button
    $root.on("click.listWidget", ".list-widget-remove", function () {
        $(this).closest(".list-widget-item").remove()
        $input.trigger("focus");
    });

    // init with existing values
    (initialValues || []).forEach(addItem);

    return {
        addItem: addItem,
        exists: exists
    };
}