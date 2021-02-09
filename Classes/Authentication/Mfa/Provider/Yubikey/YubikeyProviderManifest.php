<?php
namespace Derhansen\SfYubikey\Authentication\Mfa\Provider\Yubikey;

use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderManifestInterface;

class YubikeyProviderManifest implements MfaProviderManifestInterface
{
    public function getTitle(): string
    {
        return 'YubiKey Authentication';
    }

    public function getDescription(): string
    {
        return 'Authentication for TYPO3 backend and frontend login using YubiKey OTP two-factor authentication.';
    }

    public function getIconIdentifier(): string
    {
        return 'ext-sfyubikey-icon';
    }
}
