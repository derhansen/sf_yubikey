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


User settings
^^^^^^^^^^^^^

To enable the YubiKey two-factor authentication for a user, just edit
the user in the TYPO3 backend and enable the checkbox as shown below.

|be-user-settings|

Next you have to enter the YubiKey ID, which is the unique ID of the YubiKey USB
key. To get the ID, just insert your YubiKey into a free USB port and press the button
on the YubiKey. Now a YubiKey OTP will be inserted in the textfield. Don't care, that the
textfield will show the whole YubiKey OPT. The authentication process will automatically
extract the YubiKey ID from the OTP.

If you have multiple YubiKey devices, you can save the YubiKey ID of each
device in the textfield. Remember to use a new line for each YubiKey ID.


