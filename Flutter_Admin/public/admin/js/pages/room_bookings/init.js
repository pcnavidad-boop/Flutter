// init.js – Main initializer for Room Bookings
document.addEventListener("DOMContentLoaded", () => {

    console.log("[RoomBookings] Initializing...");

    /* --------------------------------------------------------
     |  1. CREATE CALENDAR INSTANCES
     | --------------------------------------------------------
     */
    if (window.AdminRoomBookingCalendar) {

        // ADD BOOKING calendar
        window.addCal = window.AdminRoomBookingCalendar.CalendarInstance({
            containerId: "add-calendar",
            labelId: "add-cal-label",
            prevId: "add-cal-prev",
            nextId: "add-cal-next",
            roomId: "add-room-id",
            startInput: "add-start-date",
            endInput: "add-end-date",
            modalId: "addRoomBookingModal"
        });

        // EDIT BOOKING calendar
        window.editCal = window.AdminRoomBookingCalendar.CalendarInstance({
            containerId: "edit-calendar",
            labelId: "edit-cal-label",
            prevId: "edit-cal-prev",
            nextId: "edit-cal-next",
            roomId: "edit-room-id",
            startInput: "edit-start-date",
            endInput: "edit-end-date",
            modalId: "editRoomBookingModal"
        });
    }

    /* --------------------------------------------------------
     |  2. WHEN ADD MODAL OPENS → RESET CALENDAR
     | --------------------------------------------------------
     */
    const addModal = document.getElementById("addRoomBookingModal");
    if (addModal) {
        addModal.addEventListener("show.bs.modal", () => {
            if (window.addCal) window.addCal.open();
        });
    }

    /* --------------------------------------------------------
     |  3. WHEN EDIT MODAL OPENS → FILL CALENDAR WITH DATES
     | --------------------------------------------------------
     */
    const editModal = document.getElementById("editRoomBookingModal");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", () => {
            // Dates are applied by edit.js after fetch
            if (window.editCal) {
                setTimeout(() => {
                    const start = document.getElementById("edit-start-date")?.value;
                    const end = document.getElementById("edit-end-date")?.value;

                    window.editCal.fill(start, end);
                }, 100);
            }
        });
    }

    /* --------------------------------------------------------
     |  4. FIX: LOAD CALENDAR WHEN COLLAPSE IS SHOWN
     |     Prevents empty calendar until user clicks arrows
     | --------------------------------------------------------
     */
    const addCollapse = document.getElementById("addCalendarDropdown");
    if (addCollapse) {
        addCollapse.addEventListener("shown.bs.collapse", () => {
            if (window.addCal) window.addCal.open();
        });
    }

    const editCollapse = document.getElementById("editCalendarDropdown");
    if (editCollapse) {
        editCollapse.addEventListener("shown.bs.collapse", () => {
            if (window.editCal) {
                const start = document.getElementById("edit-start-date")?.value;
                const end = document.getElementById("edit-end-date")?.value;

                window.editCal.fill(start, end);
            }
        });
    }

    console.log("[RoomBookings] Ready.");
});
