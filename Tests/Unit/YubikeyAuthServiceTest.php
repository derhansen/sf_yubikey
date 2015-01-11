<?php
namespace DERHANSEN\SfYubikey\Tests\Unit;

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

/**
 * Testcase for class DERHANSEN\SfYubikey\YubikeyAuthService
 */
class YubikeyAuthServiceTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {

	/**
	 * Data provider for authUserReturnsExpectedReturnCode
	 *
	 * @return array
	 */
	public function authUserDataProvider() {
		return array(
			'YubiKey not configured' => array(
				array(),
				'',
				NULL,
				100
			),
			'YubiKey not enabled' => array(
				array(
					'tx_sfyubikey_yubikey_enable' => FALSE
				),
				'',
				NULL,
				100
			),
			'No YubiKey given for YubiKey enabled user' => array(
				array(
					'tx_sfyubikey_yubikey_enable' => TRUE,
					'tx_sfyubikey_yubikey_id' => 'yubikey00001'
				),
				'',
				NULL,
				0
			),
			'Given YubiKey does not belong to user' => array(
				array(
					'tx_sfyubikey_yubikey_enable' => TRUE,
					'tx_sfyubikey_yubikey_id' => 'yubikey00001'
				),
				'yubikey00000someOTPvalue',
				NULL,
				0
			),
			'Given YubiKey could not be validated' => array(
				array(
					'tx_sfyubikey_yubikey_enable' => TRUE,
					'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars'
				),
				'yubikey00001someOTPvalue',
				FALSE,
				0
			),
			'Given YubiKey validated successfully' => array(
				array(
					'tx_sfyubikey_yubikey_enable' => TRUE,
					'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars'
				),
				'yubikey00001someOTPvalue',
				TRUE,
				100
			),
			'Given YubiKey validated successfully for user having multiple yubikeys' => array(
				array(
					'tx_sfyubikey_yubikey_enable' => TRUE,
					'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars' . chr(10) . 'yubikey00002ignoredchars'
				),
				'yubikey00002someOTPvalue',
				TRUE,
				100
			)
		);
	}

	/**
	 * @test
	 * @dataProvider authUserDataProvider
	 * @return void
	 */
	public function authUserReturnsExpectedReturnCode($userData, $yubikeyOtp, $checkOtpResult, $expectedReturnCode) {
		/** @var \DERHANSEN\SfYubikey\YubikeyAuthService $authenticationService */
		$authenticationService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('DERHANSEN\SfYubikey\YubikeyAuthService');

		// Set YubiKey OTP GET variable if given
		if ($yubikeyOtp != '') {
			\TYPO3\CMS\Core\Utility\GeneralUtility::_GETset(array('t3-yubikey' => $yubikeyOtp));
		}

		// Set OTP validation result if given
		if ($checkOtpResult !== NULL) {
			$yubikeyAuth = $this->getMock('DERHANSEN\\SfYubikey\\YubikeyAuth', array(), array(), '', FALSE);
			$yubikeyAuth->expects($this->once())->method('checkOtp')->with($yubikeyOtp)->will($this->returnValue($checkOtpResult));
			$this->inject($authenticationService, 'yubiKeyAuth', $yubikeyAuth);
		}

		$retCode = $authenticationService->authUser($userData);
		$this->assertEquals($retCode, $expectedReturnCode);
	}
}