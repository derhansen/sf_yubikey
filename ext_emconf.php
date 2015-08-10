<?php

########################################################################
# Extension Manager/Repository config file for ext "sf_yubikey".
#
# Auto generated 10-06-2012 12:14
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'YubiKey two-factor OTP authentication',
	'description' => 'An authentication service for TYPO3 which extends the backend/frontend login by YubiKey OTP two-factor authentication.',
	'category' => 'services',
	'author' => 'Torben Hansen',
	'author_email' => 'derhansen@gmail.com',
	'author_company' => 'Skyfillers GmbH',
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'version' => '1.1.1',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.99.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>
