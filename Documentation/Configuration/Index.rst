

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
         The Yubico API URL to validate YubiKey OTPs. This may also be an own instance of a YubiKey validation server.

   :Default:
         https://api.yubico.com/wsapi/verify