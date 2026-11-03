function toDmsString(lat, lng) {
    if (lat === null || lng === null || isNaN(lat) || isNaN(lng)) return '';
    const latDir = lat < 0 ? 'S' : 'N';
    const latAbs = Math.abs(lat);
    let latDeg = Math.floor(latAbs);
    let latMinRem = (latAbs - latDeg) * 60;
    let latMin = Math.floor(latMinRem);
    let latSec = Math.round((latMinRem - latMin) * 60 * 10) / 10;
    if (latSec >= 60) { latMin += 1; latSec = 0; }
    if (latMin >= 60) { latDeg += 1; latMin = 0; }

    const lngDir = lng < 0 ? 'W' : 'E';
    const lngAbs = Math.abs(lng);
    let lngDeg = Math.floor(lngAbs);
    let lngMinRem = (lngAbs - lngDeg) * 60;
    let lngMin = Math.floor(lngMinRem);
    let lngSec = Math.round((lngMinRem - lngMin) * 60 * 10) / 10;
    if (lngSec >= 60) { lngMin += 1; lngSec = 0; }
    if (lngMin >= 60) { lngDeg += 1; lngMin = 0; }

    const latSecStr = latSec.toFixed(1);
    const lngSecStr = lngSec.toFixed(1);

    return `${latDeg}°${latMin}'${latSecStr}"${latDir} ${lngDeg}°${lngMin}'${lngSecStr}"${lngDir}`;
}

function parseCoordinates(raw) {
    if (!raw || typeof raw !== 'string') return null;
    let str = raw.trim();
    str = str.replace(/^[\(\[\{]+|[\)\]\}]+$/g, '').trim();
    if (!str) return null;

    // 1. Check decimal: e.g. -2.433306, 115.680333 or 2.433306 S, 115.680333 E
    const decRegex = /^([+-]?\d+(?:\.\d+)?)\s*([NSns]?)[,\s;]+([+-]?\d+(?:\.\d+)?)\s*([EWew]?)$/;
    const decMatch = str.match(decRegex);
    if (decMatch) {
        let lat = parseFloat(decMatch[1]);
        const latCard = (decMatch[2] || '').toUpperCase();
        let lng = parseFloat(decMatch[3]);
        const lngCard = (decMatch[4] || '').toUpperCase();

        if (latCard === 'S' && lat > 0) lat = -lat;
        if (latCard === 'N' && lat < 0) lat = Math.abs(lat);
        if (lngCard === 'W' && lng > 0) lng = -lng;
        if (lngCard === 'E' && lng < 0) lng = Math.abs(lng);

        if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            return {
                latitude: Number(lat.toFixed(6)),
                longitude: Number(lng.toFixed(6)),
                dms: toDmsString(lat, lng)
            };
        }
    }

    // 2. Normalize DMS symbols
    const normalized = str
        .replace(/[’‘`′]/g, "'")
        .replace(/[”“″]/g, '"')
        .replace(/[º]/g, '°');

    const dmsRegex = /(\d+)\s*[°dD\s]\s*(\d+)\s*['mM\s]\s*(\d+(?:\.\d+)?)\s*["sS\s]*([NSnsEWew])?[\s,;]+(\d+)\s*[°dD\s]\s*(\d+)\s*['mM\s]\s*(\d+(?:\.\d+)?)\s*["sS\s]*([NSnsEWew])?/;
    const dmsMatch = normalized.match(dmsRegex);
    if (dmsMatch) {
        const latDeg = parseFloat(dmsMatch[1]);
        const latMin = parseFloat(dmsMatch[2]);
        const latSec = parseFloat(dmsMatch[3]);
        const latDir = (dmsMatch[4] || 'S').toUpperCase();

        const lngDeg = parseFloat(dmsMatch[5]);
        const lngMin = parseFloat(dmsMatch[6]);
        const lngSec = parseFloat(dmsMatch[7]);
        const lngDir = (dmsMatch[8] || 'E').toUpperCase();

        let lat = latDeg + (latMin / 60) + (latSec / 3600);
        let lng = lngDeg + (lngMin / 60) + (lngSec / 3600);

        if (latDir === 'S') lat = -lat;
        if (lngDir === 'W') lng = -lng;

        if (Math.abs(lat) > 90 && Math.abs(lng) <= 90) {
            const t = lat; lat = lng; lng = t;
        }

        if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            return {
                latitude: Number(lat.toFixed(6)),
                longitude: Number(lng.toFixed(6)),
                dms: toDmsString(lat, lng)
            };
        }
    }

    return null;
}

const tests = [
    '2°25\'59.9"S 115°40\'49.2"E',
    '(2°25\'59.9"S 115°40\'49.2"E)',
    '2°25\'59.9"S, 115°40\'49.2"E',
    '-2.433306, 115.680333',
    '(-2.433306, 115.680333)',
    '2.433306S 115.680333E',
];

let allPassed = true;
tests.forEach(t => {
    const res = parseCoordinates(t);
    console.log(t, '=>', res);
    if (!res || Math.abs(res.latitude - (-2.433306)) > 0.001) {
        allPassed = false;
    }
});

if (!allPassed) {
    console.error('Some JS tests failed!');
    process.exit(1);
} else {
    console.log('All JS parser tests PASSED!');
}
