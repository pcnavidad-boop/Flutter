// calendar.js - mini calendar engine
(function () {

    const weekdays = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

    function daysInMonth(y, m) {
        return new Date(y, m + 1, 0).getDate();
    }

    function label(y, m) {
        return new Date(y, m).toLocaleString("default", {
            month: "long",
            year: "numeric",
        });
    }

    function pad(n) {
        return String(n).padStart(2, "0");
    }

    function iso(y, m, d) {
        return `${y}-${pad(m + 1)}-${pad(d)}`;
    }

    async function getAvailability(roomId, y, m) {
        if (!roomId) return { booked_days: {} };

        const monthStr = `${y}-${pad(m + 1)}`;

        try {
            const res = await fetch(`/admin/room-bookings/check-availability/${roomId}?month=${monthStr}`);
            return res.ok ? await res.json() : { booked_days: {} };
        } catch {
            return { booked_days: {} };
        }
    }

    function render(opts) {
        const { containerId, labelId, year, month, booked, startIso, endIso, onSelect } = opts;

        const cont = document.getElementById(containerId);
        if (!cont) return;

        cont.innerHTML = "";

        // Headers
        weekdays.forEach(w => {
            const e = document.createElement("div");
            e.className = "day header";
            e.innerText = w;
            cont.appendChild(e);
        });

        const firstDay = new Date(year, month, 1).getDay();

        for (let i = 0; i < firstDay; i++) {
            const e = document.createElement("div");
            e.className = "day empty";
            cont.appendChild(e);
        }

        const todayIso = new Date().toISOString().slice(0, 10);

        for (let d = 1; d <= daysInMonth(year, month); d++) {
            const dayIso = iso(year, month, d);

            const cell = document.createElement("div");
            cell.className = "day";
            cell.innerText = d;

            if (dayIso < todayIso) {
                cell.classList.add("past");
                cont.appendChild(cell);
                continue;
            }

            if (booked[dayIso]) {
                cell.classList.add("booked");
                cont.appendChild(cell);
                continue;
            }

            // available
            cell.classList.add("available");

            if (startIso === dayIso || endIso === dayIso) {
                cell.classList.add("selected");
            }

            if (startIso && endIso && dayIso > startIso && dayIso < endIso) {
                cell.classList.add("in-range");
            }

            cell.addEventListener("click", () => onSelect(dayIso));
            cont.appendChild(cell);
        }

        const labelNode = document.getElementById(labelId);
        if (labelNode) labelNode.innerText = label(year, month);
    }

    function CalendarInstance(cfg) {
        const { containerId, labelId, prevId, nextId, roomId, startInput, endInput, modalId } = cfg;

        let year = new Date().getFullYear();
        let month = new Date().getMonth();
        let booked = {};
        let start = null;
        let end = null;

        async function load() {
            const room = document.getElementById(roomId)?.value;

            const data = await getAvailability(room, year, month);
            booked = data.booked_days || {};

            render({
                containerId,
                labelId,
                year,
                month,
                booked,
                startIso: start,
                endIso: end,
                onSelect: handleSelect
            });
        }

        function updateInputs() {
            const s = document.getElementById(startInput);
            const e = document.getElementById(endInput);
            if (s) s.value = start || "";
            if (e) e.value = end || "";
        }

        function handleSelect(dayIso) {
            if (!start) {
                start = dayIso;
                end = null;
                updateInputs();
                return load();
            }

            if (start && !end) {
                if (dayIso < start) {
                    end = start;
                    start = dayIso;
                } else {
                    end = dayIso;
                }

                // conflict check
                let cur = new Date(start);
                const last = new Date(end);
                let bad = false;

                while (cur <= last) {
                    const isoVal = cur.toISOString().slice(0, 10);
                    if (booked[isoVal]) { bad = true; break; }
                    cur.setDate(cur.getDate() + 1);
                }

                if (bad) {
                    end = null;
                    showMessage("Selected range includes booked dates.", modalId);
                }

                updateInputs();
                return load();
            }

            // Both selected → restart
            start = dayIso;
            end = null;
            updateInputs();
            return load();
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

        document.getElementById(roomId)?.addEventListener("change", () => {
            start = null;
            end = null;
            updateInputs();
            load();
        });

        return {
            open() {
                start = null;
                end = null;
                updateInputs();
                load();
            },
            fill(startIso, endIso) {
                start = startIso || null;
                end = endIso || null;
                load();
            }
        };
    }

    function showMessage(msg, modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        let box = modal.querySelector(".calendar-msg");
        if (!box) {
            box = document.createElement("div");
            box.className = "calendar-msg mt-2";
            modal.querySelector(".modal-body").prepend(box);
        }

        box.innerHTML = `<div class="alert alert-danger py-2">${msg}</div>`;

        setTimeout(() => box.remove(), 2000);
    }


    window.AdminRoomBookingCalendar = {
        CalendarInstance
    };

})();
