<?php
namespace DERHANSEN\SfYubikey;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Service "Yubikey OTP Authentication" for the "sf_yubikey" extension.
 */
class YubikeyAuthService extends \TYPO3\CMS\Core\Authentication\AbstractAuthenticationService
{
    /**
     * Keeps class name.
     *
     * @var string
     */
    public $prefixId = 'tx_sfyubikey_sv1';

    /**
     * Keeps path to this script relative to the extension directory.
     *
     * @var string
     */
    public $scriptRelPath = 'sv1/class.tx_sfyubikey_sv1.php';

    /**
     * Keeps extension key.
     *
     * @var string
     */
    public $extKey = 'sf_yubikey';

    /**
     * Keeps extension configuration.
     *
     * @var mixed
     */
    protected $extConf;

    /**
     * YubiKey authentication helper
     *
     * @var \DERHANSEN\SfYubikey\YubikeyAuth
     */
    protected $yubiKeyAuth = null;

    /**
     * Checks if service is available.
     *
     * @return bool TRUE if service is available
     */
    public function init()
    {
        $available = false;
        $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sf_yubikey']);
        $this->yubiKeyAuth = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            'DERHANSEN\SfYubikey\YubikeyAuth',
            $this->extConf
        );
        if (isset($this->extConf['yubikeyEnableBE']) && (bool)$this->extConf['yubikeyEnableBE'] && TYPO3_MODE == 'BE') {
            $available = true;
        } elseif (isset($this->extConf['yubikeyEnableFE']) && (bool)$this->extConf['yubikeyEnableFE'] && TYPO3_MODE == 'FE') {
            $available = true;
        }
        return $available;
    }

    /**
     * Authenticates the user by using Yubikey
     *
     * Will return one of following authentication status codes:
     *  - 0 - authentication failure
     *  - 100 - just go on. User is not authenticated but there is still no reason to stop
     *  - 200 - the service was able to authenticate the user
     *
     * @param array $user Array containing the userdata
     * @return int authentication statuscode, one of 0, 100 and 200
     */
    public function authUser(array $user)
    {
        // 0 means authentication failure
        $ret = 0;

        // Check if user Yubikey-Authentication is enabled for this user
        if (!$user['tx_sfyubikey_yubikey_enable']) {
            $this->writeDevLog(TYPO3_MODE . ' login using TYPO3 password authentication for user: ' . $user['username']);
            // Continue with TYPO3 authentication
            $ret = 100;
        } else {
            $this->writeDevLog(TYPO3_MODE . ' login using Yubikey authentication for user: ' . $user['username']);

            // Get Yubikey OTP
            $yubikeyOtp = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('t3-yubikey');
            $this->writeDevLog('Yubikey: ' . $yubikeyOtp);
            $tempYubiKeyIds = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
                chr(10),
                $user['tx_sfyubikey_yubikey_id'],
                true
            );
            $yubiKeyIds = [];
            foreach ($tempYubiKeyIds as $tempYubiKeyId) {
                $yubiKeyIds[] = substr($tempYubiKeyId, 0, 12);
            }
            // Check, if Yubikey-ID does match with users Yubikey-ID
            if (in_array(substr($yubikeyOtp, 0, 12), $yubiKeyIds)) {
                $clientId = $this->extConf['yubikeyClientId'];
                $this->writeDevLog('Yubikey config - ClientId: ' . $clientId);

                // Check Yubikey OTP
                $authResult = $this->yubiKeyAuth->checkOtp($yubikeyOtp);

                if ($authResult === false) {
                    $errorMessage = TYPO3_MODE . ' Login-attempt from %s (%s), username \'%s\', Yubikey not accepted!';
                    $this->writelog(
                        255,
                        3,
                        3,
                        1,
                        $errorMessage,
                        [
                            $this->authInfo['REMOTE_ADDR'],
                            $this->authInfo['REMOTE_HOST'],
                            $this->login['uname']
                        ]
                    );
                    $ret = 0;
                } else {
                    // Continue to other auth-service(s)
                    $ret = 100;
                }
            } else {
                if ($yubikeyOtp != '') {
                    // Wrong Yubikey ID - Authentication failure
                    $errorMessage = TYPO3_MODE . ' Login-attempt from %s (%s), username \'%s\', wrong Yubikey ID!';
                    $ret = 0;
                } else {
                    // Yubikey missing
                    $errorMessage = TYPO3_MODE . ' Login-attempt from %s (%s), username \'%s\', Yubikey needed, but empty Yubikey supplied!';
                    $ret = 0;
                }
                $this->writelog(
                    255,
                    3,
                    3,
                    1,
                    $errorMessage,
                    [
                        $this->authInfo['REMOTE_ADDR'],
                        $this->authInfo['REMOTE_HOST'],
                        $this->login['uname']
                    ]
                );
            }
        }
        return $ret;
    }

    /**
     * Writes to devlog if enabled
     *
     * @param string $message Message for devlog
     * @return void
     */
    private function writeDevLog($message)
    {
        if ($this->extConf['devlog']) {
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog($message, 'tx_sfyubikey_sv1', 0);
        }
    }
}
