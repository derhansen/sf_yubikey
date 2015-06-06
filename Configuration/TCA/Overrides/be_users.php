<?php
defined('TYPO3_MODE') or die();

// Prepare new columns for be_users table
$tempColumns = array (
	'tx_sfyubikey_yubikey_enable' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_enable',
		'config' => array (
			'type' => 'check',
		)
	),
	'tx_sfyubikey_yubikey_id' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang_db.xlf:users.tx_sfyubikey_yubikey_id',
		'config' => array (
			'type' => 'text'
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
	'be_users',
	$tempColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'be_users',
	'--div--;YubiKey,tx_sfyubikey_yubikey_enable;;;;1-1-1, tx_sfyubikey_yubikey_id'
);
