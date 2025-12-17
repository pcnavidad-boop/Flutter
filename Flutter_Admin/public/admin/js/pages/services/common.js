(function (root) {

    const capacityRules = {
        restaurant: [1, 100],
        bar: [1, 75],
        spa: [1, 25],
        gym: [1, 25],
        swimming_pool: [1, 50],
    };

    // OPTION A: UI always shows "per person"
    const priceLabel = "per person";
    const hourlyTypes = ['restaurant', 'bar', 'spa'];

    function getPriceType(/*type*/) {
        // Always return the human readable label
        return priceLabel;
    }

    function isHourlyType(type) {
        return hourlyTypes.indexOf(type) !== -1;
    }

    function updateHints(prefix, type) {
        const capHint = document.getElementById(`${prefix}-capacity-hint`);
        if (capHint && capacityRules[type]) {
            const [min, max] = capacityRules[type];
            capHint.innerText = `Allowed: ${min}–${max}`;
        }

        const priceTypeField = document.getElementById(`${prefix}-price-type`);
        if (priceTypeField) {
            // Always show per person in the UI
            priceTypeField.value = getPriceType();
        }

        // time hint + step adjustment (keep existing time behaviour)
        const startInput = document.getElementById(`${prefix}-start-time`) || document.getElementById(`${prefix}-start_time`) || document.getElementById(`${prefix}-start`);
        const endInput = document.getElementById(`${prefix}-end-time`) || document.getElementById(`${prefix}-end_time`) || document.getElementById(`${prefix}-end`);
        const timeHint = document.getElementById(`${prefix}-time-hint`);

        if (isHourlyType(type)) {
            if (timeHint) timeHint.innerText = "Hourly slots required (e.g. 09:00, 10:00).";
            if (startInput) startInput.setAttribute('step', '3600');
            if (endInput) endInput.setAttribute('step', '3600');
        } else {
            if (timeHint) timeHint.innerText = "Minute granularity allowed (e.g. 09:15).";
            if (startInput) startInput.setAttribute('step', '60');
            if (endInput) endInput.setAttribute('step', '60');
        }
    }

    root.ServicesCommon = {
        updateHints,
        getPriceType,
        isHourlyType
    };

})(window);
