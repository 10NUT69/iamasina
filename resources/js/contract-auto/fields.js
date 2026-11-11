// Positions in the coordinate grid of the approved preview, measured at the dotted lines.
// Each row starts with the seller and buyer baselines. A field can override the buyer's x range.
const partyRows = [
    [274, 504, {
        nume: [652, 1105, 645, 1103],
    }],
    [297, 527, {
        tara: [333, 444],
        judet: [526, 682],
        cod_postal: [814, 909],
    }],
    [320, 550, {
        localitate: [56, 280],
        sat_sector: [409, 579, 411, 581],
        strada: [626, 995, 628, 995],
        nr: [1040, 1100],
    }],
    [343, 572, {
        bl: [56, 87],
        sc: [166, 202],
        et: [279, 310],
        ap: [388, 419],
        act_serie: [945, 970, 945, 970, 990],
        act_nr: [1051, 1131],
    }],
    [366, 595, {
        cnp: [168, 427],
        telefon: [507, 667],
        email: [745, 1015],
    }],
    [389, 618, {
        df_tara: [348, 453],
        df_judet: [600, 800],
        df_cod_postal: [1027, 1127, 1027, 1133],
    }],
    [411, 641, {
        df_localitate: [269, 465],
        df_sat_sector: [594, 764],
        df_strada: [806, 1132],
    }],
    [434, 664, {
        df_nr: [88, 145],
        df_bl: [190, 224],
        df_sc: [269, 310],
        df_et: [353, 389],
        df_ap: [432, 477],
        repr_nume: [656, 1000],
    }],
    [457, 687, {
        repr_act_serie: [323, 358],
        repr_act_nr: [422, 492],
        repr_cif: [574, 779],
        repr_tel: [863, 1063, 863, 1057],
    }],
    [480, 710, {
        repr_email: [57, 305],
        calitate: [427, 841],
    }],
];

const partyFields = {};
for (const [sellerY, buyerY, fields] of partyRows) {
    for (const [name, [left, right, buyerLeft = left, buyerRight = right, availableRight]] of Object.entries(fields)) {
        partyFields[`v_${name}`] = [left, availableRight ?? right, sellerY, right];
        partyFields[`c_${name}`] = [buyerLeft, availableRight ?? buyerRight, buyerY, buyerRight];
    }
}

export const contractFields = {
    ...partyFields,
    m_marca: [301, 455, 757],
    m_tip: [517, 692, 757],
    m_vin: [892, 1127, 757],
    m_serie_motor: [157, 287, 779],
    m_cilindree_kw: [511, 566, 779],
    m_gma: [1072, 1131, 779],
    m_nr_inmatriculare: [402, 536, 802],
    m_itp: [948, 1133, 802],
    m_civ: [406, 516, 825],
    m_an_fabricatie: [681, 807, 825],
    m_norma_euro: [922, 963, 825],
    m_dobandit_data: [57, 241, 848],
    // A fourth coordinate marks where the dots end when the value can use more room.
    m_act: [357, 1133, 848, 834],
    pret_cifre: [238, 411, 872],
    pret_litere: [523, 1133, 872, 881],
    // The vector template moves the signing row down to make room for a second price line.
    contract_data: [360, 580, 1121.35],
    contract_loc: [599, 1133, 1121.35, 944],
};

// The vector PDF uses the approved preview's visible bounds (x = 43–1147) and places
// their 282 mm height (y = 4–1607) 5 mm below the top of the A4 page.
const pointsPerMillimeter = 72 / 25.4;
export const artworkScale = 282 * pointsPerMillimeter / (1607 - 4);
export const artworkCenter = (43 + 1147) / 2;
export const artworkTop = 4;
export const artworkTopMargin = 5 * pointsPerMillimeter;
