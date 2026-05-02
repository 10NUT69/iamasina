<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CountiesSeeder extends Seeder
{
    private const COUNTIES = [
        [
            'id' => 1,
            'name' => 'Alba',
            'municipii' => ['Aiud' => 1222, 'Alba Iulia' => 1026, 'Blaj' => 1357, 'Sebes' => 1883],
            'orase' => ['Abrud' => 1160, 'Baia de Aries' => 2924, 'Campeni' => 1464, 'Cugir' => 1703, 'Ocna Mures' => 1801, 'Teius' => 8103, 'Zlatna' => 1945],
        ],
        [
            'id' => 2,
            'name' => 'Arad',
            'municipii' => ['Arad' => 9271],
            'orase' => ['Chisineu-Cris' => 9468, 'Curtici' => 9501, 'Ineu' => 9547, 'Lipova' => 9583, 'Nadlac' => 9636, 'Pancota' => 9663, 'Pecica' => 11593, 'Santana' => 12108, 'Sebis' => 9707],
        ],
        [
            'id' => 3,
            'name' => 'Argeș',
            'municipii' => ['Campulung' => 13506, 'Curtea de Arges' => 13631, 'Pitesti' => 13178],
            'orase' => ['Costesti' => 13677, 'Mioveni' => 13310, 'Stefanesti' => 13409, 'Topoloveni' => 13766],
        ],
        [
            'id' => 4,
            'name' => 'Bacău',
            'municipii' => ['Bacau' => 20304, 'Moinesti' => 20885, 'Onesti' => 20572],
            'orase' => ['Buhusi' => 20787, 'Comanesti' => 20830, 'Darmanesti' => 22175, 'Slanic-Moldova' => 20929, 'Targu Ocna' => 20974],
        ],
        [
            'id' => 5,
            'name' => 'Bihor',
            'municipii' => ['Beius' => 26813, 'Marghita' => 26886, 'Oradea' => 26573, 'Salonta' => 26984],
            'orase' => ['Alesd' => 26706, 'Nucet' => 26939, 'Sacueni' => 30924, 'Stei' => 26859, 'Valea lui Mihai' => 32036, 'Vascau' => 27016],
        ],
        [
            'id' => 6,
            'name' => 'Bistrița-Năsăud',
            'municipii' => ['Bistrita' => 32401],
            'orase' => ['Beclean' => 32492, 'Nasaud' => 32553, 'Sangeorz-Bai' => 32606],
        ],
        [
            'id' => 7,
            'name' => 'Botoșani',
            'municipii' => ['Botosani' => 35740, 'Dorohoi' => 36015],
            'orase' => ['Bucecea' => 36462, 'Darabani' => 35955, 'Flamanzi' => 37299, 'Saveni' => 36079, 'Stefanesti' => 39177],
        ],
        [
            'id' => 8,
            'name' => 'Brașov',
            'municipii' => ['Brasov' => 40205, 'Codlea' => 40250, 'Fagaras' => 40287, 'Sacele' => 40447],
            'orase' => ['Ghimbav' => 40223, 'Predeal' => 40312, 'Rasnov' => 40376, 'Rupea' => 40401, 'Victoria' => 40474, 'Zarnesti' => 40508],
        ],
        [
            'id' => 9,
            'name' => 'Brăila',
            'municipii' => ['Braila' => 42691],
            'orase' => ['Faurei' => 42762, 'Ianca' => 43340, 'Insuratei' => 43420],
        ],
        [
            'id' => 10,
            'name' => 'București',
            'municipii' => ['Bucuresti' => 179132],
            'orase' => [],
        ],
        [
            'id' => 11,
            'name' => 'Buzău',
            'municipii' => ['Buzau' => 44827, 'Ramnicu Sarat' => 44854],
            'orase' => ['Nehoiu' => 47925, 'Patarlagele' => 48334, 'Pogoanele' => 48753],
        ],
        [
            'id' => 12,
            'name' => 'Caraș-Severin',
            'municipii' => ['Caransebes' => 51029, 'Resita' => 50807],
            'orase' => ['Anina' => 50898, 'Baile Herculane' => 50932, 'Bocsa' => 50978, 'Moldova Noua' => 51065, 'Oravita' => 51127, 'Otelu Rosu' => 51216],
        ],
        [
            'id' => 13,
            'name' => 'Călărași',
            'municipii' => ['Calarasi' => 92578, 'Oltenita' => 100629],
            'orase' => ['Budesti' => 101467, 'Fundulea' => 103041, 'Lehliu Gara' => 93897],
        ],
        [
            'id' => 14,
            'name' => 'Cluj',
            'municipii' => ['Campia Turzii' => 55366, 'Cluj-Napoca' => 54984, 'Dej' => 55017, 'Gherla' => 55393, 'Turda' => 55268],
            'orase' => ['Huedin' => 55455],
        ],
        [
            'id' => 15,
            'name' => 'Constanța',
            'municipii' => ['Constanta' => 60428, 'Mangalia' => 60491, 'Medgidia' => 60856],
            'orase' => ['Baneasa' => 62413, 'Cernavoda' => 60785, 'Eforie' => 60464, 'Harsova' => 60810, 'Murfatlar' => 62379, 'Navodari' => 60516, 'Negru Voda' => 62404, 'Ovidiu' => 60696, 'Techirghiol' => 60543],
        ],
        [
            'id' => 16,
            'name' => 'Covasna',
            'municipii' => ['Sfantu Gheorghe' => 63401, 'Targu Secuiesc' => 63759],
            'orase' => ['Baraolt' => 63456, 'Covasna' => 63535, 'Intorsura Buzaului' => 63599],
        ],
        [
            'id' => 17,
            'name' => 'Dâmbovița',
            'municipii' => ['Moreni' => 65850, 'Targoviste' => 65351],
            'orase' => ['Fieni' => 65618, 'Gaesti' => 65690, 'Pucioasa' => 65930, 'Racari' => 68636, 'Titu' => 66090],
        ],
        [
            'id' => 18,
            'name' => 'Dolj',
            'municipii' => ['Bailesti' => 70325, 'Calafat' => 70361, 'Craiova' => 69919],
            'orase' => ['Bechet' => 70888, 'Dabuleni' => 72016, 'Filiasi' => 70423, 'Segarcea' => 70511],
        ],
        [
            'id' => 19,
            'name' => 'Galați',
            'municipii' => ['Galati' => 75105, 'Tecuci' => 75212],
            'orase' => ['Beresti' => 75347, 'Targu Bujor' => 75481],
        ],
        [
            'id' => 20,
            'name' => 'Giurgiu',
            'municipii' => ['Giurgiu' => 100530],
            'orase' => ['Bolintin-Vale' => 101207, 'Mihailesti' => 104145],
        ],
        [
            'id' => 21,
            'name' => 'Gorj',
            'municipii' => ['Motru' => 78150, 'Targu Jiu' => 77821],
            'orase' => ['Bumbesti-Jiu' => 79317, 'Novaci' => 78267, 'Rovinari' => 79059, 'Targu Carbunesti' => 78338, 'Ticleni' => 78463, 'Tismana' => 82449, 'Turceni' => 82626],
        ],
        [
            'id' => 22,
            'name' => 'Harghita',
            'municipii' => ['Gheorgheni' => 83570, 'Miercurea Ciuc' => 83339, 'Odorheiu Secuiesc' => 83142, 'Toplita' => 83641],
            'orase' => ['Baile Tusnad' => 83437, 'Balan' => 83473, 'Borsec' => 83507, 'Cristuru Secuiesc' => 83534, 'Vlahita' => 83758],
        ],
        [
            'id' => 23,
            'name' => 'Hunedoara',
            'municipii' => ['Brad' => 87308, 'Deva' => 86696, 'Hunedoara' => 86829, 'Lupeni' => 87068, 'Orastie' => 87647, 'Petrosani' => 87004, 'Vulcan' => 87184],
            'orase' => ['Aninoasa' => 87228, 'Calan' => 87433, 'Geoagiu' => 89570, 'Hateg' => 87585, 'Petrila' => 87086, 'Simeria' => 87674, 'Uricani' => 87148],
        ],
        [
            'id' => 24,
            'name' => 'Ialomița',
            'municipii' => ['Fetesti' => 92710, 'Slobozia' => 92667, 'Urziceni' => 100692],
            'orase' => ['Amara' => 92845, 'Cazanesti' => 93076, 'Fierbinti-Targ' => 102758, 'Tandarei' => 92774],
        ],
        [
            'id' => 25,
            'name' => 'Iași',
            'municipii' => ['Iasi' => 95079, 'Pascani' => 95408],
            'orase' => ['Harlau' => 95364, 'Podu Iloaiei' => 98382, 'Targu Frumos' => 95480],
        ],
        [
            'id' => 26,
            'name' => 'Ilfov',
            'municipii' => [],
            'orase' => ['Bragadiru' => 179230, 'Buftea' => 100585, 'Chitila' => 179294, 'Magurele' => 179418, 'Otopeni' => 179490, 'Pantelimon' => 179524, 'Popesti-Leordeni' => 179542, 'Voluntari' => 179560],
        ],
        [
            'id' => 27,
            'name' => 'Maramureș',
            'municipii' => ['Baia Mare' => 106327, 'Sighetu Marmatiei' => 106568],
            'orase' => ['Baia Sprie' => 106693, 'Borsa' => 106755, 'Cavnic' => 106791, 'Dragomiresti' => 108026, 'Salistea de Sus' => 108909, 'Seini' => 108972, 'Somcuta Mare' => 109185, 'Targu Lapus' => 106826, 'Tautii-Magheraus' => 106470, 'Ulmeni' => 109274, 'Viseu de Sus' => 106988],
        ],
        [
            'id' => 28,
            'name' => 'Mehedinți',
            'municipii' => ['Drobeta-Turnu Severin' => 109782, 'Orsova' => 110072],
            'orase' => ['Baia de Arama' => 109933, 'Strehaia' => 110125, 'Vanju Mare' => 110241],
        ],
        [
            'id' => 29,
            'name' => 'Mureș',
            'municipii' => ['Reghin' => 114818, 'Sighisoara' => 114523, 'Targu Mures' => 114328, 'Tarnaveni' => 114934],
            'orase' => ['Iernut' => 117836, 'Ludus' => 114729, 'Miercurea Nirajului' => 118290, 'Sangeorgiu de Padure' => 119340, 'Sarmasu' => 119251, 'Sovata' => 114863, 'Ungheni' => 119901],
        ],
        [
            'id' => 30,
            'name' => 'Neamț',
            'municipii' => ['Piatra Neamt' => 120735, 'Roman' => 120879],
            'orase' => ['Bicaz' => 120977, 'Roznov' => 124126, 'Targu Neamt' => 121064],
        ],
        [
            'id' => 31,
            'name' => 'Olt',
            'municipii' => ['Caracal' => 125481, 'Slatina' => 125356],
            'orase' => ['Bals' => 125427, 'Corabia' => 125551, 'Draganesti-Olt' => 125631, 'Piatra-Olt' => 128114, 'Potcoava' => 128383, 'Scornicesti' => 128720],
        ],
        [
            'id' => 32,
            'name' => 'Prahova',
            'municipii' => ['Campina' => 131265, 'Ploiesti' => 130543],
            'orase' => ['Azuga' => 130963, 'Baicoi' => 130990, 'Boldesti-Scaeni' => 131078, 'Breaza' => 131112, 'Busteni' => 131229, 'Comarnic' => 131345, 'Mizil' => 131416, 'Plopeni' => 131452, 'Sinaia' => 131559, 'Slanic' => 131586, 'Urlati' => 131639, 'Valenii de Munte' => 131826],
        ],
        [
            'id' => 33,
            'name' => 'Satu Mare',
            'municipii' => ['Carei' => 136535, 'Satu Mare' => 136492],
            'orase' => ['Ardud' => 136857, 'Livada' => 138048, 'Negresti-Oas' => 136606, 'Tasnad' => 136651],
        ],
        [
            'id' => 34,
            'name' => 'Sălaj',
            'municipii' => ['Zalau' => 139713],
            'orase' => ['Cehu Silvaniei' => 139759, 'Jibou' => 139820, 'Simleu Silvaniei' => 139893],
        ],
        [
            'id' => 35,
            'name' => 'Sibiu',
            'municipii' => ['Medias' => 143628, 'Sibiu' => 143469],
            'orase' => ['Agnita' => 143691, 'Avrig' => 144063, 'Cisnadie' => 143744, 'Copsa Mica' => 143780, 'Dumbraveni' => 143815, 'Miercurea Sibiului' => 144937, 'Ocna Sibiului' => 143860, 'Saliste' => 145505, 'Talmaciu' => 145836],
        ],
        [
            'id' => 36,
            'name' => 'Suceava',
            'municipii' => ['Campulung Moldovenesc' => 146511, 'Falticeni' => 146548, 'Radauti' => 146637, 'Suceava' => 146272, 'Vatra Dornei' => 146753],
            'orase' => ['Brosteni' => 147367, 'Cajvana' => 147642, 'Dolhasca' => 148015, 'Frasin' => 148621, 'Gura Humorului' => 146593, 'Liteni' => 149236, 'Milisauti' => 146986, 'Salcea' => 146389, 'Siret' => 146664, 'Solca' => 146717, 'Vicovu de Sus' => 151102],
        ],
        [
            'id' => 37,
            'name' => 'Teleorman',
            'municipii' => ['Alexandria' => 151807, 'Rosiorii de Vede' => 151889, 'Turnu Magurele' => 151692],
            'orase' => ['Videle' => 151914, 'Zimnicea' => 151987],
        ],
        [
            'id' => 38,
            'name' => 'Timiș',
            'municipii' => ['Lugoj' => 155369, 'Timisoara' => 155252],
            'orase' => ['Buzias' => 155412, 'Ciacova' => 156366, 'Deta' => 155467, 'Faget' => 156810, 'Gataia' => 157095, 'Jimbolia' => 155500, 'Recas' => 158323, 'Sannicolau Mare' => 155537],
        ],
        [
            'id' => 39,
            'name' => 'Tulcea',
            'municipii' => ['Tulcea' => 159623],
            'orase' => ['Babadag' => 159669, 'Isaccea' => 159696, 'Macin' => 159749, 'Sulina' => 159776],
        ],
        [
            'id' => 40,
            'name' => 'Vaslui',
            'municipii' => ['Barlad' => 161801, 'Husi' => 161838, 'Vaslui' => 161954],
            'orase' => ['Murgeni' => 164990, 'Negresti' => 161865],
        ],
        [
            'id' => 41,
            'name' => 'Vâlcea',
            'municipii' => ['Dragasani' => 167990, 'Ramnicu Valcea' => 167482],
            'orase' => ['Babeni' => 168381, 'Baile Govora' => 168176, 'Baile Olanesti' => 167730, 'Balcesti' => 168461, 'Berbesti' => 168611, 'Brezoi' => 167801, 'Calimanesti' => 167918, 'Horezu' => 168050, 'Ocnele Mari' => 167678],
        ],
        [
            'id' => 42,
            'name' => 'Vrancea',
            'municipii' => ['Adjud' => 174879, 'Focsani' => 174753],
            'orase' => ['Marasesti' => 174931, 'Odobesti' => 175028, 'Panciu' => 175064],
        ],
    ];

    private const LOCALITY_TYPES = [
        'municipii' => 9,
        'orase' => 17,
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();
            $countyIds = array_column(self::COUNTIES, 'id');

            foreach (self::COUNTIES as $county) {
                DB::table('counties')->updateOrInsert(
                    ['id' => $county['id']],
                    [
                        'name' => $county['name'],
                        'slug' => Str::slug($county['name']),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            DB::table('counties')
                ->whereNotIn('id', $countyIds)
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('services')
                        ->whereColumn('services.county_id', 'counties.id');
                })
                ->delete();

            DB::table('localities')->delete();

            $localities = [];
            foreach (self::COUNTIES as $county) {
                foreach (self::LOCALITY_TYPES as $group => $type) {
                    foreach ($county[$group] as $name => $sirutaCode) {
                        $localities[] = [
                            'siruta_code' => $sirutaCode,
                            'type' => $type,
                            'county_id' => $county['id'],
                            'name' => $name,
                            'slug' => Str::slug($name),
                            'latitude' => null,
                            'longitude' => null,
                        ];
                    }
                }
            }

            foreach (array_chunk($localities, 100) as $chunk) {
                DB::table('localities')->insert($chunk);
            }
        });
    }
}
