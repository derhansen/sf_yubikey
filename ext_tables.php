<?php

defined('TYPO3') or die();

use Derhansen\SfYubikey\Hooks\UserSettings;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(function () {
    // Add YubiKey fields to user settings
    $GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_sfyubikey_yubikey_enable'] = [
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_enable',
        'type' => 'check',
        'table' => 'be_users',
    ];

    $GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_sfyubikey_yubikey_id'] = [
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_id',
        'type' => 'user',
        'userFunc' => UserSettings::class . '->userYubikeyId',
        'table' => 'be_users',
    ];

    ExtensionManagementUtility::addFieldsToUserSettings(
        '--div--;YubiKey,tx_sfyubikey_yubikey_enable, tx_sfyubikey_yubikey_id'
    );
});
