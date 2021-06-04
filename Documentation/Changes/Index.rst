

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

Changes
-------

A list of all changes can be found on GitHub
`https://github.com/derhansen/sf_yubikey/commits/master
<https://github.com/derhansen/sf_yubikey/commits/master>`_

Important Changes
=================

Version 1.0.0
-------------
YubiKey OTP validation is only handled through the bundled class **YubikeyAuth**.
The usage of PEAR classes has been removed.


Version 4.0.0
-------------
The YubiKey validation is performed using the class **YubikeyService** which uses
`psr/http-client` and `psr/http-factory` for requests. The `disableSslVerification` setting has been
removed, since this can be configured global in TYPO3 HTTP client settings.

The extension setting `yubikeyApiUrl` has been renamed to `yubikeyApiUrls`. Users must adapt this
setting manually if configured.