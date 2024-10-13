<?php

defined('TYPO3') or die();

use Derhansen\SfYubikey\Authentication\YubikeyAuthService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Register the auth service
ExtensionManagementUtility::addService(
    'sf_yubikey',
    'auth',
    YubikeyAuthService::class,
    [
        'title' => 'FE/BE YubiKey two-factor OTP Authentication',
        'description' => 'Two-factor authentication with a YubiKey OTP',
        'subtype' => 'authUserFE,authUserBE',
        'available' => true,
        'priority' => 80,
        'quality' => 80,
        'os' => '',
        'exec' => '',
        'className' => YubikeyAuthService::class,
    ]
);
