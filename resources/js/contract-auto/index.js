import { amountInWords, formatAmount, parseAmount } from './amount.js';

const page = document.querySelector('[data-contract]');
const form = page.querySelector('[data-contract-form]');
const steps = [...form.querySelectorAll('[data-step]')];
const markers = [...form.querySelectorAll('[data-step-marker]')];
const reviewIndex = steps.length - 1;
const stepCount = form.querySelector('[data-step-count]');
const review = form.querySelector('[data-review]');
const back = form.querySelector('[data-back]');
const next = form.querySelector('[data-continue]');
const error = form.querySelector('[data-contract-error]');
const result = page.querySelector('[data-result]');
const download = result.querySelector('[data-download]');
const print = result.querySelector('[data-print]');
const share = result.querySelector('[data-share]');
const shareStatus = result.querySelector('[data-share-status]');
const currency = form.querySelector('[data-contract-currency]');
const price = form.querySelector('[data-contract-field="price"]');
const euroOnly = form.querySelector('[data-euro-only]');
const ronPrice = form.querySelector('[data-contract-field="price_ron"]');
const signingDate = form.querySelector('[data-contract-field="contract_data"]');

let currentStep = 0;
let file;
let fileUrl;
let busy = false;

function fieldLabel(input) {
    return input.labels[0].querySelector('[data-field-label]').textContent;
}

function setGroup(group, visible) {
    group.hidden = !visible;
    group.querySelectorAll('input').forEach((input) => {
        input.disabled = !visible;
    });
}

for (const party of form.querySelectorAll('[data-party]')) {
    const labels = [...party.querySelectorAll('[data-company-label]')];
    const individualLabels = labels.map((label) => label.textContent);
    const companyFields = party.querySelector('[data-company-only]');
    const individualFields = party.querySelector('[data-individual-only]');
    const fiscalToggle = party.querySelector('[data-fiscal-toggle]');
    const fiscalAddress = party.querySelector('[data-fiscal-address]');

    function updateParty() {
        const isCompany = party.querySelector('[data-party-type]:checked').value === 'company';
        setGroup(individualFields, !isCompany);
        companyFields.hidden = companyFields.disabled = !isCompany;
        fiscalAddress.hidden = fiscalAddress.disabled = !fiscalToggle.checked;
        labels.forEach((label, index) => {
            label.textContent = isCompany ? label.dataset.companyLabel : individualLabels[index];
        });
    }

    party.addEventListener('change', (event) => {
        if (event.target.matches('[data-party-type], [data-fiscal-toggle]')) {
            updateParty();
        }
    });
    updateParty();
}

function updateCurrency() {
    const isEuro = currency.value === 'EUR';
    setGroup(euroOnly, isEuro);
    price.labels[0].querySelector('[data-field-label]').textContent = isEuro ? 'Preț în euro' : 'Preț în lei';
}

function enabledFields(step) {
    return [...steps[step].querySelectorAll('[data-contract-field]:enabled')];
}

function normalizeDate(value) {
    const separated = value.replace(/^(\d{2})(\d{2})(\d{4})$/, '$1.$2.$3');
    const match = /^(\d{1,2})([.\/-])(\d{1,2})\2(\d{4})$/.exec(separated);
    if (!match) {
        return null;
    }

    const [, day, , month, year] = match;
    const date = new Date(0);
    date.setUTCFullYear(Number(year), Number(month) - 1, Number(day));
    if (year === '0000' || date.getUTCFullYear() !== Number(year)
        || date.getUTCMonth() + 1 !== Number(month) || date.getUTCDate() !== Number(day)) {
        return null;
    }

    return `${day.padStart(2, '0')}.${month.padStart(2, '0')}.${year}`;
}

function validateStep() {
    for (const input of enabledFields(currentStep)) {
        const value = input.value.trim();
        let message = '';
        if (input.required && !value) {
            message = 'Completează acest câmp.';
        } else if (value && input.matches('[data-contract-date]')) {
            const date = normalizeDate(value);
            if (date) {
                input.value = date;
            } else {
                message = 'Introdu o dată validă în formatul ZZ.LL.AAAA.';
            }
        } else if (input.matches('[data-contract-amount]') && parseAmount(value) === null) {
            message = 'Introdu o sumă pozitivă, cu cel mult două zecimale.';
        }
        input.setCustomValidity(message);
        if (!input.reportValidity()) {
            return false;
        }
    }
    return true;
}

function transactionAmounts() {
    return {
        selected: parseAmount(price.value),
        ron: currency.value === 'EUR' ? parseAmount(ronPrice.value) : parseAmount(price.value),
    };
}

function reviewRows(step) {
    const rows = enabledFields(step).flatMap((input) => {
        const value = input.value.trim();
        if (!value) {
            return [];
        }
        const label = fieldLabel(input);
        const group = input.closest('[data-fiscal-address], [data-company-only]');
        return [[group ? `${group.querySelector('legend').textContent} · ${label}` : label, value]];
    });
    const partyType = steps[step].querySelector('[data-party-type]:checked');
    if (partyType) {
        rows.unshift(['Tip persoană', partyType.labels[0].textContent.trim()]);
    }
    if (steps[step].contains(price)) {
        const { selected, ron } = transactionAmounts();
        rows.unshift(['Monedă', currency.options[currency.selectedIndex].text]);
        rows.push(['Suma în litere', amountInWords(selected, currency.value)]);
        if (currency.value === 'EUR') {
            rows.push(['Echivalent în litere', amountInWords(ron, 'RON')]);
        }
    }
    return rows;
}

