document.addEventListener("DOMContentLoaded", async () => {
    console.log("[Admin] Initializing core...");

    // Load toast system, csrf helpers, etc.
    if (window.initializeAdminCore instanceof Function) {
        window.initializeAdminCore();
    }

    // Load page-specific JS
    await window.loadPageScripts();
});
