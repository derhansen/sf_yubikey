<?php

namespace Derhansen\SfYubikey\Authentication;

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Derhansen\SfYubikey\Service\YubikeyService;
use TYPO3\CMS\Core\Authentication\AbstractAuthenticationService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service "Yubikey OTP Authentication" for the "sf_yubikey" extension.
 */
class YubikeyAuthService extends AbstractAuthenticationService
{
    protected array $extConf;
    protected ?YubikeyService $yubiKeyAuth = null;

    public function __construct(YubikeyService $yubikeyService)
    {
        $this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sf_yubikey');
        $this->yubiKeyAuth = $yubikeyService;
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
    public function authUser(array $user): int
    {
        $loginType = $this->pObj->loginType;

        // only handle Yubikey for actual login requests and if YubiKey check is enabled
        if (empty($this->login['status']) || $this->login['status'] !== 'login' || !$this->isYubikeyCheckEnabled()) {
            return 100;
        }

        // Check if user Yubikey-Authentication is enabled for this user
        if (!$user['tx_sfyubikey_yubikey_enable']) {
            $this->logger->debug(
                $loginType . ' login using TYPO3 password authentication for user: ' . $user['username']
            );
            // Continue with TYPO3 authentication
            $ret = 100;
        } else {
            $this->logger->debug(
                $loginType . ' login using Yubikey authentication for user: ' . $user['username']
            );

            // Get Yubikey OTP
            $yubikeyOtp = GeneralUtility::_GP('t3-yubikey');
            $this->logger->debug('Yubikey: ' . $yubikeyOtp);
            $tempYubiKeyIds = GeneralUtility::trimExplode(
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
                $clientId = $this->extConf['yubikeyClientId'] ?? 'none';
                $this->logger->debug('Yubikey config - ClientId: ' . $clientId);

                // Check Yubikey OTP
                $authResult = $this->yubiKeyAuth->verifyOtp($yubikeyOtp);

                if ($authResult === false) {
                    $errorMessage = $loginType . ' Login-attempt from %s (%s), username \'%s\', Yubikey not accepted!';
                    $this->writelog(
                        255,
                        3,
                        3,
                        1,
                        $errorMessage,
                        [
                            $this->authInfo['REMOTE_ADDR'],
                            $this->authInfo['REMOTE_HOST'],
                            $this->login['uname'],
                        ]
                    );
                    $ret = 0;
                } else {
                    // Continue to other auth-service(s)
                    $ret = 100;
                }
            } else {
                $ret = 0;
                if ($yubikeyOtp !== '') {
                    // Wrong Yubikey ID - Authentication failure
                    $errorMessage = $loginType . ' Login-attempt from %s (%s), username \'%s\', wrong Yubikey ID!';
                } else {
                    // Yubikey missing
                    $errorMessage = $loginType . ' Login-attempt from %s (%s), username \'%s\', Yubikey needed, but empty Yubikey supplied!';
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
                        $this->login['uname'],
                    ]
                );
            }
        }
        return $ret;
    }

    private function isYubikeyCheckEnabled(): bool
    {
        $yubikeyCheckEnabled = false;
        if (isset($this->extConf['yubikeyEnableBE']) &&
            (bool)$this->extConf['yubikeyEnableBE'] &&
            $this->pObj->loginType === 'BE'
        ) {
            $yubikeyCheckEnabled = true;
        } elseif (isset($this->extConf['yubikeyEnableFE']) &&
            (bool)$this->extConf['yubikeyEnableFE'] &&
            $this->pObj->loginType == 'FE'
        ) {
            $yubikeyCheckEnabled = true;
        }
        return $yubikeyCheckEnabled;
    }
}
