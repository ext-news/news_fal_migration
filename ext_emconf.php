<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'News FAL migration',
    'description' => 'Extraction of FAL migration of EXT:news to separate extension',
    'category' => 'be',
    'author' => 'Georg Ringer',
    'author_email' => 'mail@ringer.it',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.13-8.7.99',
            'news' => '5.0.0-9.9.99',
        ],
        'conflicts' => [],
        'suggests' => [
        ],
    ],
];
