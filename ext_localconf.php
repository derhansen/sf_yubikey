<?php

defined('TYPO3') or die();

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

call_user_func(function () {
    // Register the auth service
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
        'sf_yubikey',
        'auth',
        \Derhansen\SfYubikey\Authentication\YubikeyAuthService::class,
        [
            'title' => 'FE/BE YubiKey two-factor OTP Authentication',
            'description' => 'Two-factor authentication with a YubiKey OTP',
            'subtype' => 'authUserFE,authUserBE',
            'available' => true,
            'priority' => 80,
            'quality' => 80,
            'os' => '',
            'exec' => '',
            'className' => \Derhansen\SfYubikey\Authentication\YubikeyAuthService::class
        ]
    );

    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)
        ->get('sf_yubikey');
    if (isset($extConf['yubikeyEnableBE']) && (bool)$extConf['yubikeyEnableBE']) {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['backend']['loginProviders'][1433416747]['provider'] =
            \Derhansen\SfYubikey\LoginProvider\YubikeyLoginProvider::class;
    }

    // Enable logging depending on extension settings
    if ($extConf['devlog']) {
        $logfileNamePrefix = 'sf_yubikey_' . date('d-m-Y') . '_';
        $namePart = substr(GeneralUtility::hmac($logfileNamePrefix, 'sfYubikey'), 0, 10);
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['Derhansen']['SfYubikey']['writerConfiguration'] = [
            \TYPO3\CMS\Core\Log\LogLevel::DEBUG => [
                \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                    'logFile' => 'typo3temp/var/log/' . $logfileNamePrefix . $namePart . '.log'
                ],
            ],
        ];
    }
});
