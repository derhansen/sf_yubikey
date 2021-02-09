<?php
namespace Derhansen\SfYubikey\Authentication\Mfa\Provider\Yubikey;

use Derhansen\SfYubikey\Service\YubikeyAuthService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Authentication\Mfa\MfaContentType;
use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderInterface;
use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderManifestInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
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

    public function isActive(AbstractUserAuthentication $user): bool
    {
        return (bool)$user->getMfaProviderPropertyManager($this->getIdentifier())->getProperty('active');
    }

    public function isLocked(AbstractUserAuthentication $user): bool
    {
        $attempts = (int)$user->getMfaProviderPropertyManager($this->getIdentifier())->getProperty('attempts', 0);
        return $attempts >= self::MAX_ATTEMPTS;
    }

    public function verify(ServerRequestInterface $request, AbstractUserAuthentication $user): bool
    {
        $propertyManager = $user->getMfaProviderPropertyManager($this->getIdentifier());
        $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sf_yubikey');
        $yubiKeyAuthService = GeneralUtility::makeInstance(YubikeyAuthService::class, $extConfig);

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

    public function renderContent(ServerRequestInterface $request, AbstractUserAuthentication $user, string $type): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateRootPaths(['EXT:sf_yubikey/Resources/Private/Templates/']);
        switch ($type) {
            case MfaContentType::SETUP:
                $this->prepareSetupView($view, $user);
                break;
            case MfaContentType::EDIT:
                $this->prepareEditView($view, $user);
                break;
            case MfaContentType::AUTH:
                $this->prepareAuthView($view, $user);
                break;
        }
        return $view->assign('provider', $this)->render();
    }

    public function activate(ServerRequestInterface $request, AbstractUserAuthentication $user): bool
    {
        if ($this->isActive($user)) {
            // Return since the user already activated this provider
            return true;
        }

        if (!$this->canProcess($request)) {
            // Return since the request can not be processed by this provider
            return false;
        }

        // @todo Verify YubiKey (I think only verify if this all entries are valid keys - no external check)

        $properties = ['yubikeys' => $this->getYubikeys($request), 'active' => true];
        $propertyManager = $user->getMfaProviderPropertyManager($this->getIdentifier());

        // Usually there should be no entry if the provider is not activated, but to prevent the
        // provider from being unable to activate again, we update the existing entry in such case.
        return $propertyManager->hasProviderEntry()
            ? $propertyManager->updateProperties($properties)
            : $propertyManager->createProviderEntry($properties);
    }

    public function deactivate(ServerRequestInterface $request, AbstractUserAuthentication $user): bool
    {
        if (!$this->isActive($user)) {
            // Return since this provider is not activated
            return false;
        }

        // Delete the provider entry
        return $user->getMfaProviderPropertyManager($this->getIdentifier())->deleteProviderEntry();
    }

    public function unlock(ServerRequestInterface $request, AbstractUserAuthentication $user): bool
    {
        if (!$this->isLocked($user)) {
            // Return since this provider is not locked
            return false;
        }

        // Reset the attempts
        return $user->getMfaProviderPropertyManager($this->getIdentifier())->updateProperties(['attempts' => 0]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param AbstractUserAuthentication $user
     * @return bool
     */
    public function update(ServerRequestInterface $request, AbstractUserAuthentication $user): bool
    {
        $yubikeys = (string)($request->getQueryParams()['yubikeys'] ?? $request->getParsedBody()['yubikeys'] ?? '');
        if ($yubikeys !== '') {
            return $user->getMfaProviderPropertyManager($this->getIdentifier())
                ->updateProperties(['yubikeys' => $yubikeys]);
        }

        // Provider properties successfully updated
        return true;
    }
    public function getIdentifier(): string
    {
        return 'yubikey';
    }

    public function getManifest(): MfaProviderManifestInterface
    {
        return GeneralUtility::makeInstance(YubikeyProviderManifest::class);
    }

    /**
     * @param ViewInterface $view
     * @param AbstractUserAuthentication $user
     */
    protected function prepareSetupView(ViewInterface $view, AbstractUserAuthentication $user): void
    {
        // @todo Check Extension Settings for Yubico Client ID and Client Key and disable textarea if not available
        $view->setTemplate('Setup');
    }

    /**
     * @param ViewInterface $view
     * @param AbstractUserAuthentication $user
     */
    protected function prepareEditView(ViewInterface $view, AbstractUserAuthentication $user): void
    {
        $propertyManager = $user->getMfaProviderPropertyManager($this->getIdentifier());
        $view->setTemplate('Edit');
        // @todo Check Extension Settings for Yubico Client ID and Client Key and disable textarea if not available
        $view->assignMultiple([
            'yubikeys' => $propertyManager->getProperty('yubikeys')
        ]);
    }

    /**
     * @param ViewInterface $view
     * @param AbstractUserAuthentication $user
     */
    protected function prepareAuthView(ViewInterface $view, AbstractUserAuthentication $user): void
    {
        $view->setTemplate('Auth');
        $view->assign('isLocked', $this->isLocked($user));
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
