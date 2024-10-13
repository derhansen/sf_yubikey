<?php

declare(strict_types=1);

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Derhansen\SfYubikey\EventListener;

use TYPO3\CMS\Backend\LoginProvider\Event\ModifyPageLayoutOnLoginProviderSelectionEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ModifyLoginView
{
    #[AsEventListener('sfyubikey/modify-login-view')]
    public function __invoke(ModifyPageLayoutOnLoginProviderSelectionEvent $event): void
    {
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('sf_yubikey');
        if (isset($extConf['yubikeyEnableBE']) && (bool)$extConf['yubikeyEnableBE']) {
            $event->getView()->setTemplatePathAndFilename(
                GeneralUtility::getFileAbsFileName('EXT:sf_yubikey/Resources/Private/Templates/LoginYubikey.html')
            );
        }
    }
}
