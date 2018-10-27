<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'YubiKey two-factor OTP authentication',
    'description' => 'An authentication service for TYPO3 which extends the backend/frontend login by YubiKey OTP two-factor authentication.',
    'category' => 'services',
    'author' => 'Torben Hansen',
    'author_email' => 'derhansen@gmail.com',
    'shy' => '',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 1,
    'lockType' => '',
    'version' => '2.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '7.6.0-9.5.99',
            'php' => '5.4.0-7.2.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'suggests' => [],
];
