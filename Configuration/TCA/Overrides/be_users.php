<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Prepare new columns for be_users table
$newColumns = [
    'tx_sfyubikey_yubikey_enable' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_enable',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'default' => 0,
            'items' => [
                [
                    'label' => '',
                    'invertStateDisplay' => false,
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
    'be_users',
    $newColumns
);
ExtensionManagementUtility::addToAllTCAtypes(
    'be_users',
    '--div--;YubiKey,tx_sfyubikey_yubikey_enable, tx_sfyubikey_yubikey_id'
);

// Show new fields in backend user settings
$GLOBALS['TCA']['be_users']['columns']['user_settings']['columns']['tx_sfyubikey_yubikey_enable'] = [
    'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_enable',
    'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',
    ],
];
$GLOBALS['TCA']['be_users']['columns']['user_settings']['columns']['tx_sfyubikey_yubikey_id'] = [
    'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_id',
    'config' => [
        'type' => 'text',
    ],
];

$GLOBALS['TCA']['be_users']['columns']['user_settings']['showitem'] .= ',--div--;YubiKey,tx_sfyubikey_yubikey_enable, tx_sfyubikey_yubikey_id';
