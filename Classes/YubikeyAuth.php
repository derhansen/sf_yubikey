<?php
namespace DERHANSEN\SfYubikey;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides YubiKey authentication without dependencies to PEAR packages
 */
class YubikeyAuth
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * Constructor for this class
     *
     * @param array $extensionConfiguration
     */
    public function __construct($extensionConfiguration)
    {
        $yubikeyApiUrls = GeneralUtility::trimExplode(';', $extensionConfiguration['yubikeyApiUrl'], true);

        // Set configuration
        $this->setConfig($yubikeyApiUrls, 'yubikeyApiUrls');
        $this->setConfig(trim($extensionConfiguration['yubikeyClientId']), 'yubikeyClientId');
        $this->setConfig(trim($extensionConfiguration['yubikeyClientKey']), 'yubikeyClientKey');
        $this->setConfig((int)$extensionConfiguration['disableSslVerification'], 'disableSslVerification');
    }

    /**
     * Do OTP check if user has been setup to do so.
     *
     * @param String $yubikeyOtp
     * @return Boolean
     */
    public function checkOtp($yubikeyOtp)
    {
        $ret = false;
        $otp = trim($yubikeyOtp);

        // Verify if the OTP is valid ?
        if ($this->verifyOtp($otp)) {
            $ret = true;
        }

        return $ret;
    }

    /**
     * Verify HMAC-SHA1 signature on result received from Yubico server
     *
     * @param String $response Data from Yubico
     * @param String $yubicoApiKey Shared API key
     * @return Boolean Does the signature match ?
     */
    public function verifyHmac($response, $yubicoApiKey)
    {
        $lines = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(chr(10), $response);
        // Create array from data
        foreach ($lines as $line) {
            $lineparts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('=', $line, false, 2);
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
            $datastring != '' ? $datastring .= '&' : $datastring .= '';
            $datastring .= $key . '=' . $value;
        }
        $hmac = base64_encode(hash_hmac('sha1', utf8_encode($datastring), base64_decode($yubicoApiKey), true));
        return $hmac === $signature;
    }

    /**
     * Call the Auth API at Yubico server
     *
     * @param String $otp One-time Password entered by user
     * @return Boolean Is the password OK ?
     */
    public function verifyOtp($otp)
    {

        // Get the global API ID/KEY
        $yubicoApiId = trim($this->getConfig('yubikeyClientId'));
        $yubicoApiKey = trim($this->getConfig('yubikeyClientKey'));
        $disableSslVerification = (int) $this->getConfig('disableSslVerification');

        $apiUrls = $this->getConfig('yubikeyApiUrls');
        $requestParams['id'] = $yubicoApiId;
        $requestParams['otp'] = $otp;
        $requestParams['nonce'] = md5(uniqid(rand(), false));
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
                base64_decode($yubicoApiKey),
                true
            )
        );
        $signature = preg_replace('/\+/', '%2B', $signature);
        $parameters .= '&h=' . $signature;
        foreach ($apiUrls as $apiUrl) {
            $urls[] = $apiUrl . '?' . $parameters;
        }
        $curlOptions = [
            CURLOPT_USERAGENT => 'Enhanced TYPO3 Yubikey OTP Login Service',
            CURLOPT_RETURNTRANSFER => true
        ];
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer']) {
            $curlOptions[CURLOPT_PROXY] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['curlProxyServer'];
        }
        if ($disableSslVerification === 1) {
            $curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
            $curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
        }

        $mh = curl_multi_init();
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
            if ($this->verifyHmac($response, $yubicoApiKey)) {
                if (!preg_match('/status=([a-zA-Z0-9_]+)/', $response, $result)) {
                    return false;
                }
                if ($result[1] === 'OK') {
                    curl_multi_close($mh);
                    return true;
                } else {
                    $this->addError($result[1]);
                }
            } else {
                $this->addError('Could not verify signature');
            }
        }
        curl_multi_close($mh);
        return false;
    }

    /**
     * Set configuration
     *
     * @param Mixed $config
     * @param String $key Optional array key for config attribute
     * @return void
     */
    public function setConfig($config, $key = '')
    {
        if ($key !== '') {
            $this->config[$key] = $config;
        } else {
            $this->config = $config;
        }
    }

    /**
     * Get configuration
     *
     * @param String $key Optional array key for config attribute
     * @return array|string
     */
    public function getConfig($key = '')
    {
        if ($key !== '') {
            $ret = $this->config[$key];
        } else {
            $ret = $this->config;
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }
}
