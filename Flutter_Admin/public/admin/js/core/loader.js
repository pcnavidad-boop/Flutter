/**
 * Dynamically load a JS file (no bundler needed)
 */
window.loadScript = function (path) {
    return new Promise((resolve, reject) => {
        const tag = document.createElement("script");
        tag.src = "/admin/" + path;
        tag.onload = resolve;
        tag.onerror = reject;
        document.body.appendChild(tag);
    });
};

/**
 * Auto load JS based on route name
 */
window.loadPageScripts = async function () {
    const page = document.body.dataset.page;
    if (!page) return;

    const routes = {
        "admin.dashboard":              "pages/dashboard.js",
        "admin.rooms.index":            "pages/rooms/index.js",
        "admin.services.index":         "pages/services/index.js",
        "admin.room_bookings.index":    "pages/room_bookings/index.js",
        "admin.service_bookings.index": "pages/service_bookings/index.js",
        "admin.payments.index":         "pages/payments/index.js",
    };

    const script = routes[page];

    if (script) {
        await loadScript(script);
        console.log("[Admin] Loaded:", script);
    } else {
        console.log("[Admin] No script for:", page);
    }
};
