<?php
return [
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => OMEKA_PATH . '/modules/LodConnect/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
];

