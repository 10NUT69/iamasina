<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Aceste mesaje sunt folosite de validatorul Laravel.
    | Poți ajusta textele după preferințe.
    |
    */

    'accepted'             => 'Câmpul :attribute trebuie să fie acceptat.',
    'accepted_if'          => 'Câmpul :attribute trebuie să fie acceptat când :other este :value.',
    'active_url'           => 'Câmpul :attribute nu este un URL valid.',
    'after'                => 'Câmpul :attribute trebuie să fie o dată după :date.',
    'after_or_equal'       => 'Câmpul :attribute trebuie să fie o dată după sau egală cu :date.',
    'alpha'                => 'Câmpul :attribute poate conține doar litere.',
    'alpha_dash'           => 'Câmpul :attribute poate conține doar litere, numere, liniuțe și underscore.',
    'alpha_num'            => 'Câmpul :attribute poate conține doar litere și numere.',
    'array'                => 'Câmpul :attribute trebuie să fie un array.',
    'ascii'                => 'Câmpul :attribute poate conține doar caractere ASCII cu un singur byte.',
    'before'               => 'Câmpul :attribute trebuie să fie o dată înainte de :date.',
    'before_or_equal'      => 'Câmpul :attribute trebuie să fie o dată înainte sau egală cu :date.',
    'between'              => [
        'array'   => 'Câmpul :attribute trebuie să aibă între :min și :max elemente.',
        'file'    => 'Câmpul :attribute trebuie să aibă între :min și :max kilobytes.',
        'numeric' => 'Câmpul :attribute trebuie să fie între :min și :max.',
        'string'  => 'Câmpul :attribute trebuie să aibă între :min și :max caractere.',
    ],
    'boolean'              => 'Câmpul :attribute trebuie să fie adevărat sau fals.',
    'can'                  => 'Câmpul :attribute conține o valoare neautorizată.',
    'confirmed'            => 'Confirmarea pentru :attribute nu se potrivește.',
    'current_password'     => 'Parola este incorectă.',
    'date'                 => 'Câmpul :attribute nu este o dată validă.',
    'date_equals'          => 'Câmpul :attribute trebuie să fie o dată egală cu :date.',
    'date_format'          => 'Câmpul :attribute nu respectă formatul :format.',
    'decimal'              => 'Câmpul :attribute trebuie să aibă :decimal zecimale.',
    'declined'             => 'Câmpul :attribute trebuie să fie respins.',
    'declined_if'          => 'Câmpul :attribute trebuie să fie respins când :other este :value.',
    'different'            => 'Câmpurile :attribute și :other trebuie să fie diferite.',
    'digits'               => 'Câmpul :attribute trebuie să aibă :digits cifre.',
    'digits_between'       => 'Câmpul :attribute trebuie să aibă între :min și :max cifre.',
    'dimensions'           => 'Câmpul :attribute are dimensiuni de imagine invalide.',
    'distinct'             => 'Câmpul :attribute are o valoare duplicată.',
    'doesnt_end_with'      => 'Câmpul :attribute nu trebuie să se termine cu una dintre: :values.',
    'doesnt_start_with'    => 'Câmpul :attribute nu trebuie să înceapă cu una dintre: :values.',
    'email'                => 'Câmpul :attribute trebuie să fie o adresă de email validă.',
    'ends_with'            => 'Câmpul :attribute trebuie să se termine cu una dintre valorile: :values.',
    'enum'                 => 'Valoarea selectată pentru :attribute nu este validă.',
    'exists'               => 'Valoarea selectată pentru :attribute nu este validă.',
    'file'                 => 'Câmpul :attribute trebuie să fie un fișier.',
    'filled'               => 'Câmpul :attribute trebuie să aibă o valoare.',
    'gt'                   => [
        'array'   => 'Câmpul :attribute trebuie să aibă mai mult de :value elemente.',
        'file'    => 'Câmpul :attribute trebuie să fie mai mare de :value kilobytes.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mare de :value.',
        'string'  => 'Câmpul :attribute trebuie să aibă mai mult de :value caractere.',
    ],
    'gte'                  => [
        'array'   => 'Câmpul :attribute trebuie să aibă cel puțin :value elemente.',
        'file'    => 'Câmpul :attribute trebuie să fie mai mare sau egal cu :value kilobytes.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mare sau egal cu :value.',
        'string'  => 'Câmpul :attribute trebuie să aibă cel puțin :value caractere.',
    ],
    'image'                => 'Câmpul :attribute trebuie să fie o imagine.',
    'in'                   => 'Valoarea selectată pentru :attribute nu este validă.',
    'in_array'             => 'Câmpul :attribute nu există în :other.',
    'integer'              => 'Câmpul :attribute trebuie să fie un număr întreg.',
    'ip'                   => 'Câmpul :attribute trebuie să fie o adresă IP validă.',
    'ipv4'                 => 'Câmpul :attribute trebuie să fie o adresă IPv4 validă.',
    'ipv6'                 => 'Câmpul :attribute trebuie să fie o adresă IPv6 validă.',
    'json'                 => 'Câmpul :attribute trebuie să fie un string JSON valid.',
    'lowercase'            => 'Câmpul :attribute trebuie să fie scris cu litere mici.',
    'lt'                   => [
        'array'   => 'Câmpul :attribute trebuie să aibă mai puțin de :value elemente.',
        'file'    => 'Câmpul :attribute trebuie să fie mai mic de :value kilobytes.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mic de :value.',
        'string'  => 'Câmpul :attribute trebuie să aibă mai puțin de :value caractere.',
    ],
    'lte'                  => [
        'array'   => 'Câmpul :attribute nu trebuie să aibă mai mult de :value elemente.',
        'file'    => 'Câmpul :attribute trebuie să fie mai mic sau egal cu :value kilobytes.',
        'numeric' => 'Câmpul :attribute trebuie să fie mai mic sau egal cu :value.',
        'string'  => 'Câmpul :attribute trebuie să aibă cel mult :value caractere.',
    ],
    'mac_address'          => 'Câmpul :attribute trebuie să fie o adresă MAC validă.',
    'max'                  => [
        'array'   => 'Câmpul :attribute nu poate avea mai mult de :max elemente.',
        'file'    => 'Câmpul :attribute nu poate fi mai mare de :max kilobytes.',
        'numeric' => 'Câmpul :attribute nu poate fi mai mare de :max.',
        'string'  => 'Câmpul :attribute nu poate avea mai mult de :max caractere.',
    ],
    'max_digits'           => 'Câmpul :attribute nu poate avea mai mult de :max cifre.',
    'mimes'                => 'Câmpul :attribute trebuie să fie un fișier de tipul: :values.',
    'mimetypes'            => 'Câmpul :attribute trebuie să fie un fișier de tipul: :values.',
    'min'                  => [
        'array'   => 'Câmpul :attribute trebuie să aibă cel puțin :min elemente.',
        'file'    => 'Câmpul :attribute trebuie să aibă cel puțin :min kilobytes.',
        'numeric' => 'Câmpul :attribute trebuie să fie cel puțin :min.',
        'string'  => 'Câmpul :attribute trebuie să aibă cel puțin :min caractere.',
    ],
    'min_digits'           => 'Câmpul :attribute trebuie să aibă cel puțin :min cifre.',
    'missing'              => 'Câmpul :attribute trebuie să lipsească.',
    'missing_if'           => 'Câmpul :attribute trebuie să lipsească atunci când :other este :value.',
    'missing_unless'       => 'Câmpul :attribute trebuie să lipsească, cu excepția cazului în care :other este :value.',
    'missing_with'         => 'Câmpul :attribute trebuie să lipsească atunci când :values este prezent.',
    'missing_with_all'     => 'Câmpul :attribute trebuie să lipsească atunci când :values sunt prezente.',
    'multiple_of'          => 'Câmpul :attribute trebuie să fie un multiplu de :value.',
    'not_in'               => 'Valoarea selectată pentru :attribute nu este validă.',
    'not_regex'            => 'Formatul câmpului :attribute nu este valid.',
    'numeric'              => 'Câmpul :attribute trebuie să fie un număr.',
    'password'             => [
        'letters'       => 'Câmpul :attribute trebuie să conțină cel puțin o literă.',
        'mixed'         => 'Câmpul :attribute trebuie să conțină cel puțin o literă mare și una mică.',
        'numbers'       => 'Câmpul :attribute trebuie să conțină cel puțin o cifră.',
        'symbols'       => 'Câmpul :attribute trebuie să conțină cel puțin un simbol.',
        'uncompromised' => 'Parola :attribute apare într-o breșă de securitate. Te rugăm să alegi o altă parolă.',
    ],
    'present'              => 'Câmpul :attribute trebuie să fie prezent.',
    'present_if'           => 'Câmpul :attribute trebuie să fie prezent când :other este :value.',
    'present_unless'       => 'Câmpul :attribute trebuie să fie prezent, cu excepția cazului în care :other este :value.',
    'present_with'         => 'Câmpul :attribute trebuie să fie prezent când :values este prezent.',
    'present_with_all'     => 'Câmpul :attribute trebuie să fie prezent când :values sunt prezente.',
    'prohibited'           => 'Câmpul :attribute este interzis.',
    'prohibited_if'        => 'Câmpul :attribute este interzis când :other este :value.',
    'prohibited_unless'    => 'Câmpul :attribute este interzis, cu excepția cazului în care :other este în :values.',
    'prohibits'            => 'Câmpul :attribute interzice ca :other să fie prezent.',
    'regex'                => 'Formatul câmpului :attribute nu este valid.',
    'required'             => 'Câmpul :attribute este obligatoriu.',
    'required_array_keys'  => 'Câmpul :attribute trebuie să conțină intrări pentru: :values.',
    'required_if'          => 'Câmpul :attribute este obligatoriu când :other este :value.',
    'required_if_accepted' => 'Câmpul :attribute este obligatoriu când :other este acceptat.',
    'required_unless'      => 'Câmpul :attribute este obligatoriu, cu excepția cazului în care :other este în :values.',
    'required_with'        => 'Câmpul :attribute este obligatoriu când :values este prezent.',
    'required_with_all'    => 'Câmpul :attribute este obligatoriu când :values sunt prezente.',
    'required_without'     => 'Câmpul :attribute este obligatoriu când :values nu este prezent.',
    'required_without_all' => 'Câmpul :attribute este obligatoriu când niciuna dintre valorile :values nu este prezentă.',
    'same'                 => 'Câmpurile :attribute și :other trebuie să coincidă.',
    'size'                 => [
        'array'   => 'Câmpul :attribute trebuie să conțină :size elemente.',
        'file'    => 'Câmpul :attribute trebuie să aibă :size kilobytes.',
        'numeric' => 'Câmpul :attribute trebuie să fie :size.',
        'string'  => 'Câmpul :attribute trebuie să aibă :size caractere.',
    ],
    'starts_with'          => 'Câmpul :attribute trebuie să înceapă cu una dintre valorile: :values.',
    'string'               => 'Câmpul :attribute trebuie să fie un șir de caractere.',
    'timezone'             => 'Câmpul :attribute trebuie să fie un fus orar valid.',
    'unique'               => 'Câmpul :attribute a fost deja folosit.',
    'uploaded'             => 'Fișierul :attribute nu a putut fi încărcat.',
    'uppercase'            => 'Câmpul :attribute trebuie să fie scris cu litere mari.',
    'url'                  => 'Câmpul :attribute trebuie să fie un URL valid.',
    'uuid'                 => 'Câmpul :attribute trebuie să fie un UUID valid.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Aici poți defini mesaje personalizate pentru câmpuri specifice.
    | Format: 'attribute.rule' => 'mesaj',
    |
    */

    'custom' => [
        'email' => [
            'required' => 'Te rugăm să introduci adresa de email.',
        ],
        'password' => [
            'required' => 'Te rugăm să introduci parola.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Aici poți traduce numele câmpurilor (attribute) în ceva mai prietenos.
    |
    */

    'attributes' => [
        'email'    => 'email',
        'password' => 'parolă',
        'name'     => 'nume',
        'title'    => 'titlu',
        'phone'    => 'telefon',
        'county_id'   => 'județ',
        'category_id' => 'categorie',
        'description' => 'descriere',
        'images'      => 'imagini',
    ],

];
