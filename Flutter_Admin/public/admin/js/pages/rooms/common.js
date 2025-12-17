(function (root) {

    const bedRules = {
        single:[1,1], double:[1,2], quad:[2,2],
        family:[2,3], suite:[1,2], penthouse:[2,4],
        function:[0,0]
    };

    const capacityRules = {
        single:[1,1], double:[1,2], quad:[4,4],
        family:[4,6], suite:[2,4], penthouse:[4,8],
        function:[1,500]
    };

    // Map room type → raw backend price_type
    const priceTypeRawMap = {
        single: "per_night",
        double: "per_night",
        quad: "per_night",
        family: "per_night",
        suite: "per_night",
        penthouse: "per_night",
        function: "per_day"   
    };

    // Raw backend → human label
    const priceTypeDisplay = {
        per_night: "per night",
        per_day: "per day"
    };

    function getPriceTypeRaw(type) {
        return priceTypeRawMap[type] ?? "per_night";
    }

    function toDisplayPriceType(raw) {
        return priceTypeDisplay[raw] ?? raw;
    }

    function updateHints(prefix, type) {
        const bedsBlock = document.getElementById(`${prefix}-beds-block`);
        const bedsHint  = document.getElementById(`${prefix}-beds-hint`);
        const capHint   = document.getElementById(`${prefix}-capacity-hint`);

        if (bedsBlock) {
            bedsBlock.style.display = (type === "function") ? "none" : "block";
        }

        if (bedsHint) {
            const [bMin,bMax] = bedRules[type] ?? [0,0];
            bedsHint.innerText = type === "function"
                ? ""
                : `Allowed: ${bMin}–${bMax} beds`;
        }

        if (capHint) {
            const [cMin,cMax] = capacityRules[type] ?? [1,500];
            capHint.innerText = `Allowed: ${cMin}–${cMax} capacity`;
        }
    }

    root.RoomsCommon = {
        updateHints,
        getPriceTypeRaw,
        toDisplayPriceType
    };

})(window);
