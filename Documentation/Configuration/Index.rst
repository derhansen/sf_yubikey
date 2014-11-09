

.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. ==================================================
.. DEFINE SOME TEXTROLES
.. --------------------------------------------------
.. role::   underline
.. role::   typoscript(code)
.. role::   ts(typoscript)
   :class:  typoscript
.. role::   php(code)


Configuration
-------------

The extension can be configured in the extension settings from the
extension manager. Besides the Yubico API Key and the Client ID, there
are four other settings that can be configured.

.. t3-field-list-table::
 :header-rows: 1

 - :Property:
         Property:

   :Date type:
         Data type:

   :Description:
         Description:

   :Default:
         Default:

 - :Property:
         yubikeyEnableBE

   :Date type:
         boolean

   :Description:
         Enable YubiKey authentication for TYPO3 backend users

   :Default:
         True

 - :Property:
         yubikeyEnableFE

   :Date type:
         boolean

   :Description:
         Enable YubiKey authentication for TYPO3 backend users

   :Default:
         False

 - :Property:
         yubikeyUseSSL

   :Date type:
         boolean

   :Description:
         Uses SSL to communicate with the YubiCloud authentication servers. Only respected, if [usePear] is enabled.

   :Default:
         True

 - :Property:
         usePear

   :Date type:
         boolean

   :Description:
         If checked, the YubiKey pear library will be used to validate YubiKey OTPs. Since version 0.7.0, the
         extension contains a native YubiKey OTP validation through Yubico API server configured in [yubikeyApiUrl]
         If you enable this option, make sure that pear is available on you server!

   :Default:
         False

 - :Property:
         devlog

   :Date type:
         boolean

   :Description:
         Writes debugging messages to the TYPO3 devlog

   :Default:
         False

 - :Property:
         yubikeyClientId

   :Date type:
         string

   :Description:
         Your Yubico API Client ID

   :Default:
         Empty

 - :Property:
         yubikeyClientKey

   :Date type:
         string

   :Description:
         Your Yubico API Client Key

   :Default:
         Empty

 - :Property:
         yubikeyApiUrl

   :Date type:
         string

   :Description:
         The Yubico API URL to validate YubiKey OTPs

   :Default:
         https://api.yubico.com/wsapi/verify