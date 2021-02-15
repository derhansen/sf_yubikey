<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'YubiKey OTP MFA provider',
    'description' => 'YubiKey OTP MFA provider for the backend login.',
    'category' => 'services',
    'author' => 'Torben Hansen',
    'author_email' => 'derhansen@gmail.com',
    'state' => 'alpha',
    'uploadfolder' => 0,
    'clearCacheOnLoad' => 1,
    'version' => '4.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-11.99.99',
            'php' => '7.4.0-7.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
