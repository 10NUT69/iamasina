const units = ['', 'unu', 'doi', 'trei', 'patru', 'cinci', 'șase', 'șapte', 'opt', 'nouă'];
const teens = ['zece', 'unsprezece', 'doisprezece', 'treisprezece', 'paisprezece', 'cincisprezece', 'șaisprezece', 'șaptesprezece', 'optsprezece', 'nouăsprezece'];
const tens = ['', '', 'douăzeci', 'treizeci', 'patruzeci', 'cincizeci', 'șaizeci', 'șaptezeci', 'optzeci', 'nouăzeci'];
const wholeAmountFormat = new Intl.NumberFormat('ro-RO');
const fractionalAmountFormat = new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export function parseAmount(value) {
    const input = value.trim();
    const localized = /^\d{1,3}(?:\.\d{3})+(?:,\d{1,2})?$/.test(input);
    const spaced = /^\d{1,3}(?:[ \u00a0\u202f]\d{3})+(?:[,.]\d{1,2})?$/.test(input);
    let normalized = input;

    if (localized) {
        normalized = input.replaceAll('.', '');
    } else if (spaced) {
        normalized = input.replace(/[ \u00a0\u202f]/g, '');
    }

    if (!/^\d{1,9}(?:[,.]\d{1,2})?$/.test(normalized)) {
        return null;
    }

    const [whole, fraction = ''] = normalized.split(/[,.]/);
    const minorUnits = Number(whole) * 100 + Number(fraction.padEnd(2, '0'));
    return minorUnits > 0 ? minorUnits : null;
}

export function formatAmount(minorUnits) {
    const format = minorUnits % 100 ? fractionalAmountFormat : wholeAmountFormat;
    return format.format(minorUnits / 100);
}

function underThousand(number, feminine = false) {
    const hundreds = Math.floor(number / 100);
    const rest = number % 100;
    const unit = rest % 10;
    const unitWord = feminine ? ({ 1: 'una', 2: 'două' }[unit] ?? units[unit]) : units[unit];
    const parts = [];

    if (hundreds) {
        parts.push(hundreds === 1 ? 'o sută' : `${hundreds === 2 ? 'două' : units[hundreds]} sute`);
    }

    if (rest < 10 && unit) {
        parts.push(unitWord);
    } else if (rest >= 10 && rest < 20) {
        parts.push(feminine && rest === 12 ? 'douăsprezece' : teens[rest - 10]);
    } else if (rest >= 20) {
        parts.push(tens[Math.floor(rest / 10)] + (unit ? ` și ${unitWord}` : ''));
    }

    return parts.join(' ');
}

function withDe(number) {
    const lastTwoDigits = number % 100;
    return (number >= 100 && lastTwoDigits === 0) || lastTwoDigits >= 20 ? ' de ' : ' ';
}

function wholeNumberInWords(number) {
    if (number === 0) {
        return 'zero';
    }

    const millions = Math.floor(number / 1_000_000);
    const thousands = Math.floor(number / 1_000) % 1_000;
    const rest = number % 1_000;
    const parts = [];

    if (millions) {
        parts.push(millions === 1 ? 'un milion' : `${underThousand(millions, true)}${withDe(millions)}milioane`);
    }
    if (thousands) {
        parts.push(thousands === 1 ? 'o mie' : `${underThousand(thousands, true)}${withDe(thousands)}mii`);
    }
    if (rest) {
        parts.push(underThousand(rest));
    }

    return parts.join(' ');
}

export function amountInWords(minorUnits, currency) {
    const whole = Math.floor(minorUnits / 100);
    const fraction = minorUnits % 100;
    const unit = currency === 'EUR' ? 'euro' : 'lei';
    const subunit = currency === 'EUR' ? 'cenți' : 'bani';
    const singularSubunit = currency === 'EUR' ? 'cent' : 'ban';
    const integerText = whole === 1
        ? (currency === 'EUR' ? 'un euro' : 'un leu')
        : `${wholeNumberInWords(whole)}${withDe(whole)}${unit}`;

    return fraction
        ? `${integerText} și ${fraction === 1 ? `un ${singularSubunit}` : `${underThousand(fraction)}${withDe(fraction)}${subunit}`}`
        : integerText;
}
