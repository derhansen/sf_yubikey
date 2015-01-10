<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService($_EXTKEY, 'auth', 'DERHANSEN\SfYubikey\YubikeyAuthService',
	array(
		'title' => 'FE/BE YubiKey two-factor OTP Authentication',
		'description' => 'Two-factor authentication with a YubiKey OTP',
		'subtype' => 'authUserFE,authUserBE',
		'available' => TRUE,
		'priority' => 80,
		'quality' => 80,
		'os' => '',
		'exec' => '',
		'className' => 'DERHANSEN\SfYubikey\YubikeyAuthService'
	)
);
