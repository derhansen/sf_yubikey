<?php
defined('TYPO3_MODE') or die();

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

call_user_func(function () {
    $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sf_yubikey');

    // Enable logging depending on extension settings
    if ($extConf['devlog']) {
        $logLevel = \TYPO3\CMS\Core\Log\LogLevel::DEBUG;
    } else {
        $logLevel = \TYPO3\CMS\Core\Log\LogLevel::INFO;
    }
    $logfileNamePrefix = 'sf_yubikey_' . date('d-m-Y') . '_';
    $namePart = substr(GeneralUtility::hmac($logfileNamePrefix, 'sfYubikey'), 0, 10);
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['Derhansen']['SfYubikey']['writerConfiguration'] = [
        $logLevel => [
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                'logFile' => 'typo3temp/var/log/' . $logfileNamePrefix . $namePart . '.log'
            ],
        ],
    ];
});
