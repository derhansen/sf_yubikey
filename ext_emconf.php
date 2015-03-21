<?php

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
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.9.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

