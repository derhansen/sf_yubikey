<?php

declare(strict_types=1);

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Derhansen\SfYubikey\Service;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides YubiKey authentication using the Yubico YubiCloud
 */
class YubikeyService
{
    protected ClientInterface $httpClient;
    protected RequestFactoryInterface $requestFactory;
    protected array $errors = [];
    protected string $yubikeyClientId = '';
    protected string $yubikeyClientKey = '';
    protected array $yubikeyApiUrl = [];
    protected bool $initialized = false;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sf_yubikey');

        $this->yubikeyClientId = trim($extConfig['yubikeyClientId'] ?? '');
        $this->yubikeyClientKey = trim($extConfig['yubikeyClientKey'] ?? '');
        $this->yubikeyApiUrl = GeneralUtility::trimExplode(';', $extConfig['yubikeyApiUrls'] ?? '', true);

        $this->initialized = $this->yubikeyClientId !== '' && $this->yubikeyClientKey !== '' &&
            !empty($this->yubikeyApiUrl);
    }

    /**
     * Verify HMAC-SHA1 signature on result received from Yubico server
     */
    public function verifyHmac(string $response, string $yubicoClientKey): bool
    {
        $lines = GeneralUtility::trimExplode(chr(10), $response);
        $result = [];

        // Create array from data
        foreach ($lines as $line) {
            $lineparts = GeneralUtility::trimExplode('=', $line, false, 2);
            if ($lineparts[0] !== '') {
                $result[$lineparts[0]] = trim($lineparts[1]);
            }
        }

        // Sort array Alphabetically based on keys
        ksort($result);

        // Grab the signature sent by server, and delete
        $signature = $result['h'];
        unset($result['h']);

        // Build new string to calculate hmac signature on
        $datastring = '';
        foreach ($result as $key => $value) {
            $datastring !== '' ? $datastring .= '&' : $datastring .= '';
            $datastring .= $key . '=' . $value;
        }
        $hmac = base64_encode(hash_hmac('sha1', $datastring, base64_decode($yubicoClientKey), true));

        $valid = $hmac === $signature;
        if (!$valid) {
            $this->addError('Could not verify signature');
        }

        return $valid;
    }

    /**
     * Call the Auth API at Yubico server
     */
    public function verifyOtp(string $otp): bool
    {
        $requestParams['id'] = $this->yubikeyClientId;
        $requestParams['otp'] = trim($otp);
        $requestParams['nonce'] = md5((new Random())->generateRandomHexString(32));
        ksort($requestParams);
        $parameters = '';
        foreach ($requestParams as $p => $v) {
            $parameters .= '&' . $p . '=' . $v;
        }
        $parameters = ltrim($parameters, '&');
        $signature = base64_encode(
            hash_hmac(
                'sha1',
                $parameters,
                base64_decode($this->yubikeyClientKey),
                true
            )
        );
        $signature = preg_replace('/\+/', '%2B', $signature);
        $parameters .= '&h=' . $signature;
        $urls = [];
        foreach ($this->yubikeyApiUrl as $apiUrl) {
            $urls[] = $apiUrl . '?' . $parameters;
        }

        foreach ($urls as $url) {
            $request = $this->requestFactory->createRequest('GET', $url);
            try {
                $response = $this->httpClient->sendRequest($request);
                if ($response->getStatusCode() !== 200) {
                    $this->addError('HTTP_STATUS_CODE_' . $response->getStatusCode());
                    continue;
                }
                $data = (string)$response->getBody();
                if ($this->verifyHmac($data, $this->yubikeyClientKey)) {
                    if (!preg_match('/status=([a-zA-Z0-9_]+)/', $data, $result)) {
                        return false;
                    }
                    if ($result[1] === 'OK') {
                        return true;
                    }
                    $this->addError($result[1]);
                }
            } catch (NetworkExceptionInterface $e) {
                $this->addError('NETWORK_EXCEPTION');
                continue;
            } catch (ClientExceptionInterface $e) {
                $this->addError('CLIENT_EXCEPTION');
                continue;
            }
        }

        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    protected function addError(string $error): void
    {
        $this->errors[] = $error;
    }
}
