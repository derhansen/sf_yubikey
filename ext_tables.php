<?php

defined('TYPO3') or die();

call_user_func(function () {
    // Add css file for YubiKey backend login
    $GLOBALS['TBE_STYLES']['stylesheet2'] = '../typo3conf/ext/sf_yubikey/Resources/Public/Css/sf_yubikey.css';

    // Add YubiKey fields to user settings
    $GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_sfyubikey_yubikey_enable'] = [
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_enable',
        'type' => 'check',
        'table' => 'be_users'
    ];

    $GLOBALS['TYPO3_USER_SETTINGS']['columns']['tx_sfyubikey_yubikey_id'] = [
        'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_id',
        'type' => 'user',
        'userFunc' => \DERHANSEN\SfYubikey\Hooks\UserSettings::class . '->userYubikeyId',
        'table' => 'be_users'
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToUserSettings(
        '--div--;YubiKey,tx_sfyubikey_yubikey_enable, tx_sfyubikey_yubikey_id'
    );
});
