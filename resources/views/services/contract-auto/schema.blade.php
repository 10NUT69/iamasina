@php
    $pageUrl = url()->current();
    $pageId = $pageUrl.'#webpage';
    $generatorId = $pageUrl.'#generator';
    $pdfId = $pageUrl.'#model-pdf';
    $wordId = $pageUrl.'#model-word';

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebPage',
                '@id' => $pageId,
                'url' => $pageUrl,
                'name' => $contractTitle,
                'description' => $contractMetaDescription,
                'inLanguage' => 'ro-RO',
                'isPartOf' => ['@id' => 'https://iaauto.ro/#website'],
                'mainEntity' => ['@id' => $generatorId],
                'hasPart' => [['@id' => $pdfId], ['@id' => $wordId]],
            ],
            [
                '@type' => 'WebApplication',
                '@id' => $generatorId,
                'url' => $pageUrl,
                'name' => 'Generator de contract de vânzare cumpărare auto',
                'description' => 'Completează contractul auto ITL 054 și generează un PDF A4 direct în browser, gratuit și fără cont.',
                'inLanguage' => 'ro-RO',
                'applicationCategory' => 'UtilitiesApplication',
                'browserRequirements' => 'Necesită JavaScript pentru completarea formularului și generarea PDF-ului.',
                'operatingSystem' => 'Web',
                'isAccessibleForFree' => true,
                'offers' => ['@type' => 'Offer', 'price' => 0, 'priceCurrency' => 'RON'],
                'mainEntityOfPage' => ['@id' => $pageId],
            ],
            [
                '@type' => 'DigitalDocument',
                '@id' => $pdfId,
                'name' => $contractTitle.' PDF',
                'url' => route('page.contract-auto.download', 'pdf'),
                'encodingFormat' => 'application/pdf',
                'inLanguage' => 'ro-RO',
                'isAccessibleForFree' => true,
            ],
            [
                '@type' => 'DigitalDocument',
                '@id' => $wordId,
                'name' => $contractTitle.' Word editabil',
                'url' => route('page.contract-auto.download', 'docx'),
                'encodingFormat' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'inLanguage' => 'ro-RO',
                'isAccessibleForFree' => true,
            ],
        ],
    ];
@endphp

<script type="application/ld+json">@json($schema)</script>
