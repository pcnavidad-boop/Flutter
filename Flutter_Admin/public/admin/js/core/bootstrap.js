/**
 * Shared admin utilities & bootstrap helpers
 */
window.initializeAdminCore = function () {

    // Toast auto-show
    document.querySelectorAll(".toast").forEach((toastEl) => {
        const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 1800,
        });
        toast.show();
    });

    console.log("[Admin] Core initialized");
};
