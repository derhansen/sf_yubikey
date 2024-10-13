<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'YubiKey two-factor OTP authentication',
    'description' => 'An authentication service for TYPO3 which extends the backend/frontend login by YubiKey OTP two-factor authentication.',
    'category' => 'services',
    'author' => 'Torben Hansen',
    'author_email' => 'derhansen@gmail.com',
    'state' => 'stable',
    'version' => '6.0.0-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '13.3.0-13.4.99'
        ],
    ],
];
