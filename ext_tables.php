<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

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
			'type' => 'input',	
			'size' => '12',	
			'max' => '12',
		)
	),
);

/* Set login template based on TYPO3 version */
$version = explode('.', TYPO3_version);
if ($version[0] < 6) {
	$template = 'typo3conf/ext/sf_yubikey/res/login-v4.html';
} else {
	$template = 'typo3conf/ext/sf_yubikey/res/login-v6.html';
}

if (isset($extConf['yubikeyEnableBE']) && (bool)$extConf['yubikeyEnableBE']) {

    $TBE_STYLES['htmlTemplates']['templates/login.html'] = PATH_site . $template;
    $TBE_STYLES['stylesheet2'] = '../typo3conf/ext/sf_yubikey/res/sf_yubikey.css';
}

t3lib_div::loadTCA('be_users');
t3lib_extMgm::addTCAcolumns('be_users',$tempColumns,1);

t3lib_extMgm::addToAllTCAtypes('be_users','--div--;YubiKey,tx_sfyubikey_yubikey_enable;;;;1-1-1, tx_sfyubikey_yubikey_id');


t3lib_div::loadTCA('fe_users');
t3lib_extMgm::addTCAcolumns('fe_users',$tempColumns,1);

t3lib_extMgm::addToAllTCAtypes('fe_users','--div--;YubiKey,tx_sfyubikey_yubikey_enable;;;;1-1-1, tx_sfyubikey_yubikey_id');

?>