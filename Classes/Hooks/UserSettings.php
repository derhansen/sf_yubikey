<?php
namespace DERHANSEN\SfYubikey\Hooks;

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
 * User Settings class
 */
class UserSettings
{

    /**
     * Returns a textarea with the YubiKey IDs
     *
     * @return string
     */
    public function user_yubikeyId()
    {
        $html = '<textarea id="field_tx_sfyubikey_yubikey_id" name="data[be_users][tx_sfyubikey_yubikey_id]"
            rows="5"  class="form-control t3js-formengine-textarea formengine-textarea">' .
            htmlspecialchars($GLOBALS['BE_USER']->user['tx_sfyubikey_yubikey_id']) . '</textarea>';
        return $html;
    }

    /**
     * Returns the current BE user.
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
