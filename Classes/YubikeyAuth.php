<?php
namespace DERHANSEN\SfYubikey;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 - 2014 mehrwert <typo3@mehrwert.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Provides YubiKey authentication without dependencies to
 * PEAR packages
 *
 * @author		mehrwert <typo3@mehrwert.de>
 * @package		TYPO3
 * @subpackage	tx_sfyubikey
 * @license		GPL
 */
class YubikeyAuth {

	/**
	 * @var array
	 */
	protected $config = array();

	/**
	 * Constructor for this class
	 *
	 * @param Array $extensionConfiguration
	 */
	public function __construct( $extensionConfiguration ) {

		// Set configuration
		$this->setConfig( trim($extensionConfiguration['yubikeyApiUrl']), 'yubikeyApiUrl' );
		$this->setConfig( trim($extensionConfiguration['yubikeyClientId']), 'yubikeyClientId' );
		$this->setConfig( trim($extensionConfiguration['yubikeyClientKey']), 'yubikeyClientKey' );

	}

	/**
	 * Do OTP check if user has been setup to do so.
	 *
	 * @param String $yubikeyOtp
	 * @return Boolean
	 */
	public function checkOtp( $yubikeyOtp ) {

		$ret = FALSE;
		$otp = trim( $yubikeyOtp );

		// Verify if the OTP is valid ?
		if ( $this->verifyOtp($otp)) {
			$ret = TRUE;
		}

		return $ret;
	}

	/**
	 * Verify HMAC-SHA1 signatur on result received from Yubico server
	 *
	 * @param String $response Data from Yubico
	 * @param String $yubicoApiKey Shared API key
	 * @return Boolean Does the signature match ?
	 */
	public function verifyHmac($response, $yubicoApiKey) {
		$lines = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(chr(10), $response);
			// Create array from data
		foreach ($lines as $line) {
			$lineparts = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode('=', $line, FALSE, 2);
			$result[$lineparts[0]] = trim($lineparts[1]);
		}
		// Sort array Alphabetically based on keys
		ksort($result);
		// Grab the signature sent by server, and delete
		$signatur = $result['h'];
		unset($result['h']);
		// Build new string to calculate hmac signature on
		$datastring = '';
		foreach ($result as $key => $value) {
			$datastring != '' ? $datastring .= '&' : $datastring .= '';
			$datastring .= $key . '=' . $value;
		}
		$hmac = base64_encode(hash_hmac('sha1', $datastring, base64_decode($yubicoApiKey), TRUE));
		return $hmac == $signatur;
	}

	/**
	 * Call the Auth API at Yubico server
	 *
	 * @param String $otp One-time Password entered by user
	 * @return Boolean Is the password OK ?
	 */
	public function verifyOtp( $otp ) {

		// Get the global API ID/KEY
		$yubicoApiId = trim($this->getConfig('yubikeyClientId'));
		$yubicoApiKey = trim($this->getConfig('yubikeyClientKey'));

		$url = $this->getConfig('yubikeyApiUrl') . '?id=' . $yubicoApiId . '&otp=' . $otp;

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Enhanced TYPO3 Yubikey OTP Login Service');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = trim(curl_exec($ch));
		curl_close($ch);

		if ( $this->verifyHmac( $response, $yubicoApiKey ) ) {
			if ( !preg_match('/status=([a-zA-Z0-9_]+)/', $response, $result) ) {
				return FALSE;
			}
			if ( $result[1] == 'OK' ) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Set configuration
	 *
	 * @param Mixed $config
	 * @param String $key Optional array key for config attribute
	 * @return void
	 */
	public function setConfig($config,  $key = '' ) {
		if ( $key != '' ) {
			$this->config[$key] = $config;
		} else {
			$this->config = $config;
		}
	}

	/**
	 * Get configuration
	 *
	 * @param String $key Optional array key for config attribute
	 * @return array
	 */
	public function getConfig( $key = '' ) {
		if ( $key != '' ) {
			$ret = $this->config[$key];
		} else {
			$ret = $this->config;
		}
		return $ret;
	}

}
