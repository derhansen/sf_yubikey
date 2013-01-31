<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_sfyubikey_sv1' /* sv key */,
		array(

			'title' => 'FE/BE YubiKey two-factor OTP Authentication',
			'description' => 'Two-factor authentication with a YubiKey OTP',

			'subtype' => 'authUserFE,authUserBE',

			'available' => TRUE,
			'priority' => 80,
			'quality' => 80,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_sfyubikey_sv1.php',
			'className' => 'tx_sfyubikey_sv1',
		)
	);
?>