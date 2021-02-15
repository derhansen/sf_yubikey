<?php

namespace Derhansen\SfYubikey\Authentication\Mfa\Provider\Yubikey;

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Derhansen\SfYubikey\Service\YubikeyAuthService;
use Derhansen\SfYubikey\Service\YubikeyService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\Mfa\MfaViewType;
use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderInterface;
use TYPO3\CMS\Core\Authentication\Mfa\MfaProviderPropertyManager;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

class YubikeyProvider implements MfaProviderInterface
{
    private const LLL = 'LLL:EXT:sf_yubikey/Resources/Private/Language/locallang.xlf:';
    private const MAX_ATTEMPTS = 3;

    private ResponseFactoryInterface $responseFactory;
    private YubikeyAuthService $yubikeyAuthService;
    private YubikeyService $yubikeyService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        YubikeyAuthService $yubikeyAuthService,
        YubikeyService $yubikeyService
    ) {
        $this->responseFactory = $responseFactory;
        $this->yubikeyAuthService = $yubikeyAuthService;
        $this->yubikeyService = $yubikeyService;
    }

    /**
     * Checks if a YubiKey OTP is in the current request
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function canProcess(ServerRequestInterface $request): bool
    {
        return $this->getYubikeyOtp($request) !== '';
    }

    /**
     * Evaluate if the provider is activated by checking the active state from the provider properties.
     *
     * @param MfaProviderPropertyManager $propertyManager
     * @return bool
     */
    public function isActive(MfaProviderPropertyManager $propertyManager): bool
    {
        return (bool)$propertyManager->getProperty('active');
    }

    /**
     * Evaluate if the provider is temporarily locked by checking the current attempts state
     * from the provider properties.
     *
     * @param MfaProviderPropertyManager $propertyManager
     * @return bool
     */
    public function isLocked(MfaProviderPropertyManager $propertyManager): bool
    {
        $attempts = (int)$propertyManager->getProperty('attempts', 0);
        return $attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Checks if the given OTP is a configured YubiKey for the current user and if so, verifies the OTP
     * against the configured authentication servers
     *
     * @param ServerRequestInterface $request
     * @param MfaProviderPropertyManager $propertyManager
     * @return bool
     */
    public function verify(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager): bool
    {
        $otp = $this->getYubikeyOtp($request);
        $yubikeys = $propertyManager->getProperty('yubikeys');
        if (!$this->yubikeyService->isInYubikeys($yubikeys, $otp)) {
            // YubiKey not configured for user
            return false;
        }

        $verified = $this->yubikeyAuthService->verifyOtp($otp);
        if (!$verified) {
            $attempts = $propertyManager->getProperty('attempts', 0);
            $propertyManager->updateProperties(['attempts' => ++$attempts]);
            return false;
        }

        $yubikeys = $this->yubikeyService->updateYubikeyUsage(
            $yubikeys,
            $otp,
            GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp')
        );
        $propertyManager->updateProperties(['yubikeys' => $yubikeys]);

        return true;
    }

    /**
     * Initialize view and forward to the appropriate implementation
     * based on the view type to be returned.
     *
     * @param ServerRequestInterface $request
     * @param MfaProviderPropertyManager $propertyManager
     * @param string $type
     * @return ResponseInterface
     */
    public function handleRequest(
        ServerRequestInterface $request,
        MfaProviderPropertyManager $propertyManager,
        string $type
    ): ResponseInterface {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplateRootPaths(['EXT:sf_yubikey/Resources/Private/Templates/']);
        $view->setPartialRootPaths(['EXT:sf_yubikey/Resources/Private/Partials/']);
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
        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($view->assign('provider', $this)->render());
        return $response;
    }

    /**
     * Activate the provider by checking the necessary parameters
     *
     * @param ServerRequestInterface $request
     * @param MfaProviderPropertyManager $propertyManager
     * @return bool
     */
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

        $newYubikey = $this->getNewYubikey($request);
        if (empty($newYubikey) && $this->isNewYubikeyRequest($request)) {
            // Either not YubiKey OTP given or OTP is wrong
            return false;
        }

        $yubikeys = [];
        $yubikeys[] = $newYubikey;

        $properties = [
            'yubikeys' => $yubikeys,
            'active' => true
        ];

        // Usually there should be no entry if the provider is not activated, but to prevent the
        // provider from being unable to activate again, we update the existing entry in such case.
        return $propertyManager->hasProviderEntry()
            ? $propertyManager->updateProperties($properties)
            : $propertyManager->createProviderEntry($properties);
    }

    /**
     * Deactivates the provider for the current user
     *
     * @param ServerRequestInterface $request
     * @param MfaProviderPropertyManager $propertyManager
     * @return bool
     */
    public function deactivate(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager): bool
    {
        if (!$this->isActive($propertyManager)) {
            // Return since this provider is not activated
            return false;
        }

        // Delete the provider entry
        return $propertyManager->deleteProviderEntry();
    }

    /**
     * Handle the unlock action by resetting the attempts provider property
     *
     * @param ServerRequestInterface $request
     * @param MfaProviderPropertyManager $propertyManager
     * @return bool
     */
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
     * Handle the save action for the provider. Takes care of adding/removing YubiKeys and updating
     * provider properties
     *
     * @param ServerRequestInterface $request
     * @param MfaProviderPropertyManager $propertyManager
     * @return bool
     */
    public function update(ServerRequestInterface $request, MfaProviderPropertyManager $propertyManager): bool
    {
        $yubikeys = $propertyManager->getProperty('yubikeys');
        $otp = $this->getYubikeyOtp($request);
        $isNewYubikeyRequest = $this->isNewYubikeyRequest($request);
        $existingYubikey = $this->yubikeyService->isInYubikeys($yubikeys, $otp);

        if ($this->isDeleteYubikeyRequest($request)) {
            // Handle delete request
            $yubikeyToDelete = $request->getParsedBody()['delete'];
            $yubikeys = $this->yubikeyService->deleteFromYubikeys($yubikeys, $yubikeyToDelete);
            return $propertyManager->updateProperties(['yubikeys' => $yubikeys]);
        }

        if ($isNewYubikeyRequest && !$existingYubikey) {
            // Add new YubiKey
            $yubikeys[] = $this->getNewYubikey($request);
            return $propertyManager->updateProperties(['yubikeys' => $yubikeys]);
        }

        if ($isNewYubikeyRequest && $existingYubikey) {
            $this->addFlashMessage(
                $this->getLanguageService()->sL(self::LLL . 'yubikeyAlreadyConfigured.message'),
                $this->getLanguageService()->sL(self::LLL . 'yubikeyAlreadyConfigured.title'),
                FlashMessage::WARNING
            );
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
        $view->setTemplate('Setup');
        $view->assignMultiple([
            'initialized' => $this->isAuthServiceInitialized()
        ]);
    }

    /**
     * @param ViewInterface $view
     * @param MfaProviderPropertyManager $propertyManager
     */
    protected function prepareEditView(ViewInterface $view, MfaProviderPropertyManager $propertyManager): void
    {
        $view->setTemplate('Edit');
        $view->assignMultiple([
            'yubikeys' => $propertyManager->getProperty('yubikeys'),
            'initialized' => $this->isAuthServiceInitialized()
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
     * Internal helper method for fetching the YubiKey OTP from the request for authentication
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function getYubikeyOtp(ServerRequestInterface $request): string
    {
        return (string)($request->getQueryParams()['yubikey-otp'] ?? $request->getParsedBody()['yubikey-otp'] ?? '');
    }

    /**
     * Internal helper method for fetching a new YubiKey from the request. Also checks if the provided YubiKey OTP
     * id valid and extracts the YubiKey ID from the OTP.
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function getNewYubikey(ServerRequestInterface $request): array
    {
        $yubikeyData = [];
        $name = (string)($request->getParsedBody()['yubikey-name'] ?? '');
        $yubikey = $this->getYubikeyOtp($request);

        $yubikeyId = $this->yubikeyService->getIdFromOtp($yubikey);

        if ($yubikeyId !== '') {
            $yubikeyData = [
                'name' => $name,
                'yubikeyId' => $yubikeyId,
                'dateAdded' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
                'lastUsed' => ''
            ];
        }

        return $yubikeyData;
    }

    protected function isNewYubikeyRequest(ServerRequestInterface $request): bool
    {
        return $this->getYubikeyOtp($request) !== '';
    }

    protected function isDeleteYubikeyRequest(ServerRequestInterface $request): bool
    {
        $delete = $request->getParsedBody()['delete'] ?? '';
        return $delete !== '';
    }

    protected function isAuthServiceInitialized(): bool
    {
        $initialized = $this->yubikeyAuthService->isInitialized();
        if (!$initialized) {
            $this->addFlashMessage(
                $this->getLanguageService()->sL(self::LLL . 'invalidConfiguration.message'),
                $this->getLanguageService()->sL(self::LLL . 'invalidConfiguration.title'),
                FlashMessage::ERROR
            );
        }

        return $initialized;
    }

    protected function addFlashMessage(string $message, string $title = '', int $severity = FlashMessage::INFO): void
    {
        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, $severity, true);
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
