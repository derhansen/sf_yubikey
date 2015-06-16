<?php
defined('TYPO3_MODE') or die();

$extConf = unserialize($TYPO3_CONF_VARS['EXT']['extConf'][$_EXTKEY]);

/* Set login template based on TYPO3 version */
$version = explode('.', TYPO3_version);
$tmplPath = 'EXT:backend/Resources/Private/Templates/login.html';
$template = 'typo3conf/ext/sf_yubikey/Resources/Private/Templates/login-v6.html';
if ($version[0] == 7 && $version[1] < 2) {
	$tmplPath = 'EXT:backend/Resources/Private/Templates/login.html';
	$template = 'typo3conf/ext/sf_yubikey/Resources/Private/Templates/login-v7.html';
}

if (isset($extConf['yubikeyEnableBE']) && (bool)$extConf['yubikeyEnableBE']) {
	// For TYPO3 6.2.x to 7.1.x
	$TBE_STYLES['htmlTemplates'][$tmplPath] = PATH_site . $template;
	$TBE_STYLES['stylesheet2'] = '../typo3conf/ext/sf_yubikey/Resources/Public/Css/sf_yubikey.css';
}