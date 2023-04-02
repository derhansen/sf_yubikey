<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Prepare new columns for fe_users table
$tempColumns = [
    'tx_sfyubikey_yubikey_enable' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_enable',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
            'items' => [
                [
                    0 => '',
                    1 => '',
                ],
            ],
        ],
    ],
    'tx_sfyubikey_yubikey_id' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_id',
        'config' => [
            'type' => 'text',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    $tempColumns
);
ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    '--div--;YubiKey,tx_sfyubikey_yubikey_enable, tx_sfyubikey_yubikey_id'
);
