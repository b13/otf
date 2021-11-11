<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'b13 OTF',
    'description' => 'Provides on-the-fly evaluation hints for FormEngine',
    'category' => 'be',
    'author' => 'b13 GmbH',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.5.99',
            'php' => '7.2.0-8.0.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ],
    'autoload' => [
        'psr-4' => [
            'B13\\Otf\\' => 'Classes/',
        ]
    ]
];
