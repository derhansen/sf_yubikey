<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

call_user_func(function () {
    // Register the auth service
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
        'sf_yubikey',
        'auth',
        \DERHANSEN\SfYubikey\Authentication\YubikeyAuthService::class,
        [
            'title' => 'FE/BE YubiKey two-factor OTP Authentication',
            'description' => 'Two-factor authentication with a YubiKey OTP',
            'subtype' => 'authUserFE,authUserBE',
            'available' => true,
            'priority' => 80,
            'quality' => 80,
            'os' => '',
            'exec' => '',
            'className' => \DERHANSEN\SfYubikey\Authentication\YubikeyAuthService::class
        ]
    );

    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)
        ->get('sf_yubikey');
    if (isset($extConf['yubikeyEnableBE']) && (bool)$extConf['yubikeyEnableBE']) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['backend']['loginProviders'][1433416747]['provider'] =
            \DERHANSEN\SfYubikey\LoginProvider\YubikeyLoginProvider::class;
    }

    // Enable logging depending on extension settings
    if ($extConf['devlog']) {
        $logLevel = \TYPO3\CMS\Core\Log\LogLevel::DEBUG;
    } else {
        $logLevel = \TYPO3\CMS\Core\Log\LogLevel::INFO;
    }
    $logfileNamePrefix = 'sf_yubikey_' . date('d-m-Y') . '_';
    $namePart = substr(GeneralUtility::hmac($logfileNamePrefix, 'sfYubikey'), 0, 10);
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['DERHANSEN']['SfYubikey']['writerConfiguration'] = [
        $logLevel => [
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                'logFile' => 'typo3temp/var/log/' . $logfileNamePrefix . $namePart . '.log'
            ],
        ],
    ];
});
