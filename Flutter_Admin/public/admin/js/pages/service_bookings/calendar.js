// calendar.js - Single-date calendar for Service Bookings
(function () {

    const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

    function pad(n) {
        return String(n).padStart(2, "0");
    }

    function iso(y, m, d) {
        return `${y}-${pad(m + 1)}-${pad(d)}`;
    }

    function daysInMonth(y, m) {
        return new Date(y, m + 1, 0).getDate();
    }

    function label(y, m) {
        return new Date(y, m).toLocaleString("default", {
            month: "long",
            year: "numeric",
        });
    }

    /**
     * Fetch availability counts for the service
     * Returns a map like: { '2025-02-12': true, '2025-02-18': true }
     */
    async function fetchDailyCapacity(serviceId, monthStr) {
        if (!serviceId) return { full: {} };

        try {
            const res = await fetch(
                `/admin/service-bookings/check-availability/${serviceId}?month=${monthStr}`
            );

            return res.ok ? await res.json() : { full: {} };
        } catch {
            return { full: {} };
        }
    }

    /**
     * Render a calendar grid
     */
    function render(opts) {
        const { containerId, labelId, year, month, fullDays, selected, onSelect } = opts;
        const cont = document.getElementById(containerId);
        if (!cont) return;

        cont.innerHTML = "";

        // Header row
        weekdays.forEach(w => {
            const el = document.createElement("div");
            el.className = "day header";
            el.innerText = w;
            cont.appendChild(el);
        });

        const firstDay = new Date(year, month, 1).getDay();
        const todayIso = new Date().toISOString().slice(0, 10);

        // Empty before first day
        for (let i = 0; i < firstDay; i++) {
            const e = document.createElement("div");
            e.className = "day empty";
            cont.appendChild(e);
        }

        // Days of the month
        for (let d = 1; d <= daysInMonth(year, month); d++) {
            const dayIso = iso(year, month, d);
            const cell = document.createElement("div");
            cell.className = "day";
            cell.innerText = d;

            // Past date = disabled
            if (dayIso < todayIso) {
                cell.classList.add("past");
                cont.appendChild(cell);
                continue;
            }

            // Fully booked date
            if (fullDays[dayIso]) {
                cell.classList.add("booked");
                cont.appendChild(cell);
                continue;
            }

            // Selectable
            cell.classList.add("available");

            if (selected === dayIso) {
                cell.classList.add("selected");
            }

            cell.addEventListener("click", () => onSelect(dayIso));
            cont.appendChild(cell);
        }

        const labelNode = document.getElementById(labelId);
        if (labelNode) labelNode.innerText = label(year, month);
    }

    /**
     * Calendar instance constructor — similar to RoomBooking but simpler.
     */
    function SingleDateCalendar(cfg) {
        const { containerId, labelId, prevId, nextId, dateInputId, serviceSelectId } = cfg;

        let year = new Date().getFullYear();
        let month = new Date().getMonth();
        let fullDays = {};
        let selectedDate = null;

        async function load() {
            const serviceId = document.getElementById(serviceSelectId)?.value;
            const monthStr = `${year}-${pad(month + 1)}`;

            const data = await fetchDailyCapacity(serviceId, monthStr);
            fullDays = data.full || {};

            render({
                containerId,
                labelId,
                year,
                month,
                fullDays,
                selected: selectedDate,
                onSelect: handleSelect
            });
        }

        function handleSelect(dateIso) {
            selectedDate = dateIso;
            document.getElementById(dateInputId).value = dateIso;
            load();
        }

        document.getElementById(prevId)?.addEventListener("click", () => {
            month--;
            if (month < 0) { month = 11; year--; }
            load();
        });

        document.getElementById(nextId)?.addEventListener("click", () => {
            month++;
            if (month > 11) { month = 0; year++; }
            load();
        });

        document.getElementById(serviceSelectId)?.addEventListener("change", () => {
            selectedDate = null;
            document.getElementById(dateInputId).value = "";
            load();
        });

        return {
            open() {
                selectedDate = null;
                document.getElementById(dateInputId).value = "";
                load();
            },

            fill(isoDate) {
                selectedDate = isoDate || null;
                load();
            }
        };
    }

    window.AdminServiceBookingCalendar = {
        SingleDateCalendar
    };

})();
