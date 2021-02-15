<?php
namespace Derhansen\SfYubikey\Authentication\Mfa\Provider\Yubikey;

use Derhansen\SfYubikey\Service\YubikeyAuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\Mfa\MfaViewType;
use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderInterface;
use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderPropertyManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class YubikeyProvider implements MfaProviderInterface
{
    private const MAX_ATTEMPTS = 3;

    public function canProcess(ServerRequestInterface $request): bool
    {
        return $this->getYubikeys($request) !== '' || $this->getYubikey($request) !== '';
    }

    public function isActive(MfaProviderPropertyManager $propertyManager): bool
    {
        return (bool)$propertyManager->getProperty('active');
    }

    public function isLocked(MfaProviderPropertyManager $propertyManager): bool
    {
        $attempts = (int)$propertyManager->getProperty('attempts', 0);
        return $attempts >= self::MAX_ATTEMPTS;
    }

    public function verify(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager): bool
    {
        $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sf_yubikey');
        $yubiKeyAuthService = GeneralUtility::makeInstance(YubikeyAuthService::class, $extConfig);

        // @todo: Check if given YubiKey is configured for user (see https://github.com/derhansen/sf_yubikey/blob/master/Classes/YubikeyAuthService.php#L111)

        $verified = $yubiKeyAuthService->checkOtp($this->getYubikey($request));
        if (!$verified) {
            $attempts = $propertyManager->getProperty('attempts', 0);
            $propertyManager->updateProperties(['attempts' => ++$attempts]);
            return false;
        }
        $propertyManager->updateProperties([
            'lastUsed' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp')
        ]);

        return $verified;
    }

    public function handleRequest(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager, string $type): ResponseInterface
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateRootPaths(['EXT:sf_yubikey/Resources/Private/Templates/']);
        switch ($type) {
            case MfaViewType::SETUP:
                $this->prepareSetupView($view, $propertyManager);
                break;
            case MfaViewType::EDIT:
                $this->prepareEditView($view, $propertyManager);
                break;
            case MfaViewType::AUTH:
                $this->prepareAuthView($view, $propertyManager);
                break;
        }
        return new HtmlResponse($view->assign('provider', $this)->render());
    }

    public function activate(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager): bool
    {
        if ($this->isActive($propertyManager)) {
            // Return since the user already activated this provider
            return true;
        }

        if (!$this->canProcess($request)) {
            // Return since the request can not be processed by this provider
            return false;
        }

        // @todo Verify YubiKey (I think only verify if this all entries are valid keys - no external check)

        $properties = ['yubikeys' => $this->getYubikeys($request), 'active' => true];

        // Usually there should be no entry if the provider is not activated, but to prevent the
        // provider from being unable to activate again, we update the existing entry in such case.
        return $propertyManager->hasProviderEntry()
            ? $propertyManager->updateProperties($properties)
            : $propertyManager->createProviderEntry($properties);
    }

    public function deactivate(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager): bool
    {
        if (!$this->isActive($propertyManager)) {
            // Return since this provider is not activated
            return false;
        }

        // Delete the provider entry
        return $propertyManager->deleteProviderEntry();
    }

    public function unlock(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager): bool
    {
        if (!$this->isLocked($propertyManager)) {
            // Return since this provider is not locked
            return false;
        }

        // Reset the attempts
        return $propertyManager->updateProperties(['attempts' => 0]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param MfaProviderPropertyManager $propertyManager
     * @return bool
     */
    public function update(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager): bool
    {
        $yubikeys = (string)($request->getQueryParams()['yubikeys'] ?? $request->getParsedBody()['yubikeys'] ?? '');
        if ($yubikeys !== '') {
            return $propertyManager->updateProperties(['yubikeys' => $yubikeys]);
        }

        // Provider properties successfully updated
        return true;
    }


    /**
     * @param ViewInterface $view
     * @param MfaProviderPropertyManager $propertyManager
     */
    protected function prepareSetupView(ViewInterface $view, MfaProviderPropertyManager $propertyManager): void
    {
        // @todo Check Extension Settings for Yubico Client ID and Client Key and disable textarea if not available
        $view->setTemplate('Setup');
    }

    /**
     * @param ViewInterface $view
     * @param MfaProviderPropertyManager $propertyManager
     */
    protected function prepareEditView(ViewInterface $view, MfaProviderPropertyManager $propertyManager): void
    {
        $view->setTemplate('Edit');
        // @todo Check Extension Settings for Yubico Client ID and Client Key and disable textarea if not available
        $view->assignMultiple([
            'yubikeys' => $propertyManager->getProperty('yubikeys')
        ]);
    }

    /**
     * @param ViewInterface $view
     * @param MfaProviderPropertyManager $propertyManager
     */
    protected function prepareAuthView(ViewInterface $view, MfaProviderPropertyManager $propertyManager): void
    {
        $view->setTemplate('Auth');
        $view->assign('isLocked', $this->isLocked($propertyManager));
    }

    /**
     * Internal helper method for fetching the YubiKeys from the request in setup/edit view
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getYubikeys(ServerRequestInterface $request): string
    {
        return (string)($request->getQueryParams()['yubikeys'] ?? $request->getParsedBody()['yubikeys'] ?? '');
    }

    /**
     * Internal helper method for fetching the YubiKey from the request
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getYubikey(ServerRequestInterface $request): string
    {
        return (string)($request->getQueryParams()['yubikey'] ?? $request->getParsedBody()['yubikey'] ?? '');
    }
}
