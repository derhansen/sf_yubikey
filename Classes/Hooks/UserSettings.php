<?php

namespace Derhansen\SfYubikey\Hooks;

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * User Settings class
 */
class UserSettings
{
    /**
     * Returns a textarea with the YubiKey IDs
     */
    public function userYubikeyId(): string
    {
        return '<textarea id="field_tx_sfyubikey_yubikey_id" name="data[be_users][tx_sfyubikey_yubikey_id]"
            rows="5"  class="form-control t3js-formengine-textarea formengine-textarea">' .
            htmlspecialchars($this->getBackendUser()->user['tx_sfyubikey_yubikey_id'] ?? '') . '</textarea>';
    }

    /**
     * Returns the current BE user.
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
