

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


Configuring TYPO3 felogin extension to use YubiKey
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you have enables YubiKey Authentication for frontend users, you
have to add an extra field to your felogin template.

First you have to configure felogin, so it uses an own template.

styles.content.loginform.templateFile =
fileadmin/templates/ext\_felogin/template.html

Then you must add the following HTML to the ###TEMPLATE\_LOGIN###
section right after the password field.::

 <div>
   <label for="t3-yubikey">YubiKey</label>
   <input type="password" id="t3-yubikey" name="t3-yubikey" value="" />
 </div>

Finally, you can add your own CSS Styles to the new field, so it looks
like a YubiKey enabled input field.

