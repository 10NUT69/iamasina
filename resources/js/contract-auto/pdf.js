import { breakTextIntoLines, LineCapStyle, PDFDocument, PDFName, PDFStream, rgb } from 'pdf-lib';
import fontkit from '@pdf-lib/fontkit';
import templateUrl from './assets/itl-054-2026.pdf?url';
import boldFontUrl from './assets/LiberationSans-Bold.ttf?url';
import { artworkCenter, artworkScale, artworkTop, artworkTopMargin, contractFields } from './fields.js';

const title = 'Contract vânzare cumpărare auto - Contract de înstrăinare - dobândire a unui mijloc de transport (ITL 054) 2026';
const bodyFontSize = 8;
const priceFontSize = 7;
const minPriceFontSize = 6;
const priceLineHeight = 9;
const dotsColor = rgb(0, 0, 0);
const dotsPattern = [0.05, 2.43];

async function loadAsset(url) {
    const response = await fetch(url, { credentials: 'omit' });
    if (!response.ok) {
        throw new Error('Contractul nu a putut fi încărcat. Încearcă din nou.');
    }
    return response.arrayBuffer();
}

function fitPriceInWords(text, width, font) {
    for (let size = priceFontSize; size >= minPriceFontSize; size -= 0.25) {
        const measure = (line) => font.widthOfTextAtSize(line, size);
        const lines = breakTextIntoLines(text, [' '], width, measure);
        if (lines.length <= 2 && lines.every((line) => measure(line.trimEnd()) <= width)) {
            return { lines, size };
        }
    }
    return null;
}

function contractFilename(values) {
    const seller = values.find(({ field }) => field === 'v_nume').value;
    const sellerName = seller.normalize('NFC')
        .replace(/[^\p{L}\p{M}\p{N}]+/gu, '-')
        .slice(0, 100)
        .replace(/^-+|-+$/g, '');
    const signingDate = values.find(({ field }) => field === 'contract_data').value.replaceAll('.', '-');
    return `iaAuto.ro-contract-vanzare-cumparare-${sellerName || 'vanzator'}-${signingDate}.pdf`;
}

function removeBlankFieldDots(page) {
    // The downloadable blank model keeps its guides in a separate content stream.
    const contents = page.node.Contents();
    const marker = PDFName.of('IAAutoFieldDots');
    for (let index = 0; index < contents.size(); index++) {
        const stream = contents.lookup(index, PDFStream);
        if (stream.dict.has(marker)) {
            page.doc.context.delete(contents.get(index));
            contents.remove(index);
            return;
        }
    }
    throw new Error('Șablonul PDF nu conține liniile de completare.');
}

function drawFieldDots(page, start, end, baseline, phase = 0) {
    if (end <= start) {
        return;
    }
    page.drawLine({
        start: { x: start, y: baseline - 0.75 },
        end: { x: end, y: baseline - 0.75 },
        thickness: 0.8,
        color: dotsColor,
        dashArray: dotsPattern,
        dashPhase: phase,
        lineCap: LineCapStyle.Round,
    });
}

export async function generateContract(values) {
    const [template, boldFontBytes] = await Promise.all([
        loadAsset(templateUrl),
        loadAsset(boldFontUrl),
    ]);
    const pdf = await PDFDocument.load(template, { updateMetadata: false });
    pdf.registerFontkit(fontkit);
    const boldFont = await pdf.embedFont(boldFontBytes, { subset: true });
    const characters = new Set(boldFont.getCharacterSet());
    const [page] = pdf.getPages();
    removeBlankFieldDots(page);
    page.setFont(boldFont);
    const centerX = page.getWidth() / 2;
    const topY = page.getHeight() - artworkTopMargin;
    const entries = new Map(values.map((entry) => [entry.field, entry]));

    for (const [field, [left, right, baseline, dotsEnd = right]] of Object.entries(contractFields)) {
        const fieldX = centerX + (left - artworkCenter) * artworkScale;
        const textY = topY - (baseline - 2 - artworkTop) * artworkScale;
        const dottedWidth = (dotsEnd - left) * artworkScale;
        const dotsStart = fieldX + 0.6;
        const dotsEndX = fieldX + dottedWidth - 0.8;
        const entry = entries.get(field);
        if (!entry?.value) {
            drawFieldDots(page, dotsStart, dotsEndX, textY);
            continue;
        }

        const { label, value, step } = entry;
        const text = value.toLocaleUpperCase('ro-RO').normalize('NFC');
        if (Array.from(text).some((character) => !characters.has(character.codePointAt(0)))) {
            throw Object.assign(new Error(`Câmpul „${label}” conține caractere pe care PDF-ul nu le poate afișa.`), { step, field });
        }
        const fieldWidth = (right - left) * artworkScale;
        const fieldPadding = 0.25;
        const width = fieldWidth - 2 * fieldPadding;
        const layout = field === 'pret_litere'
            ? fitPriceInWords(text, width, boldFont)
            : { lines: [text], size: bodyFontSize };
        const lines = layout?.lines.map((line) => line.trimEnd()) ?? [];
        const lineWidths = lines.map((line) => boldFont.widthOfTextAtSize(line, layout.size));
        const widestLine = Math.max(...lineWidths);
        if (!layout || widestLine > width) {
            throw Object.assign(new Error(`Textul din „${label}” nu încape în rubrica din PDF. Verifică dacă îl poți scurta.`), { step, field });
        }

        const centeredWidth = widestLine <= dottedWidth - 2 * fieldPadding ? dottedWidth : fieldWidth;
        const firstLineX = fieldX + (centeredWidth - lineWidths[0]) / 2;
        const rightDotsStart = Math.max(dotsStart, firstLineX + lineWidths[0] + 1);
        const dotsPeriod = dotsPattern[0] + dotsPattern[1];
        drawFieldDots(page, dotsStart, Math.min(dotsEndX, firstLineX - 1), textY);
        drawFieldDots(page, rightDotsStart, dotsEndX, textY, (rightDotsStart - dotsStart) % dotsPeriod);

        for (const [index, line] of lines.entries()) {
            page.drawText(line, {
                x: fieldX + (centeredWidth - lineWidths[index]) / 2,
                y: textY - index * priceLineHeight,
                size: layout.size,
            });
        }
    }

    pdf.setTitle(title);
    pdf.setProducer('iaAuto.ro');
    pdf.setCreator('iaAuto.ro');

    return new File([await pdf.save({ useObjectStreams: false })], contractFilename(values), { type: 'application/pdf' });
}
