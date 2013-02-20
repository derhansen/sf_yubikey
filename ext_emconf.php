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
	'shy' => '',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => 'Skyfillers GmbH',
	'version' => '0.5.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:15:{s:9:"ChangeLog";s:4:"3147";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"82a5";s:12:"ext_icon.gif";s:4:"6069";s:17:"ext_localconf.php";s:4:"0c17";s:14:"ext_tables.php";s:4:"b728";s:14:"ext_tables.sql";s:4:"5e5b";s:16:"locallang_db.xml";s:4:"1454";s:19:"doc/wizard_form.dat";s:4:"3e63";s:20:"doc/wizard_form.html";s:4:"39c4";s:14:"res/login.html";s:4:"7e00";s:18:"res/sf_yubikey.css";s:4:"95d6";s:18:"res/yubi_16x16.gif";s:4:"9346";s:30:"sv1/class.tx_sfyubikey_sv1.php";s:4:"75e9";s:15:"sv1/yubikey.php";s:4:"7506";}',
	'suggests' => array(
	),
);

?>
