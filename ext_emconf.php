<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'YubiKey two-factor OTP authentication',
    'description' => 'An authentication service for TYPO3 which extends the backend/frontend login by YubiKey OTP two-factor authentication.',
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
