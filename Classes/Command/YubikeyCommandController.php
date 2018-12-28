<?php

namespace DERHANSEN\SfYubikey\Command;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Class YubikeyCommandController
 */
class YubikeyCommandController extends CommandController
{
    /**
     * @param string $otp
     */
    public function checkYubiKeyOtpCommand($otp)
    {
        $extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sf_yubikey']);
        $yubikeyAuth = GeneralUtility::makeInstance(
            \DERHANSEN\SfYubikey\YubikeyAuth::class,
            $extensionConfiguration
        );
        if ($yubikeyAuth->checkOtp($otp) === true) {
            $this->outputLine('OK: ' . $otp . ' has been successfully validated.');
        } else {
            $this->outputLine(
                'ERROR: ' . $otp . '  could not be validated. Reasons: ' .
                implode(' / ', $yubikeyAuth->getErrors())
            );
        }
    }
}
