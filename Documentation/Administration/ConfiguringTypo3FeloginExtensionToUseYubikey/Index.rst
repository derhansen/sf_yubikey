

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

If you have enables YubiKey Authentication for frontend users, you have to add an extra field
to your felogin template.

First you have to overwrite the template `Login.html` of ext: felogin.

Then you must add the following HTML to the template::

  <div>
    <label>
      Yubikey
      <f:form.textfield name="t3-yubikey"/>
    </label>
  </div>

Finally, you can add your own CSS Styles to the new field, so it looks
like a YubiKey enabled input field.

