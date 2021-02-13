<?php

namespace Derhansen\SfYubikey\Service;

/*
 * This file is part of the Extension "sf_yubikey" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides YubiKey authentication using the yubico YubiCloud
 */
class YubikeyAuthService
{
    protected array $errors = [];
    protected string $yubikeyClientId = '';
    protected string $yubikeyClientKey = '';
    protected array $yubikeyApiUrl = [];
    protected bool $disableSslVerification = false;
    protected bool $initialized = false;

    public function __construct()
    {
        $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('sf_yubikey');

        $this->yubikeyClientId = trim($extConfig['yubikeyClientId']);
        $this->yubikeyClientKey = trim($extConfig['yubikeyClientKey']);
        $this->yubikeyApiUrl = GeneralUtility::trimExplode(';', $extConfig['yubikeyApiUrls'], true);
        $this->disableSslVerification = (bool)$extConfig['disableSslVerification'];

        $this->initialized = $this->yubikeyClientId !== '' && $this->yubikeyClientKey !== '' &&
            !empty($this->yubikeyApiUrl);
    }

    /**
     * Verify HMAC-SHA1 signature on result received from Yubico server
     *
     * @param string $response
     * @param string $yubicoClientKey
     * @return bool
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
        $hmac = base64_encode(hash_hmac('sha1', utf8_encode($datastring), base64_decode($yubicoClientKey), true));

        $valid = $hmac === $signature;
        if (!$valid) {
            $this->addError('Could not verify signature');
        }

        return $valid;
    }

    /**
     * Call the Auth API at Yubico server
     *
     * @param string $otp
     * @return bool
     */
    public function verifyOtp(string $otp): bool
    {
        $requestParams['id'] = $this->yubikeyClientId;
        $requestParams['otp'] = trim($otp);
        $requestParams['nonce'] = md5(uniqid(rand()));
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
        $curlOptions = [
            CURLOPT_USERAGENT => 'Enhanced TYPO3 Yubikey OTP Login Service',
            CURLOPT_RETURNTRANSFER => true
        ];
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']) {
            $curlOptions[CURLOPT_PROXY] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer'];
        }
        if ($this->disableSslVerification) {
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
            $curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
        }

        $mh = curl_multi_init();
        $connections = [];
        foreach ($urls as $i => $url) {
            $connections[$i] = curl_init($url);
            $curlOptions[CURLOPT_URL] = $url;
            curl_setopt_array($connections[$i], $curlOptions);
            curl_multi_add_handle($mh, $connections[$i]);
        }

        do {
            $status = curl_multi_exec($mh, $active);
        } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

        foreach ($urls as $i => $url) {
            $response = curl_multi_getcontent($connections[$i]);
            if ($this->verifyHmac($response, $this->yubikeyClientKey)) {
                if (!preg_match('/status=([a-zA-Z0-9_]+)/', $response, $result)) {
                    return false;
                }
                if ($result[1] === 'OK') {
                    curl_multi_close($mh);
                    return true;
                }
                $this->addError($result[1]);
            }
        }
        curl_multi_close($mh);

        return false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function addError(string $error): void
    {
        $this->errors[] = $error;
    }
}
