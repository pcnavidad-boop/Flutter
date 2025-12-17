document.addEventListener("DOMContentLoaded", () => {

    if (window.AdminServiceBookingCalendar) {

        window.svcAddCal = window.AdminServiceBookingCalendar.SingleDateCalendar({
            containerId: "add-calendar",
            labelId: "add-cal-label",
            prevId: "add-cal-prev",
            nextId: "add-cal-next",
            dateInputId: "add-appointment-date",
            serviceSelectId: "add-service-id"
        });

        window.svcEditCal = window.AdminServiceBookingCalendar.SingleDateCalendar({
            containerId: "edit-calendar",
            labelId: "edit-cal-label",
            prevId: "edit-cal-prev",
            nextId: "edit-cal-next",
            dateInputId: "edit-appointment-date",
            serviceSelectId: "edit-service-id"
        });
    }

    // open add calendar when collapse opened
    const addCollapse = document.getElementById("addServiceCalendarDropdown");
    if (addCollapse) {
        addCollapse.addEventListener("shown.bs.collapse", () => {
            if (window.svcAddCal) window.svcAddCal.open();
        });
    }

    // When add modal shows, ensure calendar is loaded
    const addModal = document.getElementById("addServiceBookingModal");
    if (addModal) {
        addModal.addEventListener("show.bs.modal", () => {
            if (window.svcAddCal) window.svcAddCal.open();
        });
    }

    // When edit modal shows, populate calendar after edit.js fetch completes
    const editModal = document.getElementById("editServiceBookingModal");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", () => {
            setTimeout(() => {
                const val = document.getElementById("edit-appointment-date")?.value;
                if (window.svcEditCal) window.svcEditCal.fill(val);
            }, 120);
        });
    }

    // auto-open add modal if session flag set by controller on validation failure
    if (window.__SERVICE_BOOKING__AUTOOPEN_ADD) {
        const m = new bootstrap.Modal(document.getElementById('addServiceBookingModal'));
        m.show();
    }

});
