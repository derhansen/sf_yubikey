.. include:: Images.txt

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


Setting the Yubico API Key
^^^^^^^^^^^^^^^^^^^^^^^^^^

After installing the extension from the extension repository, you need
to configure the extension settings. The authentication process only
works, if you provide a Yubico API Key, which can by obtained at
`https://upgrade.yubico.com/getapikey/
<https://upgrade.yubico.com/getapikey/>`_

The Yubico API Key is necessary to use the free YubiCloud OTP
validation service.

After you have obtained your Yubico API Key, enter the Client ID and
the Client Key in the extension settings.

|ext-settings|

Extension settings for the Yubico API Key

