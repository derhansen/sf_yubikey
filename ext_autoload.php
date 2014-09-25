<?php

$extensionPath = t3lib_extMgm::extPath( 'sf_yubikey' );
return array(
	'tx_sfyubikey_yubikeyauth' => $extensionPath . 'Classes/YubiKeyAuth.php',
);

?>