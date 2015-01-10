<?php

$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath( 'sf_yubikey' );
return array(
	'tx_sfyubikey_yubikeyauth' => $extensionPath . 'Classes/YubiKeyAuth.php',
);