function renderReview() {
    review.replaceChildren();
    for (let step = 0; step < reviewIndex; step++) {
        const section = document.createElement('section');
        section.className = 'min-w-0 rounded-xl border border-gray-200 p-3 dark:border-gray-800';
        const heading = document.createElement('h3');
        heading.className = 'font-bold';
        heading.textContent = markers[step].textContent.trim();
        const edit = document.createElement('button');
        edit.type = 'button';
        edit.className = 'mb-2 min-h-11 text-sm font-semibold text-[#a6101d] underline dark:text-red-300';
        edit.textContent = 'Modifică';
        edit.setAttribute('aria-label', `Modifică secțiunea ${heading.textContent}`);
        edit.dataset.editStep = step;
        const list = document.createElement('dl');
        list.className = 'grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-x-3 gap-y-1 break-words text-sm';

        for (const [label, value] of reviewRows(step)) {
            const term = document.createElement('dt');
            term.className = 'text-gray-600 dark:text-gray-300';
            term.textContent = label;
            const definition = document.createElement('dd');
            definition.textContent = value;
            list.append(term, definition);
        }
        section.append(heading, edit, list);
        review.append(section);
    }
}

function showStep(step, focus = true) {
    currentStep = step;
    for (const [index, panel] of steps.entries()) {
        panel.hidden = index !== step;
        markers[index].setAttribute('aria-current', index === step ? 'step' : 'false');
    }
    stepCount.textContent = `Pasul ${step + 1} din ${steps.length}`;
    back.hidden = step === 0;
    next.textContent = step === reviewIndex ? 'Generează PDF' : 'Continuă';
    error.hidden = true;
    if (step === reviewIndex) {
        renderReview();
    }
    if (focus) {
        steps[step].querySelector('h2').focus();
    }
}

function pdfValues() {
    const values = steps.flatMap((_, step) => enabledFields(step).flatMap((input) => {
        const value = input.value.trim();
        return value && !input.matches('[data-contract-amount]')
            ? [{ field: input.dataset.contractField, label: fieldLabel(input), value, step }]
            : [];
    }));
    const { selected, ron } = transactionAmounts();
    const step = reviewIndex - 1;
    const euroEquivalent = currency.value === 'EUR' ? ` (echivalent\u00a0${formatAmount(selected)}\u00a0euro)` : '';
    values.push(
        { field: 'pret_cifre', label: 'Preț', value: formatAmount(ron), step },
        { field: 'pret_litere', label: 'Preț în litere', value: amountInWords(ron, 'RON') + euroEquivalent, step },
    );
    return values;
}

function clearResult() {
    if (fileUrl) {
        URL.revokeObjectURL(fileUrl);
    }
    file = fileUrl = null;
    download.removeAttribute('href');
    print.removeAttribute('href');
    result.hidden = true;
}

function showResult(contract) {
    clearResult();
    file = contract;
    fileUrl = URL.createObjectURL(file);
    download.href = print.href = fileUrl;
    download.download = file.name;
    shareStatus.textContent = '';
    form.hidden = true;
    result.hidden = false;
    result.querySelector('h2').focus();
}

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (busy || !validateStep()) {
        return;
    }
    if (currentStep < reviewIndex) {
        showStep(currentStep + 1);
        return;
    }

    busy = next.disabled = back.disabled = true;
    next.textContent = 'Se generează…';
    form.setAttribute('aria-busy', 'true');
    error.hidden = true;
    try {
        const { generateContract } = await import('./pdf.js');
        showResult(await generateContract(pdfValues()));
    } catch (failure) {
        if (Number.isInteger(failure.step)) {
            showStep(failure.step);
            const field = form.querySelector(`[data-contract-field="${failure.field}"]`);
            (field ?? price).focus();
        }
        error.textContent = failure.step !== undefined
            ? failure.message
            : 'PDF-ul nu a putut fi generat. Încearcă din nou.';
        error.hidden = false;
    } finally {
        busy = next.disabled = back.disabled = false;
        next.textContent = currentStep === reviewIndex ? 'Generează PDF' : 'Continuă';
        form.removeAttribute('aria-busy');
    }
});

form.addEventListener('input', (event) => {
    if (event.target.matches('[data-contract-field]')) {
        event.target.setCustomValidity('');
    }
});
back.addEventListener('click', () => showStep(currentStep - 1));
review.addEventListener('click', (event) => {
    const edit = event.target.closest('[data-edit-step]');
    if (edit && !busy) {
        showStep(Number(edit.dataset.editStep));
    }
});
result.querySelector('[data-edit-result]').addEventListener('click', () => {
    clearResult();
    form.hidden = false;
    showStep(reviewIndex);
});
share.addEventListener('click', async () => {
    const data = { files: [file] };
    const fallback = 'Descarcă PDF-ul, apoi atașează documentul în conversația WhatsApp.';
    if (!navigator.canShare?.(data) || !navigator.share) {
        shareStatus.textContent = fallback;
        return;
    }
    try {
        await navigator.share(data);
    } catch (failure) {
        if (failure.name !== 'AbortError') {
            shareStatus.textContent = fallback;
        }
    }
});

const today = new Date();
signingDate.value = [today.getDate(), today.getMonth() + 1, today.getFullYear()]
    .map((part) => String(part).padStart(2, '0'))
    .join('.');
currency.addEventListener('change', updateCurrency);
updateCurrency();
showStep(0, false);
page.querySelector('[data-contract-loading]').hidden = true;
form.hidden = false;
