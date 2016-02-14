<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService($_EXTKEY, 'auth',
    'DERHANSEN\SfYubikey\YubikeyAuthService',
    array(
        'title' => 'FE/BE YubiKey two-factor OTP Authentication',
        'description' => 'Two-factor authentication with a YubiKey OTP',
        'subtype' => 'authUserFE,authUserBE',
        'available' => true,
        'priority' => 80,
        'quality' => 80,
        'os' => '',
        'exec' => '',
        'className' => 'DERHANSEN\SfYubikey\YubikeyAuthService'
    )
);

$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY]);
if (isset($extConf['yubikeyEnableBE']) && (bool)$extConf['yubikeyEnableBE']) {
    // We can't use ::class below, to keep compatibility to PHP 5.3 and 5.4
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['backend']['loginProviders'][1433416747]['provider'] = 'DERHANSEN\\SfYubikey\\LoginProvider\\YubikeyLoginProvider';
}
