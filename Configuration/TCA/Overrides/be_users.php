<?php
defined('TYPO3_MODE') or die();

// Prepare new columns for be_users table
$tempColumns = [
    'tx_sfyubikey_yubikey_enable' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_enable',
        'config' => [
            'type' => 'check',
        ]
    ],
    'tx_sfyubikey_yubikey_id' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_id',
        'config' => [
            'type' => 'text'
        ]
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'be_users',
    $tempColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'be_users',
    '--div--;YubiKey,tx_sfyubikey_yubikey_enable, tx_sfyubikey_yubikey_id'
);
