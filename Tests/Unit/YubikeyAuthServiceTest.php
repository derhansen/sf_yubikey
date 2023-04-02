<?php

namespace Derhansen\SfYubikey\Tests\Unit;

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

use Derhansen\SfYubikey\Authentication\YubikeyAuthService;
use Derhansen\SfYubikey\Service\YubikeyService;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for class Derhansen\SfYubikey\YubikeyAuthService
 */
class YubikeyAuthServiceTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    /**
     * Data provider for authUserReturnsExpectedReturnCode
     */
    public function authUserDataProvider(): array
    {
        return [
            'YubiKey not configured' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => false,
                ],
                '',
                false,
                'login',
                100,
            ],
            'YubiKey not enabled' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => false,
                ],
                '',
                false,
                'login',
                100,
            ],
            'No YubiKey given for YubiKey enabled user' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001',
                ],
                '',
                false,
                'login',
                0,
            ],
            'Given YubiKey does not belong to user' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001',
                ],
                'yubikey00000someOTPvalue',
                false,
                'login',
                0,
            ],
            'Given YubiKey could not be validated' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars',
                ],
                'yubikey00001someOTPvalue',
                false,
                'login',
                0,
            ],
            'Given YubiKey validated successfully' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars',
                ],
                'yubikey00001someOTPvalue',
                true,
                'login',
                100,
            ],
            'Given YubiKey validated successfully for user having multiple yubikeys' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001ignoredchars' . chr(10) . 'yubikey00002ignoredchars',
                ],
                'yubikey00002someOTPvalue',
                true,
                'login',
                100,
            ],
            'No YubiKey given, but status != login' => [
                [
                    'username' => 'testuser',
                    'tx_sfyubikey_yubikey_enable' => true,
                    'tx_sfyubikey_yubikey_id' => 'yubikey00001',
                ],
                '',
                false,
                'other-status',
                100,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider authUserDataProvider
     */
    public function authUserReturnsExpectedReturnCode(
        array $userData,
        string $yubikeyOtp,
        bool $checkOtpResult,
        string $loginStatus,
        int $expectedReturnCode
    ): void {
        $this->setExtensionConfig();

        $yubikeyServiceMock = $this->getMockBuilder(YubikeyService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $yubikeyServiceMock->expects(self::any())->method('verifyOtp')->willReturn($checkOtpResult);

        $authService = new YubikeyAuthService($yubikeyServiceMock);
        $mockBackendUserAuthentication = $this->createMock(BackendUserAuthentication::class);
        $authService->setLogger(new NullLogger());
        $authService->initAuth(
            'authUserBE',
            [
                'uident_text' => 'password',
                'uname' => 'username',
                'status' => $loginStatus,
            ],
            [
                'db_user' => ['table' => 'be_users'],
                'REMOTE_HOST' => 'localhost',
                'REMOTE_ADDR' => '127.0.0.1',
            ],
            $mockBackendUserAuthentication
        );

        // Set YubiKey OTP GET variable if given
        if ($yubikeyOtp !== '') {
            $_GET = ['t3-yubikey' => $yubikeyOtp];
        }

        $retCode = $authService->authUser($userData);
        self::assertEquals($expectedReturnCode, $retCode);
    }

    protected function setExtensionConfig(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['sf_yubikey'] = [
            'yubikeyClientId' => 'test',
            'yubikeyClientKey' => 'test',
            'yubikeyApiUrls' => 'api1,api2',
            'yubikeyEnableBE' => 1,
            'yubikeyEnableFE' => 0,
        ];
    }
}
