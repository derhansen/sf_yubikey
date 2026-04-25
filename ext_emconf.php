<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'YubiKey two-factor OTP authentication',
    'description' => 'An authentication service for TYPO3 which extends the backend/frontend login by YubiKey OTP two-factor authentication.',
    'category' => 'services',
    'author' => 'Torben Hansen',
    'author_email' => 'derhansen@gmail.com',
    'state' => 'stable',
    'version' => '7.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.3.99'
        ],
    ],
];
