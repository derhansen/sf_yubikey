<?php
defined('TYPO3_MODE') or die();

$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY]);

$tempColumns = array (
	'tx_sfyubikey_yubikey_enable' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_yubikey/locallang_db.xml:users.tx_sfyubikey_yubikey_enable',
		'config' => array (
			'type' => 'check',
		)
	),
	'tx_sfyubikey_yubikey_id' => array (
		'exclude' => 0,
		'label' => 'LLL:EXT:sf_yubikey/locallang_db.xml:users.tx_sfyubikey_yubikey_id',
		'config' => array (
			'type' => 'text'
		)
	),
);

/* Set login template based on TYPO3 version */
$version = explode('.', TYPO3_version);
$tmplPath = 'EXT:backend/Resources/Private/Templates/login.html';
$template = 'typo3conf/ext/sf_yubikey/Resources/Private/Templates/login-v6.html';
if ($version[0] == 7) {
	$tmplPath = 'EXT:backend/Resources/Private/Templates/login.html';
	$template = 'typo3conf/ext/sf_yubikey/Resources/Private/Templates/login-v7.html';
}

if (isset($extConf['yubikeyEnableBE']) && (bool)$extConf['yubikeyEnableBE']) {
	$TBE_STYLES['htmlTemplates'][$tmplPath] = PATH_site . $template;
	$TBE_STYLES['stylesheet2'] = '../typo3conf/ext/sf_yubikey/Resources/Public/Css/sf_yubikey.css';
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users',$tempColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users','--div--;YubiKey,tx_sfyubikey_yubikey_enable;;;;1-1-1, tx_sfyubikey_yubikey_id');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',$tempColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users','--div--;YubiKey,tx_sfyubikey_yubikey_enable;;;;1-1-1, tx_sfyubikey_yubikey_id');
