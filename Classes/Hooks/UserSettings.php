<?php

namespace Derhansen\SfYubikey\Hooks;

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
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
    public function userYubikeyId(): string
    {
        return '<textarea id="field_tx_sfyubikey_yubikey_id" name="data[be_users][tx_sfyubikey_yubikey_id]"
            rows="5"  class="form-control t3js-formengine-textarea formengine-textarea">' .
            htmlspecialchars($GLOBALS['BE_USER']->user['tx_sfyubikey_yubikey_id']) . '</textarea>';
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
