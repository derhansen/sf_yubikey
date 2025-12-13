.. include:: /Includes.rst.txt

Configuration
-------------

The extension can be configured in the extension settings from the
extension manager. Besides the Yubico API Key and the Client ID, there
are four other settings that can be configured.

yubikeyEnableBE
----------------

.. confval:: yubikeyEnableBE

   :Type: boolean
   :Default: True

   Enable YubiKey authentication for TYPO3 backend users.


yubikeyEnableFE
----------------

.. confval:: yubikeyEnableFE

   :Type: boolean
   :Default: False

   Enable YubiKey authentication for TYPO3 frontend users.


devlog
------

.. confval:: devlog

   :Type: boolean
   :Default: False

   Writes debugging messages to a logfile in ``/typo3temp/var/log/sf_yubikey_{date}_{hash}.log``.


yubikeyClientId
----------------

.. confval:: yubikeyClientId

   :Type: string
   :Default: Empty

   Your Yubico API Client ID.


yubikeyClientKey
-----------------

.. confval:: yubikeyClientKey

   :Type: string
   :Default: Empty

   Your Yubico API Client Key.


yubikeyApiUrl
--------------

.. confval:: yubikeyApiUrl

   :Type: string
   :Default: https://api.yubico.com/wsapi/2.0/verify;https://api2.yubico.com/wsapi/2.0/verify;https://api3.yubico.com/wsapi/2.0/verify;https://api4.yubico.com/wsapi/2.0/verify;https://api5.yubico.com/wsapi/2.0/verify

   The Yubico API URL to validate YubiKey OTPs. This may also be your own
   instance of a YubiKey validation server. Separate multiple endpoints with
   a semicolon.
