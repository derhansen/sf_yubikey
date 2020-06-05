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

use DERHANSEN\SfYubikey\YubikeyAuth;
use DERHANSEN\SfYubikey\YubikeyAuthService;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for class DERHANSEN\SfYubikey\YubikeyAuthService
 */
class YubikeyAuthServiceTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * Data provider for authUserReturnsExpectedReturnCode
     *
     * @return array
     */
    public function authUserDataProvider()
    {
        return [
            'YubiKey not configured' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => false
                ],
                '',
                null,
                100
            ],
            'YubiKey not enabled' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => false
                ],
                '',
                null,
                100
            ],
            'No YubiKey given for YubiKey enabled user' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001'
                ],
                '',
                null,
                0
            ],
            'Given YubiKey does not belong to user' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001'
                ],
                'yubikey00000someOTPvalue',
                null,
                0
            ],
            'Given YubiKey could not be validated' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars'
                ],
                'yubikey00001someOTPvalue',
                false,
                0
            ],
            'Given YubiKey validated successfully' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars'
                ],
                'yubikey00001someOTPvalue',
                true,
                100
            ],
            'Given YubiKey validated successfully for user having multiple yubikeys' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars' . chr(10) . 'yubikey00002ignoredchars'
                ],
                'yubikey00002someOTPvalue',
                true,
                100
            ]
        ];
    }

    /**
     * @test
     * @dataProvider authUserDataProvider
     * @return void
     */
    public function authUserReturnsExpectedReturnCode($userData, $yubikeyOtp, $checkOtpResult, $expectedReturnCode)
    {
        /** @var \DERHANSEN\SfYubikey\YubikeyAuthService $mock */
        $mock = $this->getMockBuilder(YubikeyAuthService::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->authInfo = [
            'REMOTE_ADDR' => '127.0.0.1',
            'REMOTE_HOST' => 'localhost'
        ];
        $mock->login = [
            'uname' => $userData['username']
        ];

        $mockLogger = $this->getMockBuilder(Logger::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->setLogger($mockLogger);

        // Set YubiKey OTP GET variable if given
        if ($yubikeyOtp != '') {
            $_GET = ['t3-yubikey' => $yubikeyOtp];
        }

        $extConf = [
            'yubikeyEnableBE' => 1,
            'yubikeyEnableFE' => 0,
            'yubikeyClientId' => 'test',
            'yubikeyClientKey' => 'test'
        ];

        // Set OTP validation result if given
        if ($checkOtpResult !== null) {
            /** @var YubikeyAuth $mockYubikeyAuth */
            $mockYubikeyAuth = $this->getMockBuilder(\DERHANSEN\SfYubikey\YubikeyAuth::class)
                ->disableOriginalConstructor()->getMock();
            $mockYubikeyAuth->expects($this->once())->method('checkOtp')->with($yubikeyOtp)
                ->will($this->returnValue($checkOtpResult));

            $objectReflection = new \ReflectionObject($mock);
            $yubiKeyAuthProperty = $objectReflection->getProperty('yubiKeyAuth');
            $yubiKeyAuthProperty->setAccessible(true);
            $yubiKeyAuthProperty->setValue($mock, $mockYubikeyAuth);
            $extConfProperty = $objectReflection->getProperty('extConf');
            $extConfProperty->setAccessible(true);
            $extConfProperty->setValue($mock, $extConf);
        }

        $retCode = $mock->authUser($userData);
        $this->assertEquals($expectedReturnCode, $retCode);
    }
}
